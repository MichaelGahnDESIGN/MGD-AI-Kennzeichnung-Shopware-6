<?php

declare(strict_types=1);

namespace MGDAIImageLabels;

use MGDAIImageLabels\Setup\CustomFieldSetInstaller;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;

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

        $this->customFieldSetInstaller()->install($installContext->getContext());
    }

    /** Stellt bei Updates ebenfalls die jeweils aktuelle Definition sicher. */
    public function update(UpdateContext $updateContext): void
    {
        parent::update($updateContext);

        $this->customFieldSetInstaller()->install($updateContext->getContext());
    }

    /**
     * Respektiert Shopwares Datenerhalt-Option und entfernt sonst nur das
     * plugin-eigene Custom-Field-Set.
     */
    public function uninstall(UninstallContext $uninstallContext): void
    {
        // Shopwares eigener Lebenszyklus muss unabhängig vom Datenerhalt zuerst abgeschlossen werden.
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        $this->customFieldSetInstaller()->remove($uninstallContext->getContext());
    }

    /**
     * Holt den gezielt öffentlichen Lebenszyklusdienst aus Shopwares Container.
     *
     * Plugin-Instanzen werden von Shopware selbst erzeugt und erhalten daher
     * keine normale Konstruktorinjektion. Nur dieser eine Dienst ist öffentlich;
     * seine Repository-Abhängigkeiten bleiben reguläre Containerdienste.
     */
    private function customFieldSetInstaller(): CustomFieldSetInstaller
    {
        if ($this->container === null) {
            throw new \RuntimeException('Der Shopware-Service-Container ist für den Plugin-Lebenszyklus nicht verfügbar.');
        }

        $installer = $this->container->get(CustomFieldSetInstaller::class);
        if (!$installer instanceof CustomFieldSetInstaller) {
            throw new \RuntimeException('Der Custom-Field-Installer ist im Shopware-Service-Container nicht verfügbar.');
        }

        return $installer;
    }
}
