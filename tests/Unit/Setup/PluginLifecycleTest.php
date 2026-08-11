<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Setup;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use MGDAIImageLabels\Configuration\ConfigurationBackupStorage;
use MGDAIImageLabels\Configuration\ConfigurationKeys;
use MGDAIImageLabels\Configuration\ConfigurationRetentionService;
use MGDAIImageLabels\MGDAIImageLabels;
use MGDAIImageLabels\Setup\CustomFieldSetInstaller;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\DependencyInjection\Container;

/** Prüft die schlanke Weiterleitung der Shopware-Lebenszyklusereignisse. */
final class PluginLifecycleTest extends TestCase
{
    /** Installation und Aktualisierung stellen beide die aktuelle Definition sicher. */
    public function testInstallAndUpdateDelegateToInstaller(): void
    {
        [$plugin, $setRepository, $relationRepository, , $container] = $this->pluginWithRepositories();
        $context = Context::createDefaultContext();
        $installContext = $this->createStub(InstallContext::class);
        $installContext->method('getContext')->willReturn($context);
        $updateContext = $this->createStub(UpdateContext::class);
        $updateContext->method('getContext')->willReturn($context);

        $plugin->install($installContext);
        $plugin->update($updateContext);

        self::assertFalse($container->has(CustomFieldSetInstaller::class));
        self::assertCount(2, $setRepository->upsertPayloads);
        self::assertCount(2, $relationRepository->upsertPayloads);
        self::assertCount(1, $setRepository->rows);
        self::assertCount(1, $relationRepository->rows);
    }

    /** Bei gewünschtem Datenerhalt bleiben Set und Relation vollständig bestehen. */
    public function testUninstallKeepsDataWhenRequested(): void
    {
        [$plugin, $setRepository, $relationRepository, $installer, , $connection] = $this->pluginWithRepositories();
        $context = Context::createDefaultContext();
        $installer->install($context);
        $this->insertSystemConfig($connection, ConfigurationKeys::LANGUAGE, 'de');
        $uninstallContext = $this->createStub(UninstallContext::class);
        $uninstallContext->method('getContext')->willReturn($context);
        $uninstallContext->method('keepUserData')->willReturn(true);

        $plugin->uninstall($uninstallContext);

        self::assertCount(1, $setRepository->rows);
        self::assertCount(1, $relationRepository->rows);
        self::assertSame([], $setRepository->deletePayloads);
        self::assertSame(3, $connection->fetchOne('SELECT COUNT(*) FROM mgd_ai_image_labels_config_backup'));
    }

    /** Ohne Datenerhalt entfernt der Plugin-Lebenszyklus das eigene Set. */
    public function testUninstallRemovesDataWhenNotKept(): void
    {
        [$plugin, $setRepository, , $installer, , $connection] = $this->pluginWithRepositories();
        $context = Context::createDefaultContext();
        $installer->install($context);
        (new ConfigurationBackupStorage($connection))->ensureTable();
        $uninstallContext = $this->createStub(UninstallContext::class);
        $uninstallContext->method('getContext')->willReturn($context);
        $uninstallContext->method('keepUserData')->willReturn(false);

        $plugin->uninstall($uninstallContext);

        self::assertSame([], $setRepository->rows);
        self::assertCount(1, $setRepository->deletePayloads);
        self::assertSame(0, $connection->fetchOne("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'mgd_ai_image_labels_config_backup'"));
    }

    /** Reinstall stellt den Snapshot auch ohne registrierte Plugin-Dienste nach den Core-Defaults wieder her. */
    public function testReinstallRestoresBackupWithoutOwnServices(): void
    {
        [$plugin, , , , $container, $connection, $systemConfig] = $this->pluginWithRepositories();
        self::assertFalse($container->has(ConfigurationRetentionService::class));
        $this->insertSystemConfig($connection, ConfigurationKeys::LANGUAGE, 'de');
        (new ConfigurationBackupStorage($connection))->replaceSnapshot();
        $connection->update('system_config', ['configuration_value' => '{"_value":"auto"}'], [
            'configuration_key' => ConfigurationKeys::LANGUAGE,
        ]);
        $systemConfig->expects(self::exactly(2))
            ->method('delete')
            ->with(ConfigurationKeys::LANGUAGE, null)
            ->willReturnCallback(static function () use ($connection): void {
                $connection->delete('system_config', ['configuration_key' => ConfigurationKeys::LANGUAGE]);
            });
        $systemConfig->expects(self::exactly(2))
            ->method('set')
            ->with(ConfigurationKeys::LANGUAGE, 'de', null)
            ->willReturnCallback(static function () use ($connection): void {
                // Bildet den echten SystemConfigService-Schreibzugriff für die Post-Verify-Abfrage ab.
                $connection->insert('system_config', [
                    'id' => Uuid::randomBytes(),
                    'configuration_key' => ConfigurationKeys::LANGUAGE,
                    'configuration_value' => '{"_value":"de"}',
                    'sales_channel_id' => null,
                    'created_at' => '2026-08-10 00:00:00.000',
                ]);
            });
        $context = Context::createDefaultContext();
        $installContext = $this->createStub(InstallContext::class);
        $installContext->method('getContext')->willReturn($context);

        $plugin->install($installContext);

        self::assertSame(3, $connection->fetchOne('SELECT COUNT(*) FROM mgd_ai_image_labels_config_backup'));

        // Ein späterer Shopware-Fehler darf einen zweiten Installationsversuch
        // nicht seiner Wiederherstellungsgrundlage berauben.
        $plugin->install($installContext);
        self::assertSame('{"_value":"de"}', $connection->fetchOne(
            'SELECT configuration_value FROM system_config WHERE configuration_key = ?',
            [ConfigurationKeys::LANGUAGE],
        ));
    }

    /**
     * @return array{
     *     MGDAIImageLabels,
     *     InMemoryEntityRepository,
     *     InMemoryEntityRepository,
     *     CustomFieldSetInstaller,
     *     Container,
     *     Connection,
     *     SystemConfigService&MockObject
     * }
     */
    private function pluginWithRepositories(): array
    {
        $setRepository = new InMemoryEntityRepository();
        $relationRepository = new InMemoryEntityRepository();
        $installer = new CustomFieldSetInstaller(
            $this->repository($setRepository),
            $this->repository($relationRepository),
        );
        $container = new Container();
        $container->set('custom_field_set.repository', $this->repository($setRepository));
        $container->set('custom_field_set_relation.repository', $this->repository($relationRepository));
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $connection->executeStatement(<<<'SQL'
            CREATE TABLE system_config (
                id BLOB NOT NULL PRIMARY KEY,
                configuration_key VARCHAR(255) NOT NULL,
                configuration_value TEXT NOT NULL,
                sales_channel_id BLOB NULL,
                created_at TEXT NOT NULL
            )
            SQL);
        $systemConfig = $this->createMock(SystemConfigService::class);
        $container->set(Connection::class, $connection);
        $container->set(SystemConfigService::class, $systemConfig);

        $plugin = new MGDAIImageLabels(false, dirname(__DIR__, 3));
        $plugin->setContainer($container);

        return [$plugin, $setRepository, $relationRepository, $installer, $container, $connection, $systemConfig];
    }

    /** @return EntityRepository<covariant EntityCollection<covariant Entity>> */
    private function repository(InMemoryEntityRepository $state): EntityRepository
    {
        return $state->connect($this->createMock(EntityRepository::class));
    }

    private function insertSystemConfig(Connection $connection, string $key, int|string $value): void
    {
        $connection->insert('system_config', [
            'id' => Uuid::randomBytes(),
            'configuration_key' => $key,
            'configuration_value' => json_encode(['_value' => $value], \JSON_THROW_ON_ERROR),
            'sales_channel_id' => null,
            'created_at' => '2026-08-10 00:00:00.000',
        ]);
    }
}
