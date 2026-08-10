<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Integration\Setup;

use PHPUnit\Framework\TestCase;
use Shopware\Core\TestBootstrapper;

/** Zentraler, geheimnisfreier Sicherheitszaun für alle echten Shopware-Tests. */
final readonly class ShopwareIntegrationTestBootstrap
{
    private const TASK_13_COMMAND = "MGD_SHOPWARE_INTEGRATION_TESTS=1 MGD_SHOPWARE_TEST_DATABASE_URL='mysql://.../mgd_shopware_test' composer test:integration";

    public static function boot(string $pluginComposerFile): void
    {
        if (($_SERVER['MGD_SHOPWARE_INTEGRATION_TESTS'] ?? getenv('MGD_SHOPWARE_INTEGRATION_TESTS')) !== '1') {
            TestCase::markTestSkipped('Task 13: Ausführen mit „' . self::TASK_13_COMMAND . '“.');
        }

        $databaseUrl = $_SERVER['MGD_SHOPWARE_TEST_DATABASE_URL']
            ?? $_ENV['MGD_SHOPWARE_TEST_DATABASE_URL']
            ?? getenv('MGD_SHOPWARE_TEST_DATABASE_URL');
        $validated = ShopwareTestDatabaseConfiguration::validate(is_string($databaseUrl) ? $databaseUrl : null);

        (new TestBootstrapper())
            ->setPlatformEmbedded(false)
            ->setEnableCommercial(false)
            ->setLoadEnvFile(false)
            ->setDatabaseUrl($validated)
            ->addCallingPlugin($pluginComposerFile)
            ->setForceInstallPlugins(true)
            ->bootstrap();
    }
}
