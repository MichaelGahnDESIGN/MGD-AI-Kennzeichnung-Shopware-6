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
    /** Schlüssel der globalen Schriftgröße. */
    private const CONFIG_KEY_FONT_SIZE = 'MGDAIImageLabels.config.fontSize';

    /** Schlüssel des globalen Bildrandabstands. */
    private const CONFIG_KEY_OFFSET = 'MGDAIImageLabels.config.offset';

    /** Schlüssel des globalen vertikalen Innenabstands. */
    private const CONFIG_KEY_PADDING_Y = 'MGDAIImageLabels.config.paddingY';

    /** Schlüssel des globalen horizontalen Innenabstands. */
    private const CONFIG_KEY_PADDING_X = 'MGDAIImageLabels.config.paddingX';

    /** Schlüssel des globalen Eckenradius. */
    private const CONFIG_KEY_RADIUS = 'MGDAIImageLabels.config.radius';

    /** Schlüssel der globalen Hintergrundunschärfe. */
    private const CONFIG_KEY_BLUR = 'MGDAIImageLabels.config.blur';

    /** Schlüssel der globalen Position. */
    private const CONFIG_KEY_POSITION = 'MGDAIImageLabels.config.position';

    /** Schlüssel des globalen Themes. */
    private const CONFIG_KEY_THEME = 'MGDAIImageLabels.config.theme';

    /** Schlüssel der optional verkaufskanalspezifischen Sprache. */
    private const CONFIG_KEY_LANGUAGE = 'MGDAIImageLabels.config.language';

    /** Eindeutiger lokaler Cache-Schlüssel für die globale Abfrage. */
    private const GLOBAL_CACHE_KEY = 'global';

    /**
     * Hält ausschließlich während dieser Provider-Instanz bereits geprüfte
     * Konfigurationen. Es gibt bewusst keinen statischen oder Prozesscache.
     *
     * @var array<string, DisplayConfiguration>
     */
    private array $configurationCache = [];

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
        $cacheKey = $this->cacheKey($salesChannelId);

        if (isset($this->configurationCache[$cacheKey])) {
            return $this->configurationCache[$cacheKey];
        }

        $configuration = $this->normalizer->normalize([
            'fontSize' => $this->systemConfigService->get(self::CONFIG_KEY_FONT_SIZE),
            'offset' => $this->systemConfigService->get(self::CONFIG_KEY_OFFSET),
            'paddingY' => $this->systemConfigService->get(self::CONFIG_KEY_PADDING_Y),
            'paddingX' => $this->systemConfigService->get(self::CONFIG_KEY_PADDING_X),
            'radius' => $this->systemConfigService->get(self::CONFIG_KEY_RADIUS),
            'blur' => $this->systemConfigService->get(self::CONFIG_KEY_BLUR),
            'position' => $this->systemConfigService->get(self::CONFIG_KEY_POSITION),
            'theme' => $this->systemConfigService->get(self::CONFIG_KEY_THEME),
            'language' => $this->systemConfigService->get(self::CONFIG_KEY_LANGUAGE, $salesChannelId),
        ]);

        $this->configurationCache[$cacheKey] = $configuration;

        return $configuration;
    }

    /**
     * Liefert einen kollisionsfreien, instanzlokalen Schlüssel für den Cache.
     *
     * @param ?string $salesChannelId Die optionale Shopware-ID des Verkaufskanals.
     */
    private function cacheKey(?string $salesChannelId): string
    {
        if ($salesChannelId === null) {
            return self::GLOBAL_CACHE_KEY;
        }

        return 'sales-channel:' . $salesChannelId;
    }
}
