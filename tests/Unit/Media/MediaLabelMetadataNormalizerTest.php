<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Media;

use MGDAIImageLabels\Media\MediaLabelMetadataNormalizer;
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
