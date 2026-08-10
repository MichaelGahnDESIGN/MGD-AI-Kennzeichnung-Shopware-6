<?php

declare(strict_types=1);

namespace MGDAIImageLabels;

use Doctrine\DBAL\Connection;
use MGDAIImageLabels\Configuration\ConfigurationBackupStorage;
use MGDAIImageLabels\Configuration\ConfigurationRetentionService;
use MGDAIImageLabels\Setup\CustomFieldSetInstaller;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Einstiegspunkt der Shopware-Erweiterung.
 *
 * Fachlogik bleibt in kleinen Diensten; diese Klasse koordiniert ausschließlich
 * den sicheren Installations-, Aktualisierungs- und Deinstallationsablauf.
 */
final class MGDAIImageLabels extends Plugin
{
    /** Registriert bei der Installation die aktuelle Custom-Field-Definition. */
    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);

        $this->installer()->install($installContext->getContext());
        $this->retention()->restoreAfterInstall();
    }

    /** Stellt bei Updates ebenfalls die jeweils aktuelle Definition sicher. */
    public function update(UpdateContext $updateContext): void
    {
        parent::update($updateContext);

        $this->installer()->install($updateContext->getContext());
    }

    /**
     * Respektiert Shopwares Datenerhalt-Option und entfernt sonst nur das
     * plugin-eigene Custom-Field-Set.
     */
    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            // Der Snapshot muss vor Shopwares nachgelagerter Konfigurationsbehandlung vollständig sein.
            $this->retention()->snapshotBeforeKeepUninstall();
            parent::uninstall($uninstallContext);

            return;
        }

        parent::uninstall($uninstallContext);
        $this->installer()->remove($uninstallContext->getContext());
        $this->retention()->removeBackupWithoutUserData();
    }

    /**
     * Liefert den Installer auch dann, wenn Plugin-Dienste noch nicht geladen sind.
     *
     * Beim Installieren oder Aktualisieren eines inaktiven Plugins ist die eigene
     * services.xml nicht garantiert Teil des laufenden Containers. Deshalb wird
     * bevorzugt der registrierte Dienst genutzt und andernfalls ausschließlich
     * aus Shopwares garantiert öffentlichen Core-Repositories aufgebaut.
     */
    private function installer(): CustomFieldSetInstaller
    {
        if ($this->container === null) {
            throw new \RuntimeException('Der Shopware-Service-Container ist für den Plugin-Lebenszyklus nicht verfügbar.');
        }

        if ($this->container->has(CustomFieldSetInstaller::class)) {
            $installer = $this->container->get(CustomFieldSetInstaller::class);
            if (!$installer instanceof CustomFieldSetInstaller) {
                throw new \RuntimeException('Der registrierte Custom-Field-Installer besitzt einen unerwarteten Typ.');
            }

            return $installer;
        }

        return new CustomFieldSetInstaller(
            $this->coreRepository('custom_field_set.repository'),
            $this->coreRepository('custom_field_set_relation.repository'),
        );
    }

    /**
     * Liefert den Datenerhalt-Dienst auch während eines inaktiven Lebenszyklus.
     *
     * Beim Reinstall ist die eigene services.xml nicht zuverlässig geladen.
     * Der Fallback verwendet deshalb nur Shopwares öffentliche Core-Dienste.
     */
    private function retention(): ConfigurationRetentionService
    {
        if ($this->container === null) {
            throw new \RuntimeException('Der Shopware-Service-Container ist für den Plugin-Lebenszyklus nicht verfügbar.');
        }

        if ($this->container->has(ConfigurationRetentionService::class)) {
            $retention = $this->container->get(ConfigurationRetentionService::class);
            if (!$retention instanceof ConfigurationRetentionService) {
                throw new \RuntimeException('Der registrierte Konfigurations-Datenerhalt besitzt einen unerwarteten Typ.');
            }

            return $retention;
        }

        $connection = $this->container->get(Connection::class);
        $systemConfigService = $this->container->get(SystemConfigService::class);
        if (!$connection instanceof Connection || !$systemConfigService instanceof SystemConfigService) {
            throw new \RuntimeException('Erforderliche Shopware-Core-Dienste für den Konfigurations-Datenerhalt fehlen.');
        }

        return new ConfigurationRetentionService(
            new ConfigurationBackupStorage($connection),
            $systemConfigService,
        );
    }

    /**
     * Holt ein garantiert öffentliches Core-Repository mit statischer Fehlerausgabe.
     *
     * @return EntityRepository<covariant EntityCollection<covariant Entity>>
     */
    private function coreRepository(string $serviceId): EntityRepository
    {
        if ($this->container === null || !$this->container->has($serviceId)) {
            throw new \RuntimeException('Ein erforderliches Shopware-Core-Repository ist nicht verfügbar.');
        }

        $repository = $this->container->get($serviceId);
        if (!$repository instanceof EntityRepository) {
            throw new \RuntimeException('Ein erforderliches Shopware-Core-Repository besitzt einen unerwarteten Typ.');
        }

        return $repository;
    }
}
