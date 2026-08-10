<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Cms\BackgroundImage;

/**
 * Unveränderliche, vollständig normalisierte Storefront-Darstellung.
 *
 * Die privaten Zuordnungen stellen sicher, dass selbst ein versehentlich
 * falscher Aufrufer niemals freie CSS-Klassen in das Template geben kann.
 */
final readonly class BackgroundImagePresentation
{
    private function __construct(
        public string $heightClass,
        public string $horizontalClass,
        public string $verticalClass,
        public string $fallbackClass,
        public bool $decorative,
        public string $altText,
    ) {}

    public static function fromNormalizedValues(
        string $minimumHeight,
        string $horizontalPosition,
        string $verticalPosition,
        string $fallbackColor,
        bool $decorative,
        string $altText,
    ): self {
        return new self(
            match ($minimumHeight) {
                '240px' => 'mgd-ai-background-image--height-240',
                '480px' => 'mgd-ai-background-image--height-480',
                '640px' => 'mgd-ai-background-image--height-640',
                default => 'mgd-ai-background-image--height-320',
            },
            match ($horizontalPosition) {
                'left' => 'mgd-ai-background-image--horizontal-left',
                'right' => 'mgd-ai-background-image--horizontal-right',
                default => 'mgd-ai-background-image--horizontal-center',
            },
            match ($verticalPosition) {
                'top' => 'mgd-ai-background-image--vertical-top',
                'bottom' => 'mgd-ai-background-image--vertical-bottom',
                default => 'mgd-ai-background-image--vertical-center',
            },
            match ($fallbackColor) {
                'neutral-dark' => 'mgd-ai-background-image--fallback-neutral-dark',
                'brand' => 'mgd-ai-background-image--fallback-brand',
                default => 'mgd-ai-background-image--fallback-neutral-light',
            },
            $decorative,
            $decorative ? '' : $altText,
        );
    }
}
