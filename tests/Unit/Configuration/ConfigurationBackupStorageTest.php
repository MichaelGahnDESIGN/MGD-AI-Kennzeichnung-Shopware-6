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

/** Prüft die Sicherung mit einer echten transaktionalen SQL-Datenbank. */
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

    public function testSnapshotReadsOnlyWhitelistAndAllScopes(): void
    {
        $salesChannelId = '018f123456789abcdef0123456789abc';
        $this->insertSystemConfig(ConfigurationKeys::LANGUAGE, 'de');
        $this->insertSystemConfig(ConfigurationKeys::FONT_SIZE, 14);
        $this->insertSystemConfig(ConfigurationKeys::LANGUAGE, 'en', $salesChannelId);
        $this->insertSystemConfig('OtherPlugin.config.secret', 'nicht-sichern');

        $this->storage->replaceSnapshot();

        $entries = null;
        $this->storage->restoreTransaction(static function (array $restored) use (&$entries): void {
            $entries = $restored;
        });

        self::assertEquals([
            new ConfigurationBackupEntry(ConfigurationKeys::FONT_SIZE, null, 14),
            new ConfigurationBackupEntry(ConfigurationKeys::LANGUAGE, null, 'de'),
            new ConfigurationBackupEntry(ConfigurationKeys::LANGUAGE, $salesChannelId, 'en'),
        ], $entries);
        self::assertSame(0, $this->backupCount(), 'Ein erfolgreich verbrauchter Snapshot muss entfernt sein.');
    }

    public function testRepeatedKeepCyclesReplaceTheOldSnapshot(): void
    {
        $this->insertSystemConfig(ConfigurationKeys::LANGUAGE, 'de');
        $this->storage->replaceSnapshot();
        $this->connection->update('system_config', ['configuration_value' => '{"_value":"en"}'], [
            'configuration_key' => ConfigurationKeys::LANGUAGE,
        ]);

        $this->storage->replaceSnapshot();

        $entries = [];
        $this->storage->restoreTransaction(static function (array $restored) use (&$entries): void {
            $entries = $restored;
        });
        self::assertSame('en', $entries[0]->value);
        self::assertCount(1, $entries);
    }

    public function testMalformedOrForeignBackupFailsBeforeRestoreCallback(): void
    {
        $this->storage->replaceSnapshot();
        $this->insertBackup('OtherPlugin.config.secret', null, 'string', '{"_value":"x"}');
        $called = false;

        try {
            $this->storage->restoreTransaction(static function () use (&$called): void {
                $called = true;
            });
            self::fail('Ein fremder Sicherungsschlüssel muss die Wiederherstellung abbrechen.');
        } catch (\RuntimeException $exception) {
            self::assertSame(
                'Die Plugin-Konfiguration konnte nicht vollständig wiederhergestellt werden.',
                $exception->getMessage(),
            );
        }

        self::assertFalse($called);
        self::assertSame(1, $this->backupCount());
    }

    public function testWrongValueTypeFailsClosed(): void
    {
        $this->storage->replaceSnapshot();
        $this->insertBackup(ConfigurationKeys::FONT_SIZE, null, 'string', '{"_value":"14"}');

        $this->expectException(\RuntimeException::class);
        $this->storage->restoreTransaction(static function (): void {
            self::fail('Bei einem falschen Typ darf nichts wiederhergestellt werden.');
        });
    }

    public function testDuplicateScopeCollisionFailsBeforeRestoreCallback(): void
    {
        $this->storage->replaceSnapshot();
        $this->insertBackup(ConfigurationKeys::LANGUAGE, null, 'string', '{"_value":"de"}');
        $this->insertBackup(ConfigurationKeys::LANGUAGE, null, 'string', '{"_value":"en"}');
        $called = false;

        $this->expectException(\RuntimeException::class);
        try {
            $this->storage->restoreTransaction(static function () use (&$called): void {
                $called = true;
            });
        } finally {
            self::assertFalse($called);
            self::assertSame(2, $this->backupCount());
        }
    }

    public function testInvalidSalesChannelBytesFailClosed(): void
    {
        $this->storage->replaceSnapshot();
        $this->connection->insert('mgd_ai_image_labels_config_backup', [
            'id' => Uuid::randomBytes(),
            'config_key' => ConfigurationKeys::LANGUAGE,
            'sales_channel_id' => 'zu-kurz',
            'value_type' => 'string',
            'configuration_value' => '{"_value":"de"}',
            'created_at' => '2026-08-10 00:00:00.000',
            'updated_at' => null,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->storage->restoreTransaction(static function (): void {
            self::fail('Eine ungültige Verkaufskanal-ID darf nicht wiederhergestellt werden.');
        });
    }

    public function testRestoreFailureRollsBackAndKeepsSnapshot(): void
    {
        $this->insertSystemConfig(ConfigurationKeys::LANGUAGE, 'de');
        $this->storage->replaceSnapshot();

        try {
            $this->storage->restoreTransaction(function (): void {
                $this->connection->update('system_config', ['configuration_value' => '{"_value":"en"}'], [
                    'configuration_key' => ConfigurationKeys::LANGUAGE,
                ]);
                throw new \RuntimeException('absichtlicher Testfehler');
            });
            self::fail('Der Testfehler muss weitergereicht werden.');
        } catch (\RuntimeException $exception) {
            self::assertSame(
                'Die Plugin-Konfiguration konnte nicht vollständig wiederhergestellt werden.',
                $exception->getMessage(),
            );
            self::assertNotNull($exception->getPrevious());
        }

        self::assertSame('{"_value":"de"}', $this->connection->fetchOne(
            'SELECT configuration_value FROM system_config WHERE configuration_key = ?',
            [ConfigurationKeys::LANGUAGE],
        ));
        self::assertSame(1, $this->backupCount());
    }

    public function testSilentValueMutationFailsVerificationAndRollsBack(): void
    {
        $this->insertSystemConfig(ConfigurationKeys::LANGUAGE, 'de');
        $this->storage->replaceSnapshot();
        $this->connection->update('system_config', ['configuration_value' => '{"_value":"auto"}'], [
            'configuration_key' => ConfigurationKeys::LANGUAGE,
        ]);

        $this->assertRestoreMismatchRollsBack(function (): void {
            $this->connection->update('system_config', ['configuration_value' => '{"_value":"en"}'], [
                'configuration_key' => ConfigurationKeys::LANGUAGE,
            ]);
        });

        self::assertSame('{"_value":"auto"}', $this->systemConfigValue(ConfigurationKeys::LANGUAGE));
    }

    public function testSilentDeletionFailsVerificationAndRollsBack(): void
    {
        $this->insertSystemConfig(ConfigurationKeys::LANGUAGE, 'de');
        $this->storage->replaceSnapshot();
        $this->connection->update('system_config', ['configuration_value' => '{"_value":"auto"}'], [
            'configuration_key' => ConfigurationKeys::LANGUAGE,
        ]);

        $this->assertRestoreMismatchRollsBack(function (): void {
            $this->connection->delete('system_config', ['configuration_key' => ConfigurationKeys::LANGUAGE]);
        });

        self::assertSame('{"_value":"auto"}', $this->systemConfigValue(ConfigurationKeys::LANGUAGE));
    }

    public function testSilentExtraWhitelistRowFailsVerificationAndRollsBack(): void
    {
        $this->insertSystemConfig(ConfigurationKeys::LANGUAGE, 'de');
        $this->storage->replaceSnapshot();
        $this->connection->update('system_config', ['configuration_value' => '{"_value":"auto"}'], [
            'configuration_key' => ConfigurationKeys::LANGUAGE,
        ]);

        $this->assertRestoreMismatchRollsBack(function (): void {
            $this->insertSystemConfig(ConfigurationKeys::FONT_SIZE, 18);
        });

        self::assertSame(0, $this->connection->fetchOne(
            'SELECT COUNT(*) FROM system_config WHERE configuration_key = ?',
            [ConfigurationKeys::FONT_SIZE],
        ));
        self::assertSame('{"_value":"auto"}', $this->systemConfigValue(ConfigurationKeys::LANGUAGE));
    }

    public function testSilentTypeCoercionFailsVerificationAndRollsBack(): void
    {
        $this->insertSystemConfig(ConfigurationKeys::FONT_SIZE, 13);
        $this->storage->replaceSnapshot();
        $this->connection->update('system_config', ['configuration_value' => '{"_value":6}'], [
            'configuration_key' => ConfigurationKeys::FONT_SIZE,
        ]);

        $this->assertRestoreMismatchRollsBack(function (): void {
            $this->connection->update('system_config', ['configuration_value' => '{"_value":"13"}'], [
                'configuration_key' => ConfigurationKeys::FONT_SIZE,
            ]);
        });

        self::assertSame('{"_value":6}', $this->systemConfigValue(ConfigurationKeys::FONT_SIZE));
    }

    public function testDropRemovesOnlyOwnedBackupTable(): void
    {
        $this->storage->replaceSnapshot();
        $this->storage->dropTable();

        self::assertSame(1, $this->connection->fetchOne("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'system_config'"));
        self::assertSame(0, $this->connection->fetchOne("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'mgd_ai_image_labels_config_backup'"));
    }

    private function insertSystemConfig(string $key, int|string $value, ?string $salesChannelId = null): void
    {
        $this->connection->insert('system_config', [
            'id' => Uuid::randomBytes(),
            'configuration_key' => $key,
            'configuration_value' => json_encode(['_value' => $value], JSON_THROW_ON_ERROR),
            'sales_channel_id' => $salesChannelId === null ? null : Uuid::fromHexToBytes($salesChannelId),
            'created_at' => '2026-08-10 00:00:00.000',
        ]);
    }

    private function insertBackup(string $key, ?string $salesChannelId, string $type, string $rawValue): void
    {
        $this->connection->insert('mgd_ai_image_labels_config_backup', [
            'id' => Uuid::randomBytes(),
            'config_key' => $key,
            'sales_channel_id' => $salesChannelId === null ? null : Uuid::fromHexToBytes($salesChannelId),
            'value_type' => $type,
            'configuration_value' => $rawValue,
            'created_at' => '2026-08-10 00:00:00.000',
            'updated_at' => null,
        ]);
    }

    private function backupCount(): int
    {
        return (int) $this->connection->fetchOne('SELECT COUNT(*) FROM mgd_ai_image_labels_config_backup');
    }

    /** @param callable(): void $silentMutation */
    private function assertRestoreMismatchRollsBack(callable $silentMutation): void
    {
        $completed = false;
        try {
            $this->storage->restoreTransaction(static function () use ($silentMutation): void {
                $silentMutation();
            });
            $completed = true;
        } catch (\RuntimeException $exception) {
            self::assertSame(
                'Die Plugin-Konfiguration konnte nicht vollständig wiederhergestellt werden.',
                $exception->getMessage(),
            );
        }

        self::assertFalse($completed, 'Ein vom Snapshot abweichender Datenbankzustand muss den Restore abbrechen.');
        self::assertSame(1, $this->backupCount(), 'Der Snapshot muss nach einem Verify-Fehler erhalten bleiben.');
    }

    private function systemConfigValue(string $key): string
    {
        $value = $this->connection->fetchOne(
            'SELECT configuration_value FROM system_config WHERE configuration_key = ?',
            [$key],
        );
        self::assertIsString($value);

        return $value;
    }
}
