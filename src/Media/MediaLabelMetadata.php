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
        return in_array($status, self::ALLOWED_STATUSES, true);
    }

    /**
     * Prüft, ob eine Position bytegenau für eine KI-Kennzeichnung erlaubt ist.
     */
    public static function isAllowedPosition(string $position): bool
    {
        return in_array($position, self::ALLOWED_POSITIONS, true);
    }

    /**
     * Prüft, ob ein Theme bytegenau für eine KI-Kennzeichnung erlaubt ist.
     */
    public static function isAllowedTheme(string $theme): bool
    {
        return in_array($theme, self::ALLOWED_THEMES, true);
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
