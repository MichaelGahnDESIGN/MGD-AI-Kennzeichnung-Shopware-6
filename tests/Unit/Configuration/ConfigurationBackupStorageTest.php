<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Configuration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use MGDAIImageLabels\Configuration\ConfigurationBackupEntry;
use MGDAIImageLabels\Configuration\ConfigurationBackupStorage;
use MGDAIImageLabels\Configuration\ConfigurationKeys;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Uuid\Uuid;

/** Prüft Generationen, Eigentum und Rollback mit einer echten SQLite-Datenbank. */
final class ConfigurationBackupStorageTest extends TestCase
{
    private Connection $connection;
    private ConfigurationBackupStorage $storage;

    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $this->connection->executeStatement(<<<'SQL'
            CREATE TABLE system_config (
                id BLOB NOT NULL PRIMARY KEY,
                configuration_key VARCHAR(255) NOT NULL,
                configuration_value TEXT NOT NULL,
                sales_channel_id BLOB NULL,
                created_at TEXT NOT NULL
            )
            SQL);
        $this->storage = new ConfigurationBackupStorage($this->connection);
    }

    public function testSnapshotReadsOnlyWhitelistAndPersistsAfterRestore(): void
    {
        $salesChannelId = '018f123456789abcdef0123456789abc';
        $this->insertSystemConfig(ConfigurationKeys::LANGUAGE, 'de');
        $this->insertSystemConfig(ConfigurationKeys::FONT_SIZE, 14);
        $this->insertSystemConfig(ConfigurationKeys::LANGUAGE, 'en', $salesChannelId);
        $this->insertSystemConfig('OtherPlugin.config.secret', 'nicht-sichern');
        $this->storage->replaceSnapshot();

        $entries = null;
        $restored = $this->storage->restoreTransaction(
            static function (array $snapshot) use (&$entries): void {
                $entries = $snapshot;
            },
        );

        self::assertTrue($restored);
        self::assertEquals([
            new ConfigurationBackupEntry(ConfigurationKeys::FONT_SIZE, null, 14),
            new ConfigurationBackupEntry(ConfigurationKeys::LANGUAGE, null, 'de'),
            new ConfigurationBackupEntry(ConfigurationKeys::LANGUAGE, $salesChannelId, 'en'),
        ], $entries);
        self::assertSame(5, $this->backupCount(), 'Owner, Kopfzeile und Werte bleiben für einen Retry erhalten.');
    }

    public function testNoSnapshotAndExplicitEmptySnapshotAreDifferentStates(): void
    {
        $this->storage->ensureTable();
        $called = false;
        self::assertFalse($this->storage->restoreTransaction(static function () use (&$called): void {
            $called = true;
        }));
        self::assertFalse($called);

        $this->storage->replaceSnapshot();
        $this->insertSystemConfig(ConfigurationKeys::LANGUAGE, 'auto');
        self::assertTrue($this->storage->restoreTransaction(function (array $snapshot, array $current): void {
            self::assertSame([], $snapshot);
            self::assertCount(1, $current);
            $this->connection->delete('system_config', ['configuration_key' => ConfigurationKeys::LANGUAGE]);
        }));
        self::assertSame(2, $this->backupCount(), 'Auch ein leerer Snapshot besitzt Owner und Kopfzeile.');
    }

    public function testRepeatedKeepCyclesAtomicallyReplaceGeneration(): void
    {
        $this->insertSystemConfig(ConfigurationKeys::LANGUAGE, 'de');
        $this->storage->replaceSnapshot();
        $firstGeneration = $this->generation();
        $this->connection->update('system_config', ['configuration_value' => '{"_value":"en"}'], [
            'configuration_key' => ConfigurationKeys::LANGUAGE,
        ]);

        $this->storage->replaceSnapshot();

        self::assertNotSame($firstGeneration, $this->generation());
        $this->storage->restoreTransaction(static function (array $snapshot): void {
            self::assertCount(1, $snapshot);
            self::assertSame('en', $snapshot[0]->value);
        });
        self::assertSame(3, $this->backupCount());
    }

    public function testRestoreFailureRollsBackSystemValuesAndKeepsGeneration(): void
    {
        $this->insertSystemConfig(ConfigurationKeys::LANGUAGE, 'de');
        $this->storage->replaceSnapshot();
        $generation = $this->generation();

        try {
            $this->storage->restoreTransaction(function (): void {
                $this->connection->update('system_config', ['configuration_value' => '{"_value":"en"}'], [
                    'configuration_key' => ConfigurationKeys::LANGUAGE,
                ]);
                throw new \RuntimeException('absichtlicher Testfehler');
            });
            self::fail('Der Testfehler muss weitergereicht werden.');
        } catch (\RuntimeException $exception) {
            self::assertSame('Die Plugin-Konfiguration konnte nicht vollständig wiederhergestellt werden.', $exception->getMessage());
        }

        self::assertSame('{"_value":"de"}', $this->systemConfigValue(ConfigurationKeys::LANGUAGE));
        self::assertSame($generation, $this->generation());
    }

    public function testSilentRestoreMutationFailsStrictVerificationAndRollsBack(): void
    {
        $this->insertSystemConfig(ConfigurationKeys::FONT_SIZE, 13);
        $this->storage->replaceSnapshot();
        $this->connection->update('system_config', ['configuration_value' => '{"_value":6}'], [
            'configuration_key' => ConfigurationKeys::FONT_SIZE,
        ]);

        $this->expectException(\RuntimeException::class);
        try {
            $this->storage->restoreTransaction(function (): void {
                $this->connection->update('system_config', ['configuration_value' => '{"_value":"13"}'], [
                    'configuration_key' => ConfigurationKeys::FONT_SIZE,
                ]);
            });
        } finally {
            self::assertSame('{"_value":6}', $this->systemConfigValue(ConfigurationKeys::FONT_SIZE));
            self::assertSame(3, $this->backupCount());
        }
    }

    public function testMalformedSnapshotFailsBeforeCallback(): void
    {
        $this->insertSystemConfig(ConfigurationKeys::LANGUAGE, 'de');
        $this->storage->replaceSnapshot();
        $this->connection->update(ConfigurationBackupStorage::TABLE_NAME, [
            'configuration_value' => '{"_value":14}',
        ], ['record_type' => 'value']);
        $called = false;

        $this->expectException(\RuntimeException::class);
        try {
            $this->storage->restoreTransaction(static function () use (&$called): void {
                $called = true;
            });
        } finally {
            self::assertFalse($called);
        }
    }

    public function testForeignSameNamedTableIsNeverClaimedOrDropped(): void
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $connection->executeStatement('CREATE TABLE mgd_ai_image_labels_config_backup (id BLOB PRIMARY KEY, secret TEXT)');
        $storage = new ConfigurationBackupStorage($connection);

        foreach (['ensureTable', 'dropTable'] as $method) {
            try {
                $storage->{$method}();
                self::fail('Eine namensgleiche Fremdtabelle muss fail-safe abgewiesen werden.');
            } catch (\RuntimeException) {
                self::assertTrue($connection->createSchemaManager()->tablesExist([ConfigurationBackupStorage::TABLE_NAME]));
                self::assertSame(['id', 'secret'], array_keys($connection->createSchemaManager()->listTableColumns(ConfigurationBackupStorage::TABLE_NAME)));
            }
        }
    }

    public function testMissingOrWrongOwnerMarkerPreventsMutationAndDrop(): void
    {
        $this->storage->ensureTable();
        $this->connection->delete(ConfigurationBackupStorage::TABLE_NAME, ['record_type' => 'owner']);
        $this->connection->insert(ConfigurationBackupStorage::TABLE_NAME, [
            'id' => Uuid::randomBytes(),
            'record_type' => 'owner',
            'generation_id' => null,
            'scope_hash' => hash('sha256', 'fremd'),
            'config_key' => null,
            'sales_channel_id' => null,
            'value_type' => null,
            'configuration_value' => null,
            'owner_token' => 'fremdes-plugin',
            'created_at' => '2026-08-10 00:00:00.000',
            'updated_at' => null,
        ]);

        foreach (['replaceSnapshot', 'dropTable'] as $method) {
            try {
                $this->storage->{$method}();
                self::fail('Ohne eigenen Marker darf keine Mutation erfolgen.');
            } catch (\RuntimeException) {
                self::assertTrue($this->connection->createSchemaManager()->tablesExist([ConfigurationBackupStorage::TABLE_NAME]));
            }
        }
    }

    public function testOwnedTableCanBeDroppedAndLeavesSystemConfigUntouched(): void
    {
        $this->storage->ensureTable();
        $this->storage->dropTable();

        self::assertTrue($this->connection->createSchemaManager()->tablesExist(['system_config']));
        self::assertFalse($this->connection->createSchemaManager()->tablesExist([ConfigurationBackupStorage::TABLE_NAME]));
    }

    private function insertSystemConfig(string $key, int|string $value, ?string $salesChannelId = null): void
    {
        $this->connection->insert('system_config', [
            'id' => Uuid::randomBytes(),
            'configuration_key' => $key,
            'configuration_value' => json_encode(['_value' => $value], \JSON_THROW_ON_ERROR),
            'sales_channel_id' => $salesChannelId === null ? null : Uuid::fromHexToBytes($salesChannelId),
            'created_at' => '2026-08-10 00:00:00.000',
        ]);
    }

    private function backupCount(): int
    {
        $count = $this->connection->fetchOne('SELECT COUNT(*) FROM `' . ConfigurationBackupStorage::TABLE_NAME . '`');
        self::assertTrue(is_int($count) || (is_string($count) && ctype_digit($count)));

        return intval($count);
    }

    private function generation(): string
    {
        $generation = $this->connection->fetchOne(
            'SELECT generation_id FROM `' . ConfigurationBackupStorage::TABLE_NAME . '` WHERE record_type = ?',
            ['header'],
        );
        self::assertIsString($generation);

        return bin2hex($generation);
    }

    private function systemConfigValue(string $key): string
    {
        $value = $this->connection->fetchOne('SELECT configuration_value FROM system_config WHERE configuration_key = ?', [$key]);
        self::assertIsString($value);

        return $value;
    }
}
