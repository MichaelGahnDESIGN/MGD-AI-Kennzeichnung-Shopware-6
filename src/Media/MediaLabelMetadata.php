<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Media;

use MGDAIImageLabels\Domain\LabelPosition;
use MGDAIImageLabels\Domain\LabelStatus;
use MGDAIImageLabels\Domain\LabelTheme;

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
        if (!self::isAllowedStatus($status)) {
            throw new \InvalidArgumentException('Ungültiger KI-Kennzeichnungsstatus.');
        }

        if ($position !== null && !self::isAllowedPosition($position)) {
            throw new \InvalidArgumentException('Ungültige Position für die KI-Kennzeichnung.');
        }

        if ($theme !== null && !self::isAllowedTheme($theme)) {
            throw new \InvalidArgumentException('Ungültiges Theme für die KI-Kennzeichnung.');
        }
    }

    /**
     * Prüft, ob ein Status bytegenau für eine KI-Kennzeichnung erlaubt ist.
     */
    public static function isAllowedStatus(string $status): bool
    {
        return LabelStatus::tryFrom($status) !== null;
    }

    /**
     * Prüft, ob eine Position bytegenau für eine KI-Kennzeichnung erlaubt ist.
     */
    public static function isAllowedPosition(string $position): bool
    {
        return LabelPosition::tryFrom($position) !== null;
    }

    /**
     * Prüft, ob ein Theme bytegenau für eine KI-Kennzeichnung erlaubt ist.
     */
    public static function isAllowedTheme(string $theme): bool
    {
        return LabelTheme::tryFrom($theme) !== null;
    }

    /**
     * Legt fest, ob für das Medium überhaupt eine Kennzeichnung erscheint.
     *
     * Der Enum-Fall „none“ ist der einzige explizite Zustand ohne sichtbares
     * Label. Alle anderen Werte wurden zuvor durch die Domain geprüft.
     */
    public function isVisible(): bool
    {
        return $this->status !== LabelStatus::None->value;
    }
}
