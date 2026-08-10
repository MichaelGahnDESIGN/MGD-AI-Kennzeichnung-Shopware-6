<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Storefront\Thumbnail;

/**
 * Unveränderliche, geschlossene Darstellungsentscheidung für ein Thumbnail.
 */
final readonly class ThumbnailPresentation
{
    private function __construct(
        public bool $labelAllowed,
        public bool $intrinsicLayout,
        public bool $floatEndLayout,
    ) {}

    /** Das Label bleibt semantisch und visuell vollständig ausgeschlossen. */
    public static function excluded(): self
    {
        return new self(false, true, false);
    }

    /** Der Rahmen folgt der nachgewiesenen Breite und Höhe seines Layoutslots. */
    public static function fill(): self
    {
        return new self(true, false, false);
    }

    /** Der Rahmen folgt konservativ den intrinsischen Maßen des Bildes. */
    public static function intrinsic(): self
    {
        return new self(true, true, false);
    }

    /**
     * Der intrinsische Rahmen übernimmt den festen End-Float des offiziellen
     * Zahlungs- oder Versandlogos und hält dessen Bild im normalen Innenfluss.
     */
    public static function intrinsicFloatEnd(): self
    {
        return new self(true, true, true);
    }
}
