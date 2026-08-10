<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Storefront\Thumbnail;

/**
 * Sichere PHP-Grenze für die kontextbezogene Darstellung von Shopware-Thumbnails.
 */
final class ThumbnailPresentationResolver
{
    private const MAX_INPUT_DEPTH = 8;

    private const GALLERY_NAVIGATION_NAME = 'gallery-slider-thumbnails-image-thumbnails';

    private const GALLERY_NAVIGATION_CLASS = 'gallery-slider-thumbnails-image';

    /**
     * Diese beiden offiziellen Aufrufer besitzen in 6.6 und 6.7 keine eigene
     * Bildklasse. Ihr Platzhalter ist jedoch nachweislich absolut und 100 % groß.
     *
     * @var list<string>
     */
    private const CLASSLESS_FILL_NAMES = [
        'cms-element-vimeo-video__placeholder',
        'cms-element-youtube-video__placeholder',
    ];

    /**
     * Stabile Name-Klassen-Paare mit einem nachgewiesenen, slotfüllenden Vertrag.
     *
     * @var array<string, string>
     */
    private const FILL_CALLERS = [
        'cms-block-background' => 'cms-block-background',
        'cms-image-slider-thumbnails' => 'image-slider-image',
        'configurator-option-img-thumbnails' => 'product-detail-configurator-option-image',
        'line-item-img-thumbnails' => 'line-item-img',
        'mgd-ai-background-image-thumbnails' => 'mgd-ai-background-image__media',
        'product-image-thumbnails' => 'product-image',
    ];

    /**
     * Diese Logo-Bilder floaten in Shopware 6.6 und 6.7 rechts. Nur das exakte
     * Name-Klassen-Paar darf den entsprechenden Rahmenmodus aktivieren.
     *
     * @var array<string, string>
     */
    private const FLOAT_END_CALLERS = [
        'payment-method-image-thumbnails' => 'payment-method-image',
        'shipping-method-image-thumbnails' => 'shipping-method-image',
    ];

    /**
     * Diese Aufrufer sind nur in ausdrücklich freigegebenen Modi slotfüllend.
     *
     * @var array<string, array{class: string, modes: list<string>}>
     */
    private const MODE_AWARE_CALLERS = [
        'cms-image-thumbnails' => [
            'class' => 'cms-image',
            'modes' => ['cover', 'stretch'],
        ],
        'gallery-slider-image-thumbnails' => [
            'class' => 'gallery-slider-image',
            'modes' => ['cover', 'contain'],
        ],
    ];

    /**
     * Vollständiges Inventar der bewusst intrinsischen offiziellen Aufrufer.
     * Der anschließende Fallback ist absichtlich ebenfalls intrinsisch.
     *
     * @var list<string>
     */
    private const KNOWN_INTRINSIC_NAMES = [
        'footer-payment-image-thumbnails',
        'footer-shipping-image-thumbnails',
        'minimal-image-thumbnails',
        'navigation-flyout-teaser-image-thumbnails',
        'product-detail-manufacturer-image-thumbnails',
        'quickview-minimal-product-manufacturer-logo',
        'search-suggest-product-image-thumbnails',
    ];

    /** @var list<string> */
    private const MODE_KEYS = [
        'displayMode',
        'configDisplayMode',
        'elementDisplayMode',
    ];

    /** @var list<string> */
    private const ALLOWED_MODES = ['standard', 'stretch', 'cover', 'contain'];

    /**
     * Entscheidet ausschließlich aus geschlossenen offiziellen Signaturen.
     * Freie Theme-Werte werden niemals direkt zu Klassen oder Boolean-Optionen.
     */
    public function resolve(mixed $name, mixed $attributes = null, mixed $context = null): ThumbnailPresentation
    {
        $safeName = is_string($name) ? $name : '';
        $classTokens = $this->classTokens($attributes);

        if ($safeName === self::GALLERY_NAVIGATION_NAME || isset($classTokens[self::GALLERY_NAVIGATION_CLASS])) {
            return ThumbnailPresentation::excluded();
        }

        if (in_array($safeName, self::CLASSLESS_FILL_NAMES, true)) {
            return ThumbnailPresentation::fill();
        }

        $requiredClass = self::FILL_CALLERS[$safeName] ?? null;
        if ($requiredClass !== null && isset($classTokens[$requiredClass])) {
            return ThumbnailPresentation::fill();
        }

        $floatEndClass = self::FLOAT_END_CALLERS[$safeName] ?? null;
        if ($floatEndClass !== null && isset($classTokens[$floatEndClass])) {
            return ThumbnailPresentation::intrinsicFloatEnd();
        }

        $modeAwareCaller = self::MODE_AWARE_CALLERS[$safeName] ?? null;
        if ($modeAwareCaller !== null
            && isset($classTokens[$modeAwareCaller['class']])
            && in_array($this->displayMode($context), $modeAwareCaller['modes'], true)
        ) {
            return ThumbnailPresentation::fill();
        }

        if (in_array($safeName, self::KNOWN_INTRINSIC_NAMES, true)) {
            return ThumbnailPresentation::intrinsic();
        }

        return ThumbnailPresentation::intrinsic();
    }

    /**
     * @return array<string, true>
     */
    private function classTokens(mixed $attributes): array
    {
        $classStrings = [];

        if (is_string($attributes)) {
            $classStrings[] = $attributes;
        } elseif (is_array($attributes)) {
            if ($this->containsClassKey($attributes, 0)) {
                $this->collectNamedClassStrings($attributes, $classStrings, 0);
            } elseif (array_is_list($attributes)) {
                $this->collectStrings($attributes, $classStrings, 0);
            }
        }

        $tokens = [];
        foreach ($classStrings as $classString) {
            $parts = preg_split('/[\t\n\f\r ]+/', $classString, -1, PREG_SPLIT_NO_EMPTY);
            if (!is_array($parts)) {
                continue;
            }

            foreach ($parts as $part) {
                $tokens[$part] = true;
            }
        }

        return $tokens;
    }

    /**
     * @param array<mixed> $values
     */
    private function containsClassKey(array $values, int $depth): bool
    {
        if ($depth > self::MAX_INPUT_DEPTH) {
            return false;
        }

        foreach ($values as $key => $value) {
            if ($key === 'class') {
                return true;
            }

            if (is_array($value) && $this->containsClassKey($value, $depth + 1)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<mixed> $values
     * @param list<string> $strings
     */
    private function collectNamedClassStrings(array $values, array &$strings, int $depth): void
    {
        if ($depth > self::MAX_INPUT_DEPTH) {
            return;
        }

        foreach ($values as $key => $value) {
            if ($key === 'class') {
                $this->collectValueStrings($value, $strings, $depth + 1);
                continue;
            }

            if (is_array($value)) {
                $this->collectNamedClassStrings($value, $strings, $depth + 1);
            }
        }
    }

    /**
     * @param array<mixed> $values
     * @param list<string> $strings
     */
    private function collectStrings(array $values, array &$strings, int $depth): void
    {
        if ($depth > self::MAX_INPUT_DEPTH) {
            return;
        }

        foreach ($values as $value) {
            $this->collectValueStrings($value, $strings, $depth + 1);
        }
    }

    /** @param list<string> $strings */
    private function collectValueStrings(mixed $value, array &$strings, int $depth): void
    {
        if ($depth > self::MAX_INPUT_DEPTH) {
            return;
        }

        if (is_string($value)) {
            $strings[] = $value;

            return;
        }

        if (is_array($value)) {
            $this->collectStrings($value, $strings, $depth);
        }
    }

    private function displayMode(mixed $context, int $depth = 0): ?string
    {
        if ($depth > self::MAX_INPUT_DEPTH) {
            return null;
        }

        if (is_string($context)) {
            return in_array($context, self::ALLOWED_MODES, true) ? $context : null;
        }

        if (!is_array($context)) {
            return null;
        }

        foreach ($context as $key => $value) {
            if (is_string($key) && in_array($key, self::MODE_KEYS, true) && is_string($value)) {
                return in_array($value, self::ALLOWED_MODES, true) ? $value : null;
            }

            if (is_array($value)) {
                $nestedMode = $this->displayMode($value, $depth + 1);
                if ($nestedMode !== null) {
                    return $nestedMode;
                }
            }
        }

        return null;
    }
}
