<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Configuration;

/**
 * Überführt unzuverlässige gespeicherte Einstellungen in sichere Anzeigewerte.
 *
 * Jedes Feld wird unabhängig geprüft. Dadurch kann ein fehlerhafter oder
 * manipulierter Wert nie eine valide Einstellung eines anderen Feldes löschen.
 */
final class DisplayConfigurationNormalizer
{
    /**
     * Liest ausschließlich die neun vorgesehenen Einstellungsschlüssel.
     *
     * @param array<mixed> $values Nicht vertrauenswürdige Konfigurationswerte.
     */
    public function normalize(array $values): DisplayConfiguration
    {
        return new DisplayConfiguration(
            fontSize: $this->normalizeFontSize($values['fontSize'] ?? null),
            offset: $this->normalizeOffset($values['offset'] ?? null),
            paddingY: $this->normalizePaddingY($values['paddingY'] ?? null),
            paddingX: $this->normalizePaddingX($values['paddingX'] ?? null),
            radius: $this->normalizeRadius($values['radius'] ?? null),
            blur: $this->normalizeBlur($values['blur'] ?? null),
            position: $this->normalizePosition($values['position'] ?? null),
            theme: $this->normalizeTheme($values['theme'] ?? null),
            language: $this->normalizeLanguage($values['language'] ?? null),
        );
    }

    /** @param mixed $value Nicht vertrauenswürdige Schriftgröße. */
    private function normalizeFontSize(mixed $value): int
    {
        if (!is_int($value) || !DisplayConfiguration::isValidFontSize($value)) {
            return DisplayConfiguration::DEFAULT_FONT_SIZE;
        }

        return $value;
    }

    /** @param mixed $value Nicht vertrauenswürdiger Abstand. */
    private function normalizeOffset(mixed $value): int
    {
        if (!is_int($value) || !DisplayConfiguration::isValidOffset($value)) {
            return DisplayConfiguration::DEFAULT_OFFSET;
        }

        return $value;
    }

    /** @param mixed $value Nicht vertrauenswürdiger vertikaler Innenabstand. */
    private function normalizePaddingY(mixed $value): int
    {
        if (!is_int($value) || !DisplayConfiguration::isValidPaddingY($value)) {
            return DisplayConfiguration::DEFAULT_PADDING_Y;
        }

        return $value;
    }

    /** @param mixed $value Nicht vertrauenswürdiger horizontaler Innenabstand. */
    private function normalizePaddingX(mixed $value): int
    {
        if (!is_int($value) || !DisplayConfiguration::isValidPaddingX($value)) {
            return DisplayConfiguration::DEFAULT_PADDING_X;
        }

        return $value;
    }

    /** @param mixed $value Nicht vertrauenswürdiger Eckenradius. */
    private function normalizeRadius(mixed $value): int
    {
        if (!is_int($value) || !DisplayConfiguration::isValidRadius($value)) {
            return DisplayConfiguration::DEFAULT_RADIUS;
        }

        return $value;
    }

    /** @param mixed $value Nicht vertrauenswürdige Hintergrundunschärfe. */
    private function normalizeBlur(mixed $value): int
    {
        if (!is_int($value) || !DisplayConfiguration::isValidBlur($value)) {
            return DisplayConfiguration::DEFAULT_BLUR;
        }

        return $value;
    }

    /** @param mixed $value Nicht vertrauenswürdige Positionsangabe. */
    private function normalizePosition(mixed $value): string
    {
        if (!is_string($value) || !DisplayConfiguration::isAllowedPosition($value)) {
            return DisplayConfiguration::DEFAULT_POSITION;
        }

        return $value;
    }

    /** @param mixed $value Nicht vertrauenswürdige Theme-Angabe. */
    private function normalizeTheme(mixed $value): string
    {
        if (!is_string($value) || !DisplayConfiguration::isAllowedTheme($value)) {
            return DisplayConfiguration::DEFAULT_THEME;
        }

        return $value;
    }

    /** @param mixed $value Nicht vertrauenswürdige Sprachangabe. */
    private function normalizeLanguage(mixed $value): string
    {
        if (!is_string($value) || !DisplayConfiguration::isAllowedLanguage($value)) {
            return DisplayConfiguration::DEFAULT_LANGUAGE;
        }

        return $value;
    }
}
