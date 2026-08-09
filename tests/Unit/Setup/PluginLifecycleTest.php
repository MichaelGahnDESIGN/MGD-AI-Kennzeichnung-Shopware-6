<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Setup;

use MGDAIImageLabels\MGDAIImageLabels;
use MGDAIImageLabels\Setup\CustomFieldSetInstaller;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
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
        [$plugin, $setRepository, $relationRepository, $installer] = $this->pluginWithRepositories();
        $context = Context::createDefaultContext();
        $installer->install($context);
        $uninstallContext = $this->createStub(UninstallContext::class);
        $uninstallContext->method('getContext')->willReturn($context);
        $uninstallContext->method('keepUserData')->willReturn(true);

        $plugin->uninstall($uninstallContext);

        self::assertCount(1, $setRepository->rows);
        self::assertCount(1, $relationRepository->rows);
        self::assertSame([], $setRepository->deletePayloads);
    }

    /** Ohne Datenerhalt entfernt der Plugin-Lebenszyklus das eigene Set. */
    public function testUninstallRemovesDataWhenNotKept(): void
    {
        [$plugin, $setRepository, , $installer] = $this->pluginWithRepositories();
        $context = Context::createDefaultContext();
        $installer->install($context);
        $uninstallContext = $this->createStub(UninstallContext::class);
        $uninstallContext->method('getContext')->willReturn($context);
        $uninstallContext->method('keepUserData')->willReturn(false);

        $plugin->uninstall($uninstallContext);

        self::assertSame([], $setRepository->rows);
        self::assertCount(1, $setRepository->deletePayloads);
    }

    /**
     * @return array{
     *     MGDAIImageLabels,
     *     InMemoryEntityRepository,
     *     InMemoryEntityRepository,
     *     CustomFieldSetInstaller,
     *     Container
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

        $plugin = new MGDAIImageLabels(false, dirname(__DIR__, 3));
        $plugin->setContainer($container);

        return [$plugin, $setRepository, $relationRepository, $installer, $container];
    }

    /** @return EntityRepository<covariant EntityCollection<covariant Entity>> */
    private function repository(InMemoryEntityRepository $state): EntityRepository
    {
        return $state->connect($this->createMock(EntityRepository::class));
    }
}
