<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Storefront;

use MGDAIImageLabels\Storefront\Label\LabelLanguageResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Prüft die sichere und eindeutige Auflösung der Ausgabesprache.
 */
final class LabelLanguageResolverTest extends TestCase
{
    #[DataProvider('languageCases')]
    public function testResolveReturnsOnlyAClosedLanguageValue(
        string $setting,
        string $locale,
        string $expected,
    ): void {
        self::assertSame($expected, (new LabelLanguageResolver())->resolve($setting, $locale));
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function languageCases(): iterable
    {
        yield 'automatisch deutsch Deutschland' => ['auto', 'de-DE', 'de'];
        yield 'automatisch deutsch Schweiz' => ['auto', 'de-CH', 'de'];
        yield 'automatisch deutsch unabhängig von Großschreibung' => ['auto', 'DE-at', 'de'];
        yield 'automatisch englisch' => ['auto', 'en-GB', 'en'];
        yield 'automatisch unbekannte Sprache' => ['auto', 'fr-FR', 'en'];
        yield 'automatisch leere Locale' => ['auto', '', 'en'];
        yield 'deutsche Überschreibung' => ['de', 'en-GB', 'de'];
        yield 'englische Überschreibung' => ['en', 'de-DE', 'en'];
        yield 'manipulierte Einstellung bleibt neutral' => ['<script>', 'de-DE', 'en'];
    }
}
