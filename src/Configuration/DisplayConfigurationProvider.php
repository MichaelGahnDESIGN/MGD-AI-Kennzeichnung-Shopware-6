<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Configuration;

use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Liest Anzeigeeinstellungen aus Shopware und normalisiert sie vor Gebrauch.
 *
 * Sowohl globale als auch verkaufskanalspezifische Werte sind gespeicherte
 * Eingaben und werden deshalb nie ohne eine erneute Sicherheitsprüfung genutzt.
 */
final class DisplayConfigurationProvider
{
    /** Der eindeutige Shopware-Domainpräfix dieser Plugin-Konfiguration. */
    public const CONFIG_DOMAIN = 'MGDAIImageLabels.config.';

    /**
     * @param SystemConfigService $systemConfigService Shopwares Dienst für Systemkonfigurationen.
     * @param DisplayConfigurationNormalizer $normalizer Prüft alle gelesenen Werte.
     */
    public function __construct(
        private SystemConfigService $systemConfigService,
        private DisplayConfigurationNormalizer $normalizer = new DisplayConfigurationNormalizer(),
    ) {
    }

    /**
     * Gibt die sichere Konfiguration für einen optionalen Verkaufskanal zurück.
     *
     * Die Vererbung berücksichtigt globale Werte als Grundlage und gezielte
     * Verkaufskanalwerte als Überschreibung, wie es Shopware vorsieht.
     *
     * @param ?string $salesChannelId Die optionale Shopware-ID des Verkaufskanals.
     */
    public function get(?string $salesChannelId = null): DisplayConfiguration
    {
        $domainValues = $this->systemConfigService->getDomain(
            self::CONFIG_DOMAIN,
            $salesChannelId,
            true,
        );

        $values = [];

        foreach ($domainValues as $key => $value) {
            if (!is_string($key) || !str_starts_with($key, self::CONFIG_DOMAIN)) {
                continue;
            }

            $values[substr($key, strlen(self::CONFIG_DOMAIN))] = $value;
        }

        return $this->normalizer->normalize($values);
    }
}
