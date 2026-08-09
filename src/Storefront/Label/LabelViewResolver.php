<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Storefront\Label;

use MGDAIImageLabels\Configuration\DisplayConfigurationProvider;
use MGDAIImageLabels\Domain\LabelPosition;
use MGDAIImageLabels\Domain\LabelStatus;
use MGDAIImageLabels\Domain\LabelTheme;
use MGDAIImageLabels\Media\MediaLabelMetadataNormalizer;

/**
 * Verbindet Medienmetadaten, Plugin-Konfiguration und Verkaufskanalsprache.
 *
 * Der Resolver ist die einzige Übergabestelle zum Storefront-Viewmodell. Alle
 * gespeicherten Werte passieren zuvor ihre zuständigen Normalizer; danach
 * werden sie erneut als Domain-Enums typisiert. Rohe Custom Fields können so
 * weder in CSS-Klassen noch in Snippet-Schlüssel oder Zahlenwerte gelangen.
 */
final class LabelViewResolver
{
    public function __construct(
        private MediaLabelMetadataNormalizer $metadataNormalizer,
        private DisplayConfigurationProvider $configurationProvider,
        private LabelLanguageResolver $languageResolver,
    ) {
    }

    /**
     * @param array<mixed> $customFields Nicht vertrauenswürdige Custom Fields des Mediums.
     * @param ?string $salesChannelId Optionale Shopware-ID für die Sprachkonfiguration.
     * @param string $salesChannelLocale Locale des aktuellen Storefront-Kontexts.
     */
    public function resolve(
        array $customFields,
        ?string $salesChannelId,
        string $salesChannelLocale,
    ): LabelView {
        $metadata = $this->metadataNormalizer->normalize($customFields);

        if (!$metadata->isVisible()) {
            return LabelView::hidden();
        }

        $configuration = $this->configurationProvider->get($salesChannelId);
        $language = $this->languageResolver->resolve($configuration->language, $salesChannelLocale);

        return LabelView::visible(
            LabelStatus::from($metadata->status),
            LabelPosition::from($metadata->position ?? $configuration->position),
            LabelTheme::from($metadata->theme ?? $configuration->theme),
            $language,
            $configuration,
        );
    }
}
