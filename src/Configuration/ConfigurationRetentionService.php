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

    /** Stellt nach Shopwares Default-Import exakt die vorherige Belegung wieder her. */
    public function restoreAfterInstall(): void
    {
        $this->storage->restoreTransaction(function (array $entries, array $currentEntries): void {
            // Auch ein ausdrücklicher leerer Snapshot ist fachlich relevant: Die
            // zuvor von Shopware importierten Defaults müssen dann verschwinden.
            foreach ($currentEntries as $entry) {
                $this->systemConfigService->delete($entry->key, $entry->salesChannelId);
            }
            foreach ($entries as $entry) {
                $this->systemConfigService->set($entry->key, $entry->value, $entry->salesChannelId);
            }
        });
    }

    /** Entfernt bei No-Keep alle eigenen Scopes und danach die eigene Backup-Tabelle. */
    public function removeBackupWithoutUserData(): void
    {
        $this->storage->removeConfigurationTransaction(function (array $currentEntries): void {
            foreach ($currentEntries as $entry) {
                $this->systemConfigService->delete($entry->key, $entry->salesChannelId);
            }
        });
        $this->storage->dropTable();
    }
}
