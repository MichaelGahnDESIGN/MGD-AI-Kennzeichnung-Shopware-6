<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Cms\Philosophy;

/**
 * Zentrale serverseitige Quelle für sichere, zweisprachige Standardinhalte.
 *
 * Nur eine ausdrücklich deutsche Locale wählt Deutsch. Fehlende, unbekannte
 * oder manipulierte Locale-Werte fallen neutral auf Englisch zurück.
 */
final readonly class PhilosophyDefaultContent
{
    public static function german(): string
    {
        return '<h2>Unser Umgang mit KI-Bildern</h2><p>Wir kennzeichnen Bilder transparent, wenn sie vollständig oder teilweise mit künstlicher Intelligenz erstellt oder bearbeitet wurden.</p><p>So können Sie Inhalte bewusst einordnen. Die Kennzeichnung verändert weder Produktinformationen noch Ihre Privatsphäre.</p>';
    }

    public static function english(): string
    {
        return '<h2>Our approach to AI imagery</h2><p>We label images transparently when they were created or edited wholly or partly with artificial intelligence.</p><p>This helps you assess content consciously. The label changes neither product information nor your privacy.</p>';
    }

    public function forLocale(mixed $locale): string
    {
        return is_string($locale) && preg_match('/^de(?:[-_]|$)/i', trim($locale)) === 1
            ? self::german()
            : self::english();
    }

    public function resolve(mixed $content, mixed $locale): string
    {
        return is_string($content) && trim($content) !== ''
            ? $content
            : $this->forLocale($locale);
    }
}
