<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Configuration;

/** Ein bereits vollständig geprüfter Konfigurationswert aus der Sicherung. */
final readonly class ConfigurationBackupEntry
{
    public function __construct(
        public string $key,
        public ?string $salesChannelId,
        public int|string|bool|float $value,
    ) {
    }
}
