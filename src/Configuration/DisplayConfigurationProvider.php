<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Configuration;

use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Liest Anzeigeeinstellungen aus Shopwares cache-integrierter API.
 *
 * Sowohl globale als auch verkaufskanalspezifische Werte sind gespeicherte
 * Eingaben und werden deshalb nie ohne eine erneute Sicherheitsprüfung genutzt.
 */
final class DisplayConfigurationProvider
{
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
     * Alle Darstellungswerte sind global. Nur die Sprache wird bei einer
     * Verkaufskanal-ID über Shopwares regulären Fallback gelesen.
     *
     * @param ?string $salesChannelId Die optionale Shopware-ID des Verkaufskanals.
     */
    public function get(?string $salesChannelId = null): DisplayConfiguration
    {
        return $this->normalizer->normalize([
            'fontSize' => $this->systemConfigService->get(ConfigurationKeys::FONT_SIZE),
            'offset' => $this->systemConfigService->get(ConfigurationKeys::OFFSET),
            'paddingY' => $this->systemConfigService->get(ConfigurationKeys::PADDING_Y),
            'paddingX' => $this->systemConfigService->get(ConfigurationKeys::PADDING_X),
            'radius' => $this->systemConfigService->get(ConfigurationKeys::RADIUS),
            'blur' => $this->systemConfigService->get(ConfigurationKeys::BLUR),
            'position' => $this->systemConfigService->get(ConfigurationKeys::POSITION),
            'theme' => $this->systemConfigService->get(ConfigurationKeys::THEME),
            'language' => $this->systemConfigService->get(ConfigurationKeys::LANGUAGE, $salesChannelId),
        ]);
    }
}
