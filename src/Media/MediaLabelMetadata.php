<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Media;

/**
 * Enthält die geprüften Metadaten für eine einzelne KI-Bildkennzeichnung.
 *
 * Diese wertartige Klasse kennt keine Shopware-Details. Sie transportiert nur
 * bereits validierte Werte vom Einlesen der Media-Custom-Fields zur späteren
 * Darstellung im Storefront.
 */
final readonly class MediaLabelMetadata
{
    /**
     * @param string $status Der geprüfte Kennzeichnungsstatus.
     * @param ?string $position Die optionale, geprüfte Position des Labels.
     * @param ?string $theme Das optionale, geprüfte visuelle Theme des Labels.
     */
    public function __construct(
        public string $status,
        public ?string $position,
        public ?string $theme,
    ) {
    }

    /**
     * Legt fest, ob für das Medium überhaupt eine Kennzeichnung erscheint.
     *
     * Der Status „none“ ist der einzige explizite Zustand ohne sichtbares
     * Label. Alle anderen Werte wurden zuvor durch die Positivliste geprüft.
     */
    public function isVisible(): bool
    {
        return $this->status !== 'none';
    }
}
