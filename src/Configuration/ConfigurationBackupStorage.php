<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Configuration;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * Bewahrt ausschließlich die neun positiv gelisteten Plugin-Einstellungen.
 *
 * Die Tabelle besitzt eine dauerhafte Eigentümerzeile und eine ausdrücklich
 * vorhandene Snapshot-Kopfzeile. Dadurch sind „kein Snapshot“ und ein bewusst
 * leerer Snapshot unterscheidbar. Der Snapshot bleibt nach einem Restore stehen:
 * Scheitert Shopware erst später im Installationsablauf, kann der nächste Versuch
 * dieselbe geprüfte Generation erneut einspielen.
 */
class ConfigurationBackupStorage
{
    public const TABLE_NAME = 'mgd_ai_image_labels_config_backup';
    public const OWNER_TOKEN = 'mgd-ai-image-labels/config-backup/v2';

    private const RECORD_OWNER = 'owner';
    private const RECORD_HEADER = 'header';
    private const RECORD_VALUE = 'value';
    private const OWNER_SCOPE_HASH = 'f70b13d8a047d1b8603beac36c560d541cff6837095e8dcd8473aa34a53bf8d5';
    private const HEADER_SCOPE_HASH = '1ece8ff152fa2157c83cd58ad78415ffdf9f0c65cd1d39ff6414f78e95d3f85a';

    /** @var list<string> */
    private const COLUMNS = [
        'id',
        'record_type',
        'generation_id',
        'scope_hash',
        'config_key',
        'sales_channel_id',
        'value_type',
        'configuration_value',
        'owner_token',
        'created_at',
        'updated_at',
    ];

    public function __construct(private Connection $connection)
    {
    }

    /** Ersetzt atomar die vorherige Generation, auch wenn aktuell kein Wert gesetzt ist. */
    public function replaceSnapshot(): void
    {
        $this->ensureTable();

        try {
            $this->connection->transactional(function (): void {
                $this->lockOwnerRow();
                $entries = $this->readOwnedSystemConfiguration();
                $this->assertUniqueScopes($entries);
                $generation = Uuid::randomBytes();
                $now = $this->now();

                $this->connection->executeStatement(
                    'DELETE FROM `' . self::TABLE_NAME . '` WHERE record_type <> ?',
                    [self::RECORD_OWNER],
                );
                $this->insertRecord([
                    'id' => Uuid::randomBytes(),
                    'record_type' => self::RECORD_HEADER,
                    'generation_id' => $generation,
                    'scope_hash' => self::HEADER_SCOPE_HASH,
                    'config_key' => null,
                    'sales_channel_id' => null,
                    'value_type' => null,
                    'configuration_value' => null,
                    'owner_token' => null,
                    'created_at' => $now,
                    'updated_at' => null,
                ]);

                foreach ($entries as $entry) {
                    $this->insertRecord([
                        'id' => Uuid::randomBytes(),
                        'record_type' => self::RECORD_VALUE,
                        'generation_id' => $generation,
                        'scope_hash' => hash('sha256', $entry->key . '|' . ($entry->salesChannelId ?? 'global')),
                        'config_key' => $entry->key,
                        'sales_channel_id' => $entry->salesChannelId === null
                            ? null
                            : Uuid::fromHexToBytes($entry->salesChannelId),
                        'value_type' => ConfigurationKeys::expectedType($entry->key),
                        'configuration_value' => json_encode(['_value' => $entry->value], \JSON_THROW_ON_ERROR),
                        'owner_token' => null,
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

    /**
     * @param callable(list<ConfigurationBackupEntry>, list<ConfigurationBackupEntry>): void $restore
     *
     * @return bool true, wenn eine ausdrückliche Snapshot-Generation vorlag
     */
    public function restoreTransaction(callable $restore): bool
    {
        $this->ensureTable();

        try {
            return $this->connection->transactional(function () use ($restore): bool {
                $this->lockOwnerRow();
                $headerRows = $this->connection->fetchAllAssociative(
                    'SELECT generation_id FROM `' . self::TABLE_NAME . '` WHERE record_type = ?',
                    [self::RECORD_HEADER],
                );
                if ($headerRows === []) {
                    $this->assertOnlyOwnerRecordExists();

                    return false;
                }
                if (count($headerRows) !== 1) {
                    throw new \RuntimeException('Die Konfigurationssicherung besitzt keine eindeutige Generation.');
                }

                $generation = $headerRows[0]['generation_id'] ?? null;
                if (!is_string($generation) || strlen($generation) !== 16) {
                    throw new \RuntimeException('Die Konfigurationssicherung besitzt eine ungültige Generation.');
                }
                $rows = $this->connection->fetchAllAssociative(
                    'SELECT config_key, configuration_value, sales_channel_id, value_type, generation_id FROM `' . self::TABLE_NAME . '` WHERE record_type = ? ORDER BY config_key ASC, sales_channel_id ASC',
                    [self::RECORD_VALUE],
                );
                $entries = [];
                foreach ($rows as $row) {
                    if (($row['generation_id'] ?? null) !== $generation) {
                        throw new \RuntimeException('Die Konfigurationssicherung enthält eine fremde Generation.');
                    }
                    $entries[] = $this->entryFromBackupRow($row);
                }
                $this->assertSnapshotRecordCount(count($entries));
                $this->assertUniqueScopes($entries);

                $currentEntries = $this->readOwnedSystemConfiguration();
                $this->assertUniqueScopes($currentEntries);
                $restore($entries, $currentEntries);
                $this->assertSnapshotMatchesSystemConfiguration($entries);

                // Absichtlich keine Löschung: Ein späterer Fehler im Shopware-
                // Installationsablauf muss einen sicheren Wiederholungsversuch erlauben.
                return true;
            });
        } catch (\Throwable $exception) {
            throw new \RuntimeException(
                'Die Plugin-Konfiguration konnte nicht vollständig wiederhergestellt werden.',
                0,
                $exception,
            );
        }
    }

    /**
     * @param callable(list<ConfigurationBackupEntry>): void $remove
     *
     * Entfernt die positiv gelisteten Shopware-Werte transaktional. Shopware 6.6
     * löscht beim No-Keep-Lauf sonst nur globale, aber nicht alle Kanalwerte.
     */
    public function removeConfigurationTransaction(callable $remove): void
    {
        $this->ensureTable();

        try {
            $this->connection->transactional(function () use ($remove): void {
                $this->lockOwnerRow();
                $currentEntries = $this->readOwnedSystemConfiguration();
                $this->assertUniqueScopes($currentEntries);
                $remove($currentEntries);
                $this->assertSnapshotMatchesSystemConfiguration([]);
            });
        } catch (\Throwable $exception) {
            throw new \RuntimeException(
                'Die Plugin-Konfiguration konnte nicht vollständig entfernt werden.',
                0,
                $exception,
            );
        }
    }

    /** Entfernt nur eine nachweislich eigene Tabelle; Namensgleichheit allein genügt nie. */
    public function dropTable(): void
    {
        if (!$this->tableExists()) {
            return;
        }

        $this->assertOwnedSchema();
        $this->connection->executeStatement('DROP TABLE `' . self::TABLE_NAME . '`');
    }

    /** Legt die Tabelle auch vor Shopwares nachgelagertem Migrationslauf an. */
    public function ensureTable(): void
    {
        if ($this->tableExists()) {
            $this->assertOwnedSchema();

            return;
        }

        $platform = $this->connection->getDatabasePlatform();
        if ($platform instanceof AbstractMySQLPlatform) {
            $this->connection->executeStatement(<<<'SQL'
                CREATE TABLE IF NOT EXISTS `mgd_ai_image_labels_config_backup` (
                    `id` BINARY(16) NOT NULL,
                    `record_type` VARCHAR(16) NOT NULL,
                    `generation_id` BINARY(16) NULL,
                    `scope_hash` CHAR(64) NOT NULL,
                    `config_key` VARCHAR(255) NULL,
                    `sales_channel_id` BINARY(16) NULL,
                    `value_type` VARCHAR(16) NULL,
                    `configuration_value` JSON NULL,
                    `owner_token` VARCHAR(128) NULL,
                    `created_at` DATETIME(3) NOT NULL,
                    `updated_at` DATETIME(3) NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uniq.mgd_ai_config_backup.scope` (`scope_hash`),
                    CONSTRAINT `json.mgd_ai_config_backup.value` CHECK (`configuration_value` IS NULL OR JSON_VALID(`configuration_value`))
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                SQL);
            // Doctrine DBAL 3 schreibt die Klasse „SqlitePlatform“, DBAL 4 dagegen
            // „SQLitePlatform“. Ein Vergleich des vollständig normalisierten Namens
            // hält beide Shopware-Linien kompatibel, ohne eine dort fehlende Klasse
            // bereits beim statischen Analysieren auflösen zu müssen.
        } elseif (strtolower($platform::class) === 'doctrine\\dbal\\platforms\\sqliteplatform') {
            $this->connection->executeStatement(<<<'SQL'
                CREATE TABLE IF NOT EXISTS `mgd_ai_image_labels_config_backup` (
                    `id` BLOB NOT NULL PRIMARY KEY,
                    `record_type` VARCHAR(16) NOT NULL,
                    `generation_id` BLOB NULL,
                    `scope_hash` VARCHAR(64) NOT NULL,
                    `config_key` VARCHAR(255) NULL,
                    `sales_channel_id` BLOB NULL,
                    `value_type` VARCHAR(16) NULL,
                    `configuration_value` TEXT NULL,
                    `owner_token` VARCHAR(128) NULL,
                    `created_at` TEXT NOT NULL,
                    `updated_at` TEXT NULL
                )
                SQL);
            $this->connection->executeStatement(
                'CREATE UNIQUE INDEX IF NOT EXISTS `uniq.mgd_ai_config_backup.scope` ON `' . self::TABLE_NAME . '` (`scope_hash`)',
            );
        } else {
            throw new \RuntimeException('Die Datenbankplattform wird für die Konfigurationssicherung nicht unterstützt.');
        }

        // Nach CREATE IF NOT EXISTS wird zuerst die Struktur geprüft. So erhält
        // eine zufällig gleichnamige Fremdtabelle niemals unseren Eigentümermarker.
        $this->assertExpectedColumnsAndIndexes();
        try {
            $this->insertRecord([
                'id' => hex2bin('8f3b109b4bf24b2a80b265017572c079'),
                'record_type' => self::RECORD_OWNER,
                'generation_id' => null,
                'scope_hash' => self::OWNER_SCOPE_HASH,
                'config_key' => null,
                'sales_channel_id' => null,
                'value_type' => null,
                'configuration_value' => null,
                'owner_token' => self::OWNER_TOKEN,
                'created_at' => $this->now(),
                'updated_at' => null,
            ]);
        } catch (\Throwable) {
            // Ein paralleler eigener Ersteller darf bereits dieselbe eindeutige
            // Eigentümerzeile geschrieben haben. Die folgende Prüfung entscheidet.
        }
        $this->assertOwnedSchema();
    }

    /** @param array<string, mixed> $values */
    private function insertRecord(array $values): void
    {
        $this->connection->insert(self::TABLE_NAME, $values);
    }

    private function tableExists(): bool
    {
        return $this->connection->createSchemaManager()->tablesExist([self::TABLE_NAME]);
    }

    private function assertOwnedSchema(): void
    {
        $this->assertExpectedColumnsAndIndexes();
        $rows = $this->connection->fetchAllAssociative(
            'SELECT owner_token FROM `' . self::TABLE_NAME . '` WHERE record_type = ?',
            [self::RECORD_OWNER],
        );
        if (count($rows) !== 1 || ($rows[0]['owner_token'] ?? null) !== self::OWNER_TOKEN) {
            throw new \RuntimeException('Die gleichnamige Tabelle gehört nicht nachweislich zu diesem Plugin.');
        }
    }

    private function assertExpectedColumnsAndIndexes(): void
    {
        $schema = $this->connection->createSchemaManager();
        $columns = array_keys($schema->listTableColumns(self::TABLE_NAME));
        sort($columns);
        $expected = self::COLUMNS;
        sort($expected);
        if ($columns !== $expected) {
            throw new \RuntimeException('Die gleichnamige Tabelle besitzt nicht das erwartete Plugin-Schema.');
        }

        $indexes = $schema->listTableIndexes(self::TABLE_NAME);
        $hasPrimaryId = false;
        $hasUniqueScope = false;
        foreach ($indexes as $index) {
            $columnNames = $index->getColumns();
            $hasPrimaryId = $hasPrimaryId || ($index->isPrimary() && $columnNames === ['id']);
            $hasUniqueScope = $hasUniqueScope || ($index->isUnique() && $columnNames === ['scope_hash']);
        }
        if (!$hasPrimaryId || !$hasUniqueScope) {
            throw new \RuntimeException('Die gleichnamige Tabelle besitzt nicht die erwarteten Schutzindizes.');
        }
    }

    private function lockOwnerRow(): void
    {
        $suffix = $this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform ? ' FOR UPDATE' : '';
        $token = $this->connection->fetchOne(
            'SELECT owner_token FROM `' . self::TABLE_NAME . '` WHERE record_type = ?' . $suffix,
            [self::RECORD_OWNER],
        );
        if ($token !== self::OWNER_TOKEN) {
            throw new \RuntimeException('Die Eigentümersperre der Konfigurationssicherung fehlt.');
        }
    }

    private function assertOnlyOwnerRecordExists(): void
    {
        $count = $this->databaseCount('SELECT COUNT(*) FROM `' . self::TABLE_NAME . '`');
        if ($count !== 1) {
            throw new \RuntimeException('Die Konfigurationssicherung enthält Datensätze ohne Kopfzeile.');
        }
    }

    private function assertSnapshotRecordCount(int $valueCount): void
    {
        $count = $this->databaseCount('SELECT COUNT(*) FROM `' . self::TABLE_NAME . '`');
        if ($count !== $valueCount + 2) {
            throw new \RuntimeException('Die Konfigurationssicherung enthält unerwartete Datensätze.');
        }
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

        return array_map(fn(array $row): ConfigurationBackupEntry => $this->entryFromSystemConfigRow($row), $rows);
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

    private function validatedEntry(mixed $key, mixed $salesChannelBytes, mixed $rawValue, mixed $storedType): ConfigurationBackupEntry
    {
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
        if (!is_int($value) && !is_string($value)) {
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

    /** @param list<ConfigurationBackupEntry> $snapshot */
    private function assertSnapshotMatchesSystemConfiguration(array $snapshot): void
    {
        $actual = $this->readOwnedSystemConfiguration();
        $this->assertUniqueScopes($actual);
        if (count($actual) !== count($snapshot)) {
            throw new \RuntimeException('Die wiederhergestellte Plugin-Konfiguration weicht vom Snapshot ab.');
        }
        foreach ($snapshot as $index => $expected) {
            $restored = $actual[$index];
            if ($restored->key !== $expected->key
                || $restored->salesChannelId !== $expected->salesChannelId
                || gettype($restored->value) !== gettype($expected->value)
                || $restored->value !== $expected->value) {
                throw new \RuntimeException('Die wiederhergestellte Plugin-Konfiguration weicht vom Snapshot ab.');
            }
        }
    }

    private function now(): string
    {
        return (new \DateTimeImmutable())->format(Defaults::STORAGE_DATE_TIME_FORMAT);
    }

    private function databaseCount(string $sql): int
    {
        $value = $this->connection->fetchOne($sql);
        if (!is_int($value) && (!is_string($value) || !ctype_digit($value))) {
            throw new \RuntimeException('Die Datenbank lieferte keinen gültigen Zählerwert.');
        }

        return intval($value);
    }
}
