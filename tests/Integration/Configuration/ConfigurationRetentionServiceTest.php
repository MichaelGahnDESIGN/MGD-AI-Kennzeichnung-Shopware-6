<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Integration\Configuration;

use Doctrine\DBAL\Connection;
use MGDAIImageLabels\Configuration\ConfigurationBackupStorage;
use MGDAIImageLabels\Configuration\ConfigurationKeys;
use MGDAIImageLabels\Configuration\ConfigurationRetentionService;
use MGDAIImageLabels\Tests\Integration\Setup\ShopwareIntegrationTestBootstrap;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Prüft Datenerhalt und Cache-Invalidierung gegen Shopwares echte Tabellen.
 *
 * Die gemeinsame Sicherheitsvorprüfung akzeptiert ausschließlich eine bewusst
 * freigegebene, eindeutig benannte Testdatenbank. Alle Werte werden anschließend
 * durch Shopwares IntegrationTestBehaviour transaktional zurückgerollt.
 */
#[Group('integration')]
final class ConfigurationRetentionServiceTest extends TestCase
{
    use IntegrationTestBehaviour;

    public static function setUpBeforeClass(): void
    {
        ShopwareIntegrationTestBootstrap::boot(dirname(__DIR__, 3) . '/composer.json');
    }

    public function testSnapshotAndRestorePreserveGlobalAndSalesChannelValues(): void
    {
        $connection = self::getContainer()->get(Connection::class);
        $systemConfig = self::getContainer()->get(SystemConfigService::class);
        self::assertInstanceOf(Connection::class, $connection);
        self::assertInstanceOf(SystemConfigService::class, $systemConfig);
        $storage = new ConfigurationBackupStorage($connection);
        $retention = new ConfigurationRetentionService($storage, $systemConfig);
        $salesChannelId = $connection->fetchOne(
            'SELECT LOWER(HEX(id)) FROM sales_channel ORDER BY created_at ASC, id ASC LIMIT 1',
        );
        self::assertIsString($salesChannelId, 'Die Shopware-Testinstallation benötigt einen realen Verkaufskanal.');
        self::assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $salesChannelId);

        $systemConfig->set(ConfigurationKeys::LANGUAGE, 'de');
        $systemConfig->set(ConfigurationKeys::FONT_SIZE, 17);
        $systemConfig->set(ConfigurationKeys::LANGUAGE, 'en', $salesChannelId);
        $retention->snapshotBeforeKeepUninstall();

        // Simuliert exakt die vor plugin->install() geschriebenen Core-Defaults.
        $systemConfig->set(ConfigurationKeys::LANGUAGE, 'auto');
        $systemConfig->set(ConfigurationKeys::FONT_SIZE, 6);
        $systemConfig->set(ConfigurationKeys::LANGUAGE, 'auto', $salesChannelId);
        $retention->restoreAfterInstall();

        self::assertSame('de', $systemConfig->get(ConfigurationKeys::LANGUAGE));
        self::assertSame(17, $systemConfig->get(ConfigurationKeys::FONT_SIZE));
        self::assertSame('en', $systemConfig->get(ConfigurationKeys::LANGUAGE, $salesChannelId));
        self::assertSame(0, $connection->fetchOne(
            'SELECT COUNT(*) FROM `' . ConfigurationBackupStorage::TABLE_NAME . '`',
        ));
    }
}
