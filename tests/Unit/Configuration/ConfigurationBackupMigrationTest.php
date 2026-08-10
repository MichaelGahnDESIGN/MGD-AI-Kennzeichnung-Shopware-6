<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Configuration;

use Doctrine\DBAL\DriverManager;
use MGDAIImageLabels\Migration\Migration1786312800CreateConfigurationBackupTable;
use PHPUnit\Framework\TestCase;

/** Sichert den Shopware-Migrationsvertrag und die idempotente Tabellenerstellung ab. */
final class ConfigurationBackupMigrationTest extends TestCase
{
    public function testMigrationCreatesBackupTableIdempotently(): void
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $migration = new Migration1786312800CreateConfigurationBackupTable();

        $migration->update($connection);
        $migration->update($connection);

        self::assertSame(1786312800, $migration->getCreationTimestamp());
        self::assertSame(1, $connection->fetchOne(
            "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'mgd_ai_image_labels_config_backup'",
        ));
    }
}
