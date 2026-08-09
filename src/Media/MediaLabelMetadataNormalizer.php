<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Media;

use MGDAIImageLabels\Domain\LabelPosition;
use MGDAIImageLabels\Domain\LabelStatus;
use MGDAIImageLabels\Domain\LabelTheme;

/**
 * Überführt Media-Custom-Fields in sichere, streng geprüfte Label-Metadaten.
 *
 * Es werden ausschließlich bekannte Schlüssel und bytegenau erlaubte Werte
 * akzeptiert. So können unzuverlässige oder manipulierte Custom-Field-Inhalte
 * weder als freie Darstellungsvorgabe noch als Kennzeichnungsstatus dienen.
 */
final class MediaLabelMetadataNormalizer
{
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
            $this->normalizePosition($customFields['mgd_ai_position'] ?? null),
            $this->normalizeTheme($customFields['mgd_ai_theme'] ?? null),
        );
    }

    /**
     * Prüft einen möglichen Status gegen die geschlossene Positivliste.
     *
     * @param mixed $value Der ungeprüfte gespeicherte Feldwert.
     */
    private function normalizeStatus(mixed $value): string
    {
        if (!is_string($value) || LabelStatus::tryFrom($value) === null) {
            return 'none';
        }

        return $value;
    }

    /**
     * Prüft eine optionale Positionsangabe gegen die Domain-Positivliste.
     *
     * @param mixed $value Der ungeprüfte gespeicherte Feldwert.
     */
    private function normalizePosition(mixed $value): ?string
    {
        if (!is_string($value) || LabelPosition::tryFrom($value) === null) {
            return null;
        }

        return $value;
    }

    /**
     * Prüft eine optionale Theme-Angabe gegen die Domain-Positivliste.
     *
     * @param mixed $value Der ungeprüfte gespeicherte Feldwert.
     */
    private function normalizeTheme(mixed $value): ?string
    {
        if (!is_string($value) || LabelTheme::tryFrom($value) === null) {
            return null;
        }

        return $value;
    }
}
