<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Cms\Philosophy;

/** Kleines unveränderliches Ergebnis der wiederholbaren Seitenerstellung. */
final readonly class PhilosophyPageCreationResult
{
    public function __construct(
        public bool $created,
        public string $cmsPageId,
    ) {
    }
}
