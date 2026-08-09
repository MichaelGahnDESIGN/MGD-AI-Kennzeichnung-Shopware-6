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
    ) {}

    /** Das Label bleibt semantisch und visuell vollständig ausgeschlossen. */
    public static function excluded(): self
    {
        return new self(false, true);
    }

    /** Der Rahmen folgt der nachgewiesenen Breite und Höhe seines Layoutslots. */
    public static function fill(): self
    {
        return new self(true, false);
    }

    /** Der Rahmen folgt konservativ den intrinsischen Maßen des Bildes. */
    public static function intrinsic(): self
    {
        return new self(true, true);
    }
}
