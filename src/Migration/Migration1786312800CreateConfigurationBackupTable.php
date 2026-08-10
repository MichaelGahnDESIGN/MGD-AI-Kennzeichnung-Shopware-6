<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Migration;

use Doctrine\DBAL\Connection;
use MGDAIImageLabels\Configuration\ConfigurationBackupStorage;
use Shopware\Core\Framework\Migration\MigrationStep;

/** Legt den datensparsamen Zwischenspeicher für „Benutzerdaten behalten“ an. */
final class Migration1786312800CreateConfigurationBackupTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1786312800;
    }

    public function update(Connection $connection): void
    {
        (new ConfigurationBackupStorage($connection))->ensureTable();
    }

    public function updateDestructive(Connection $connection): void
    {
        // Die Tabelle wird nur im bewussten No-Keep-Lebenszyklus entfernt.
    }
}
