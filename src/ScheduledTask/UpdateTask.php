<?php

declare(strict_types=1);

namespace MGDAIImageLabels\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

/** Ausführung benötigt wie alle Shopware-Aufgaben einen konfigurierten Worker/Scheduler. */
final class UpdateTask extends ScheduledTask
{
    public static function getTaskName(): string { return 'mgd_ai_image_labels.update_check'; }
    // Das Intervall ist nur ein Prüfrhythmus bei aktivierter Automatik.
    // Manuelle Checks bleiben jederzeit über die geschützte Admin-API möglich.
    public static function getDefaultInterval(): int { return 3600; }
}
