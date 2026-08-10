<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Configuration;

/**
 * Einzige Positivliste aller Konfigurationsschlüssel des Plugins.
 *
 * Sicherung, Wiederherstellung und Storefront greifen damit auf denselben
 * geschlossenen Vertrag zu. Präfix- oder Domain-Abfragen sind bewusst verboten.
 */
final class ConfigurationKeys
{
    public const FONT_SIZE = 'MGDAIImageLabels.config.fontSize';
    public const OFFSET = 'MGDAIImageLabels.config.offset';
    public const PADDING_Y = 'MGDAIImageLabels.config.paddingY';
    public const PADDING_X = 'MGDAIImageLabels.config.paddingX';
    public const RADIUS = 'MGDAIImageLabels.config.radius';
    public const BLUR = 'MGDAIImageLabels.config.blur';
    public const POSITION = 'MGDAIImageLabels.config.position';
    public const THEME = 'MGDAIImageLabels.config.theme';
    public const LANGUAGE = 'MGDAIImageLabels.config.language';

    /** @return list<string> */
    public static function all(): array
    {
        return [
            self::FONT_SIZE,
            self::OFFSET,
            self::PADDING_Y,
            self::PADDING_X,
            self::RADIUS,
            self::BLUR,
            self::POSITION,
            self::THEME,
            self::LANGUAGE,
        ];
    }

    public static function isOwned(string $key): bool
    {
        return in_array($key, self::all(), true);
    }

    /** Liefert den von der XML-Konfiguration vorgegebenen PHP-Skalartyp. */
    public static function expectedType(string $key): ?string
    {
        if (!self::isOwned($key)) {
            return null;
        }

        return in_array($key, [self::POSITION, self::THEME, self::LANGUAGE], true)
            ? 'string'
            : 'integer';
    }
}
