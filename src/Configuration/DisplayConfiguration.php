<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Configuration;

use MGDAIImageLabels\Domain\LabelLanguage;
use MGDAIImageLabels\Domain\LabelPosition;
use MGDAIImageLabels\Domain\LabelTheme;

/**
 * Beschreibt die vollständig geprüften Anzeigeeinstellungen eines Labels.
 *
 * Diese Klasse ist die zentrale fachliche Wahrheit für Standards und
 * Zahlenräume. Geschlossene Auswahllisten stammen ausschließlich aus den
 * passenden Domain-Enums. Ihr Konstruktor schützt die Invarianten auch bei
 * einer direkten Nutzung außerhalb des Normalizers.
 */
final readonly class DisplayConfiguration
{
    /** Die sichere Standardschriftgröße in Pixeln. */
    public const DEFAULT_FONT_SIZE = 6;

    /** Die sichere Standarddistanz zum Bildrand in Pixeln. */
    public const DEFAULT_OFFSET = 12;

    /** Der sichere vertikale Standardinnenabstand in Pixeln. */
    public const DEFAULT_PADDING_Y = 5;

    /** Der sichere horizontale Standardinnenabstand in Pixeln. */
    public const DEFAULT_PADDING_X = 9;

    /** Der sichere Standardradius für eine Pillenform in Pixeln. */
    public const DEFAULT_RADIUS = 999;

    /** Die sichere Standardunschärfe für den Hintergrund in Pixeln. */
    public const DEFAULT_BLUR = 10;

    /** Die sichere Standardposition für das Label. */
    public const DEFAULT_POSITION = 'bottom-right';

    /** Das sichere Standardtheme, das sich am System orientiert. */
    public const DEFAULT_THEME = 'auto';

    /** Die sichere Standardsprache, die den Shop-Kontext verwendet. */
    public const DEFAULT_LANGUAGE = 'auto';

    /** Untere Grenze der Schriftgröße. */
    public const MIN_FONT_SIZE = 6;

    /** Obere Grenze der Schriftgröße. */
    public const MAX_FONT_SIZE = 24;

    /** Untere Grenze des Abstands. */
    public const MIN_OFFSET = 0;

    /** Obere Grenze des Abstands. */
    public const MAX_OFFSET = 96;

    /** Untere Grenze des vertikalen Innenabstands. */
    public const MIN_PADDING_Y = 2;

    /** Obere Grenze des vertikalen Innenabstands. */
    public const MAX_PADDING_Y = 24;

    /** Untere Grenze des horizontalen Innenabstands. */
    public const MIN_PADDING_X = 4;

    /** Obere Grenze des horizontalen Innenabstands. */
    public const MAX_PADDING_X = 40;

    /** Untere Grenze des Radius. */
    public const MIN_RADIUS = 0;

    /** Obere Grenze des Radius. */
    public const MAX_RADIUS = 999;

    /** Untere Grenze der Hintergrundunschärfe. */
    public const MIN_BLUR = 0;

    /** Obere Grenze der Hintergrundunschärfe. */
    public const MAX_BLUR = 24;

    /**
     * @param int $fontSize Geprüfte Schriftgröße in Pixeln.
     * @param int $offset Geprüfter Abstand zum Bildrand in Pixeln.
     * @param int $paddingY Geprüfter vertikaler Innenabstand in Pixeln.
     * @param int $paddingX Geprüfter horizontaler Innenabstand in Pixeln.
     * @param int $radius Geprüfter Eckenradius in Pixeln.
     * @param int $blur Geprüfte Hintergrundunschärfe in Pixeln.
     * @param string $position Geprüfte Position aus der geschlossenen Liste.
     * @param string $theme Geprüftes Theme aus der geschlossenen Liste.
     * @param string $language Geprüfte Sprache aus der geschlossenen Liste.
     */
    public function __construct(
        public int $fontSize,
        public int $offset,
        public int $paddingY,
        public int $paddingX,
        public int $radius,
        public int $blur,
        public string $position,
        public string $theme,
        public string $language,
    ) {
        if (!self::isValidFontSize($fontSize)) {
            throw new \InvalidArgumentException('Ungültige Schriftgröße für die Kennzeichnung.');
        }

        if (!self::isValidOffset($offset)) {
            throw new \InvalidArgumentException('Ungültiger Abstand für die Kennzeichnung.');
        }

        if (!self::isValidPaddingY($paddingY)) {
            throw new \InvalidArgumentException('Ungültiger vertikaler Innenabstand für die Kennzeichnung.');
        }

        if (!self::isValidPaddingX($paddingX)) {
            throw new \InvalidArgumentException('Ungültiger horizontaler Innenabstand für die Kennzeichnung.');
        }

        if (!self::isValidRadius($radius)) {
            throw new \InvalidArgumentException('Ungültiger Radius für die Kennzeichnung.');
        }

        if (!self::isValidBlur($blur)) {
            throw new \InvalidArgumentException('Ungültige Hintergrundunschärfe für die Kennzeichnung.');
        }

        if (!self::isAllowedPosition($position)) {
            throw new \InvalidArgumentException('Ungültige Position für die Kennzeichnung.');
        }

        if (!self::isAllowedTheme($theme)) {
            throw new \InvalidArgumentException('Ungültiges Theme für die Kennzeichnung.');
        }

        if (!self::isAllowedLanguage($language)) {
            throw new \InvalidArgumentException('Ungültige Sprache für die Kennzeichnung.');
        }
    }

    /** Prüft die Schriftgröße gegen ihren engen, sicheren Zahlenraum. */
    public static function isValidFontSize(int $value): bool
    {
        return $value >= self::MIN_FONT_SIZE && $value <= self::MAX_FONT_SIZE;
    }

    /** Prüft den Abstand gegen seinen engen, sicheren Zahlenraum. */
    public static function isValidOffset(int $value): bool
    {
        return $value >= self::MIN_OFFSET && $value <= self::MAX_OFFSET;
    }

    /** Prüft den vertikalen Innenabstand gegen seinen sicheren Zahlenraum. */
    public static function isValidPaddingY(int $value): bool
    {
        return $value >= self::MIN_PADDING_Y && $value <= self::MAX_PADDING_Y;
    }

    /** Prüft den horizontalen Innenabstand gegen seinen sicheren Zahlenraum. */
    public static function isValidPaddingX(int $value): bool
    {
        return $value >= self::MIN_PADDING_X && $value <= self::MAX_PADDING_X;
    }

    /** Prüft den Radius gegen seinen sicheren Zahlenraum. */
    public static function isValidRadius(int $value): bool
    {
        return $value >= self::MIN_RADIUS && $value <= self::MAX_RADIUS;
    }

    /** Prüft die Hintergrundunschärfe gegen ihren sicheren Zahlenraum. */
    public static function isValidBlur(int $value): bool
    {
        return $value >= self::MIN_BLUR && $value <= self::MAX_BLUR;
    }

    /** Prüft die Position gegen die geschlossene Positivliste. */
    public static function isAllowedPosition(string $value): bool
    {
        return LabelPosition::tryFrom($value) !== null;
    }

    /** Prüft das Theme gegen die geschlossene Positivliste. */
    public static function isAllowedTheme(string $value): bool
    {
        return LabelTheme::tryFrom($value) !== null;
    }

    /** Prüft die Sprache gegen die geschlossene Positivliste. */
    public static function isAllowedLanguage(string $value): bool
    {
        return LabelLanguage::tryFrom($value) !== null;
    }
}
