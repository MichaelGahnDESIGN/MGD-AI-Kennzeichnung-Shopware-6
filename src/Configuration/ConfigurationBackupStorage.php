<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Configuration;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Uuid\Uuid;

/** Persistiert ausschließlich die neun positiv gelisteten Plugin-Einstellungen. */
class ConfigurationBackupStorage
{
    public const TABLE_NAME = 'mgd_ai_image_labels_config_backup';

    public function __construct(private Connection $connection)
    {
    }

    public function replaceSnapshot(): void
    {
        $this->ensureTable();

        try {
            $this->connection->transactional(function (): void {
                $entries = $this->readOwnedSystemConfiguration();
                $this->assertUniqueScopes($entries);
                $this->connection->executeStatement('DELETE FROM `' . self::TABLE_NAME . '`');

                $now = (new \DateTimeImmutable())->format(Defaults::STORAGE_DATE_TIME_FORMAT);
                foreach ($entries as $entry) {
                    $this->connection->insert(self::TABLE_NAME, [
                        'id' => Uuid::randomBytes(),
                        'config_key' => $entry->key,
                        'sales_channel_id' => $entry->salesChannelId === null
                            ? null
                            : Uuid::fromHexToBytes($entry->salesChannelId),
                        'value_type' => ConfigurationKeys::expectedType($entry->key),
                        'configuration_value' => json_encode(['_value' => $entry->value], \JSON_THROW_ON_ERROR),
                        'created_at' => $now,
                        'updated_at' => null,
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            throw new \RuntimeException(
                'Die Plugin-Konfiguration konnte nicht sicher für die Reinstallation gesichert werden.',
                0,
                $exception,
            );
        }
    }

    /** @param callable(list<ConfigurationBackupEntry>): void $restore */
    public function restoreTransaction(callable $restore): void
    {
        try {
            $this->ensureTable();

            $this->connection->transactional(function () use ($restore): void {
                $rows = $this->connection->fetchAllAssociative(
                    'SELECT config_key, configuration_value, sales_channel_id, value_type FROM `' . self::TABLE_NAME . '` ORDER BY config_key ASC, sales_channel_id ASC',
                );
                $entries = [];
                foreach ($rows as $row) {
                    $entries[] = $this->entryFromBackupRow($row);
                }

                $this->assertUniqueScopes($entries);
                $restore($entries);
                // Eine leere Tabelle kennzeichnet den ersten Installationslauf ohne Keep-Snapshot.
                // Shopwares zuvor geschriebene Standardwerte müssen dann unverändert bestehen bleiben.
                if ($entries !== []) {
                    $this->assertSnapshotMatchesSystemConfiguration($entries);
                }
                $this->connection->executeStatement('DELETE FROM `' . self::TABLE_NAME . '`');
            });
        } catch (\Throwable $exception) {
            throw new \RuntimeException(
                'Die Plugin-Konfiguration konnte nicht vollständig wiederhergestellt werden.',
                0,
                $exception,
            );
        }
    }

    public function dropTable(): void
    {
        $this->connection->executeStatement('DROP TABLE IF EXISTS `mgd_ai_image_labels_config_backup`');
    }

    /** Legt die kleine Tabelle auch vor Shopwares nachgelagertem Migrationslauf an. */
    public function ensureTable(): void
    {
        $platform = $this->connection->getDatabasePlatform();
        if ($platform instanceof AbstractMySQLPlatform) {
            $this->connection->executeStatement(<<<'SQL'
                CREATE TABLE IF NOT EXISTS `mgd_ai_image_labels_config_backup` (
                    `id` BINARY(16) NOT NULL,
                    `config_key` VARCHAR(255) NOT NULL,
                    `sales_channel_id` BINARY(16) NULL,
                    `value_type` VARCHAR(16) NOT NULL,
                    `configuration_value` JSON NOT NULL,
                    `created_at` DATETIME(3) NOT NULL,
                    `updated_at` DATETIME(3) NULL,
                    PRIMARY KEY (`id`),
                    INDEX `idx.mgd_ai_config_backup.key` (`config_key`),
                    CONSTRAINT `json.mgd_ai_config_backup.value` CHECK (JSON_VALID(`configuration_value`))
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                SQL);

            return;
        }

        if ($platform instanceof SQLitePlatform) {
            $this->connection->executeStatement(<<<'SQL'
                CREATE TABLE IF NOT EXISTS `mgd_ai_image_labels_config_backup` (
                    `id` BLOB NOT NULL PRIMARY KEY,
                    `config_key` VARCHAR(255) NOT NULL,
                    `sales_channel_id` BLOB NULL,
                    `value_type` VARCHAR(16) NOT NULL,
                    `configuration_value` TEXT NOT NULL,
                    `created_at` TEXT NOT NULL,
                    `updated_at` TEXT NULL
                )
                SQL);

            return;
        }

        throw new \RuntimeException('Die Datenbankplattform wird für die Konfigurationssicherung nicht unterstützt.');
    }

    /** @param array<string, mixed> $row */
    private function entryFromSystemConfigRow(array $row): ConfigurationBackupEntry
    {
        return $this->validatedEntry(
            $row['configuration_key'] ?? null,
            $row['sales_channel_id'] ?? null,
            $row['configuration_value'] ?? null,
            null,
        );
    }

    /** @return list<ConfigurationBackupEntry> */
    private function readOwnedSystemConfiguration(): array
    {
        $rows = $this->connection->executeQuery(
            <<<'SQL'
                SELECT configuration_key, configuration_value, sales_channel_id
                FROM system_config
                WHERE configuration_key IN (:keys)
                ORDER BY configuration_key ASC, sales_channel_id ASC
                SQL,
            ['keys' => ConfigurationKeys::all()],
            ['keys' => ArrayParameterType::STRING],
        )->fetchAllAssociative();

        $entries = [];
        foreach ($rows as $row) {
            $entries[] = $this->entryFromSystemConfigRow($row);
        }

        return $entries;
    }

    /** @param array<string, mixed> $row */
    private function entryFromBackupRow(array $row): ConfigurationBackupEntry
    {
        return $this->validatedEntry(
            $row['config_key'] ?? null,
            $row['sales_channel_id'] ?? null,
            $row['configuration_value'] ?? null,
            $row['value_type'] ?? null,
        );
    }

    private function validatedEntry(
        mixed $key,
        mixed $salesChannelBytes,
        mixed $rawValue,
        mixed $storedType,
    ): ConfigurationBackupEntry {
        if (!is_string($key) || !ConfigurationKeys::isOwned($key) || !is_string($rawValue)) {
            throw new \RuntimeException('Die gesicherte Plugin-Konfiguration ist ungültig.');
        }

        try {
            $decoded = json_decode($rawValue, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \RuntimeException('Die gesicherte Plugin-Konfiguration ist ungültig.', 0, $exception);
        }

        if (!is_array($decoded) || array_keys($decoded) !== ['_value']) {
            throw new \RuntimeException('Die gesicherte Plugin-Konfiguration ist ungültig.');
        }

        $value = $decoded['_value'];
        if (!is_int($value) && !is_string($value) && !is_bool($value) && !is_float($value)) {
            throw new \RuntimeException('Die gesicherte Plugin-Konfiguration ist ungültig.');
        }

        $expectedType = ConfigurationKeys::expectedType($key);
        if ($expectedType === null || gettype($value) !== $expectedType) {
            throw new \RuntimeException('Die gesicherte Plugin-Konfiguration besitzt einen ungültigen Werttyp.');
        }
        if ($storedType !== null && (!is_string($storedType) || $storedType !== $expectedType)) {
            throw new \RuntimeException('Die gesicherte Plugin-Konfiguration besitzt einen ungültigen Werttyp.');
        }

        $salesChannelId = null;
        if ($salesChannelBytes !== null) {
            if (!is_string($salesChannelBytes) || strlen($salesChannelBytes) !== 16) {
                throw new \RuntimeException('Die gesicherte Verkaufskanal-Zuordnung ist ungültig.');
            }
            $salesChannelId = Uuid::fromBytesToHex($salesChannelBytes);
        }

        return new ConfigurationBackupEntry($key, $salesChannelId, $value);
    }

    /** @param list<ConfigurationBackupEntry> $entries */
    private function assertUniqueScopes(array $entries): void
    {
        $seen = [];
        foreach ($entries as $entry) {
            $scope = $entry->key . ':' . ($entry->salesChannelId ?? 'global');
            if (isset($seen[$scope])) {
                throw new \RuntimeException('Die gesicherte Plugin-Konfiguration enthält eine ungültige Zuordnung.');
            }
            $seen[$scope] = true;
        }
    }

    /**
     * @param list<ConfigurationBackupEntry> $snapshot
     *
     * Vergleicht bewusst Schlüssel, Scope, PHP-Typ und Wert einzeln. Eine lose
     * Objekt- oder JSON-Prüfung könnte etwa die Zeichenkette „13“ mit 13 verwechseln.
     */
    private function assertSnapshotMatchesSystemConfiguration(array $snapshot): void
    {
        $actual = $this->readOwnedSystemConfiguration();
        $this->assertUniqueScopes($actual);

        if (count($actual) !== count($snapshot)) {
            throw new \RuntimeException('Die wiederhergestellte Plugin-Konfiguration weicht vom Snapshot ab.');
        }

        foreach ($snapshot as $index => $expected) {
            $restored = $actual[$index];
            if (
                $restored->key !== $expected->key
                || $restored->salesChannelId !== $expected->salesChannelId
                || gettype($restored->value) !== gettype($expected->value)
                || $restored->value !== $expected->value
            ) {
                throw new \RuntimeException('Die wiederhergestellte Plugin-Konfiguration weicht vom Snapshot ab.');
            }
        }
    }
}
