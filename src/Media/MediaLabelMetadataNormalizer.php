<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Media;

/**
 * Überführt Media-Custom-Fields in sichere, streng geprüfte Label-Metadaten.
 *
 * Es werden ausschließlich bekannte Schlüssel und bytegenau erlaubte Werte
 * akzeptiert. So können unzuverlässige oder manipulierte Custom-Field-Inhalte
 * weder als freie Darstellungsvorgabe noch als Kennzeichnungsstatus dienen.
 */
final class MediaLabelMetadataNormalizer
{
    /** @var list<string> Die ausschließlich erlaubten Kennzeichnungsstatus. */
    private const ALLOWED_STATUSES = [
        'none',
        'generated',
        'partially-generated',
        'modified',
        'deepfake',
    ];

    /** @var list<string> Die ausschließlich erlaubten Positionen. */
    private const ALLOWED_POSITIONS = [
        'top-left',
        'top-right',
        'bottom-left',
        'bottom-right',
    ];

    /** @var list<string> Die ausschließlich erlaubten Themes. */
    private const ALLOWED_THEMES = [
        'auto',
        'light',
        'dark',
    ];

    /**
     * Liest die drei vorgesehenen KI-Custom-Fields und gibt ausschließlich
     * sichere Werte zurück.
     *
     * Fehlende oder ungültige Statuswerte werden als „none“ behandelt. Für
     * Position und Theme bedeutet ein ungültiger Wert „nicht gesetzt“, damit
     * spätere globale Standards greifen können.
     *
     * @param array<mixed> $customFields Die nicht vertrauenswürdigen Custom Fields des Mediums.
     */
    public function normalize(array $customFields): MediaLabelMetadata
    {
        return new MediaLabelMetadata(
            $this->normalizeStatus($customFields['mgd_ai_status'] ?? null),
            $this->normalizeOptionalValue($customFields['mgd_ai_position'] ?? null, self::ALLOWED_POSITIONS),
            $this->normalizeOptionalValue($customFields['mgd_ai_theme'] ?? null, self::ALLOWED_THEMES),
        );
    }

    /**
     * Prüft einen möglichen Status gegen die geschlossene Positivliste.
     *
     * @param mixed $value Der ungeprüfte gespeicherte Feldwert.
     */
    private function normalizeStatus(mixed $value): string
    {
        if (!is_string($value) || !in_array($value, self::ALLOWED_STATUSES, true)) {
            return 'none';
        }

        return $value;
    }

    /**
     * Prüft eine optionale Darstellungsangabe gegen ihre Positivliste.
     *
     * @param mixed $value Der ungeprüfte gespeicherte Feldwert.
     * @param list<string> $allowedValues Die bytegenau erlaubten Werte.
     */
    private function normalizeOptionalValue(mixed $value, array $allowedValues): ?string
    {
        if (!is_string($value) || !in_array($value, $allowedValues, true)) {
            return null;
        }

        return $value;
    }
}
