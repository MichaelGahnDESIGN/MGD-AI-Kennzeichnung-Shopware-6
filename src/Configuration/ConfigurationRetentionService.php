<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Configuration;

use Shopware\Core\System\SystemConfig\SystemConfigService;

/** Koordiniert ausschließlich den Datenerhalt im Shopware-Plugin-Lebenszyklus. */
final readonly class ConfigurationRetentionService
{
    public function __construct(
        private ConfigurationBackupStorage $storage,
        private SystemConfigService $systemConfigService,
    ) {
    }

    /** Ersetzt eine eventuell alte Sicherung durch den aktuellen Datenbankzustand. */
    public function snapshotBeforeKeepUninstall(): void
    {
        $this->storage->replaceSnapshot();
    }

    /**
     * Stellt nach Shopwares Default-Import die vorherigen Werte wieder her.
     *
     * Der Storage validiert zuerst den gesamten Snapshot und führt Schreiben und
     * Löschen anschließend in derselben Datenbanktransaktion aus.
     */
    public function restoreAfterInstall(): void
    {
        $this->storage->restoreTransaction(function (array $entries): void {
            foreach ($entries as $entry) {
                $this->systemConfigService->set($entry->key, $entry->value, $entry->salesChannelId);
            }
        });
    }

    /** Entfernt bei einer Deinstallation ohne Datenerhalt ausschließlich die Backup-Tabelle. */
    public function removeBackupWithoutUserData(): void
    {
        $this->storage->dropTable();
    }
}
