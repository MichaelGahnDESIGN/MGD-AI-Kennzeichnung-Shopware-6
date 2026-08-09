<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Media;

use MGDAIImageLabels\Media\MediaLabelMetadataNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Prüft, dass Metadaten einer KI-Bildkennzeichnung ausschließlich aus den
 * vorgesehenen, gespeicherten Werten übernommen werden.
 */
final class MediaLabelMetadataNormalizerTest extends TestCase
{
    /**
     * Gültige, bytegenaue Metadaten dürfen unverändert an die Darstellung
     * weitergegeben werden.
     */
    public function testNormalizeKeepsValidValuesExactly(): void
    {
        $result = (new MediaLabelMetadataNormalizer())->normalize([
            'mgd_ai_status' => 'deepfake',
            'mgd_ai_position' => 'top-left',
            'mgd_ai_theme' => 'light',
        ]);

        self::assertSame('deepfake', $result->status);
        self::assertSame('top-left', $result->position);
        self::assertSame('light', $result->theme);
        self::assertTrue($result->isVisible());
    }

    /**
     * Jeder erlaubte Status muss durch den Normalizer unverändert erhalten
     * bleiben; nur „none“ darf kein sichtbares Label erzeugen.
     */
    #[DataProvider('allowedStatusProvider')]
    public function testNormalizeKeepsEveryAllowedStatus(string $status, bool $isVisible): void
    {
        $result = (new MediaLabelMetadataNormalizer())->normalize([
            'mgd_ai_status' => $status,
        ]);

        self::assertSame($status, $result->status);
        self::assertSame($isVisible, $result->isVisible());
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function allowedStatusProvider(): iterable
    {
        yield 'keine Kennzeichnung' => ['none', false];
        yield 'vollständig generiert' => ['generated', true];
        yield 'teilweise generiert' => ['partially-generated', true];
        yield 'verändert' => ['modified', true];
        yield 'Deepfake' => ['deepfake', true];
    }

    /**
     * Jede erlaubte Position muss durch den Normalizer unverändert erhalten
     * bleiben, wenn ein sichtbarer Kennzeichnungsstatus vorliegt.
     */
    #[DataProvider('allowedPositionProvider')]
    public function testNormalizeKeepsEveryAllowedPosition(string $position): void
    {
        $result = (new MediaLabelMetadataNormalizer())->normalize([
            'mgd_ai_status' => 'generated',
            'mgd_ai_position' => $position,
        ]);

        self::assertSame($position, $result->position);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function allowedPositionProvider(): iterable
    {
        yield 'oben links' => ['top-left'];
        yield 'oben rechts' => ['top-right'];
        yield 'unten links' => ['bottom-left'];
        yield 'unten rechts' => ['bottom-right'];
    }

    /**
     * Jedes erlaubte Theme muss durch den Normalizer unverändert erhalten
     * bleiben, wenn ein sichtbarer Kennzeichnungsstatus vorliegt.
     */
    #[DataProvider('allowedThemeProvider')]
    public function testNormalizeKeepsEveryAllowedTheme(string $theme): void
    {
        $result = (new MediaLabelMetadataNormalizer())->normalize([
            'mgd_ai_status' => 'generated',
            'mgd_ai_theme' => $theme,
        ]);

        self::assertSame($theme, $result->theme);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function allowedThemeProvider(): iterable
    {
        yield 'automatisch' => ['auto'];
        yield 'hell' => ['light'];
        yield 'dunkel' => ['dark'];
    }

    /**
     * Manipulierte Inhalte dürfen nicht in die Metadaten oder Darstellung
     * gelangen, sondern fallen auf die sicheren Standardwerte zurück.
     */
    public function testNormalizeRejectsManipulatedValues(): void
    {
        $result = (new MediaLabelMetadataNormalizer())->normalize([
            'mgd_ai_status' => '<script>',
            'mgd_ai_position' => 'center',
            'mgd_ai_theme' => 'url(https://example.invalid)',
        ]);

        self::assertSame('none', $result->status);
        self::assertNull($result->position);
        self::assertNull($result->theme);
        self::assertFalse($result->isVisible());
    }

    /**
     * Fehlende Felder bleiben ungesetzt; ohne Status soll keine Kennzeichnung
     * angezeigt werden.
     */
    public function testNormalizeUsesSecureDefaultsForMissingValues(): void
    {
        $result = (new MediaLabelMetadataNormalizer())->normalize([]);

        self::assertSame('none', $result->status);
        self::assertNull($result->position);
        self::assertNull($result->theme);
        self::assertFalse($result->isVisible());
    }

    /**
     * Unerwartete Datentypen aus Custom Fields werden ohne Ausnahme verworfen.
     */
    public function testNormalizeRejectsNonStringValues(): void
    {
        $result = (new MediaLabelMetadataNormalizer())->normalize([
            'mgd_ai_status' => ['generated'],
            'mgd_ai_position' => 42,
            'mgd_ai_theme' => new \stdClass(),
        ]);

        self::assertSame('none', $result->status);
        self::assertNull($result->position);
        self::assertNull($result->theme);
        self::assertFalse($result->isVisible());
    }

    /**
     * Die Positivlisten sind absichtlich bytegenau: Schreibweise und Leerraum
     * werden nicht stillschweigend korrigiert.
     */
    public function testNormalizeRejectsChangedCaseAndWhitespace(): void
    {
        $result = (new MediaLabelMetadataNormalizer())->normalize([
            'mgd_ai_status' => ' Generated ',
            'mgd_ai_position' => 'TOP-LEFT',
            'mgd_ai_theme' => ' dark ',
        ]);

        self::assertSame('none', $result->status);
        self::assertNull($result->position);
        self::assertNull($result->theme);
        self::assertFalse($result->isVisible());
    }
}
