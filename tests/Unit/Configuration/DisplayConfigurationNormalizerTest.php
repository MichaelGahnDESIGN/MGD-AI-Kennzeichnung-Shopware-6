<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Configuration;

use MGDAIImageLabels\Configuration\DisplayConfiguration;
use MGDAIImageLabels\Configuration\DisplayConfigurationNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Prüft die feldweise Sicherheitsnormalisierung der Anzeigeeinstellungen.
 */
final class DisplayConfigurationNormalizerTest extends TestCase
{
    /**
     * Fehlende Werte müssen die sicheren, fachlich festgelegten Standards
     * ergeben, ohne vom Inhalt eines Konfigurationsspeichers abzuhängen.
     */
    public function testNormalizeUsesSecureDefaultsForMissingValues(): void
    {
        $configuration = (new DisplayConfigurationNormalizer())->normalize([]);

        self::assertSame(6, $configuration->fontSize);
        self::assertSame(12, $configuration->offset);
        self::assertSame(5, $configuration->paddingY);
        self::assertSame(9, $configuration->paddingX);
        self::assertSame(999, $configuration->radius);
        self::assertSame(10, $configuration->blur);
        self::assertSame('bottom-right', $configuration->position);
        self::assertSame('auto', $configuration->theme);
        self::assertSame('auto', $configuration->language);
    }

    /**
     * Alle unteren Grenzwerte und erlaubten Auswahlwerte müssen unverändert
     * in die sichere Konfiguration übernommen werden.
     */
    public function testNormalizeKeepsValidLowerBoundsAndSelectionValues(): void
    {
        $configuration = (new DisplayConfigurationNormalizer())->normalize([
            'fontSize' => 6,
            'offset' => 0,
            'paddingY' => 2,
            'paddingX' => 4,
            'radius' => 0,
            'blur' => 0,
            'position' => 'top-left',
            'theme' => 'light',
            'language' => 'de',
        ]);

        self::assertSame(6, $configuration->fontSize);
        self::assertSame(0, $configuration->offset);
        self::assertSame(2, $configuration->paddingY);
        self::assertSame(4, $configuration->paddingX);
        self::assertSame(0, $configuration->radius);
        self::assertSame(0, $configuration->blur);
        self::assertSame('top-left', $configuration->position);
        self::assertSame('light', $configuration->theme);
        self::assertSame('de', $configuration->language);
    }

    /**
     * Alle oberen Grenzwerte und weiteren Auswahlwerte sind ebenfalls gültig.
     */
    public function testNormalizeKeepsValidUpperBoundsAndSelectionValues(): void
    {
        $configuration = (new DisplayConfigurationNormalizer())->normalize([
            'fontSize' => 24,
            'offset' => 96,
            'paddingY' => 24,
            'paddingX' => 40,
            'radius' => 999,
            'blur' => 24,
            'position' => 'bottom-left',
            'theme' => 'dark',
            'language' => 'en',
        ]);

        self::assertSame(24, $configuration->fontSize);
        self::assertSame(96, $configuration->offset);
        self::assertSame(24, $configuration->paddingY);
        self::assertSame(40, $configuration->paddingX);
        self::assertSame(999, $configuration->radius);
        self::assertSame(24, $configuration->blur);
        self::assertSame('bottom-left', $configuration->position);
        self::assertSame('dark', $configuration->theme);
        self::assertSame('en', $configuration->language);
    }

    /**
     * Ungültige Felder fallen unabhängig voneinander zurück; sichere
     * Nachbarfelder bleiben dabei unverändert erhalten.
     */
    public function testNormalizeFallsBackPerInvalidFieldOnly(): void
    {
        $configuration = (new DisplayConfigurationNormalizer())->normalize([
            'fontSize' => '6px;background:red',
            'offset' => 97,
            'paddingY' => -1,
            'paddingX' => 20,
            'radius' => 12,
            'blur' => 8,
            'position' => 'center',
            'theme' => 'dark',
            'language' => 'fr-FR',
        ]);

        self::assertSame(6, $configuration->fontSize);
        self::assertSame(12, $configuration->offset);
        self::assertSame(5, $configuration->paddingY);
        self::assertSame(20, $configuration->paddingX);
        self::assertSame(12, $configuration->radius);
        self::assertSame(8, $configuration->blur);
        self::assertSame('bottom-right', $configuration->position);
        self::assertSame('dark', $configuration->theme);
        self::assertSame('auto', $configuration->language);
    }

    /**
     * Nur echte PHP-Integer dürfen Zahlenfelder erreichen. Typumwandlungen
     * würden sonst manipulierte Konfigurationsdaten stillschweigend zulassen.
     *
     * @param mixed $invalidValue
     */
    #[DataProvider('invalidIntegerValueProvider')]
    public function testNormalizeRejectsEveryNonIntegerValue(mixed $invalidValue): void
    {
        $configuration = (new DisplayConfigurationNormalizer())->normalize([
            'fontSize' => $invalidValue,
        ]);

        self::assertSame(6, $configuration->fontSize);
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function invalidIntegerValueProvider(): iterable
    {
        yield 'numerischer String' => ['6'];
        yield 'Float' => [6.0];
        yield 'Boolean true' => [true];
        yield 'Boolean false' => [false];
        yield 'Array' => [[6]];
        yield 'Objekt' => [new \stdClass()];
    }

    /**
     * Die Positivlisten dürfen keine Varianten, Leerzeichen oder freie Werte
     * akzeptieren. Jede erlaubte Alternative wird zugleich explizit geprüft.
     *
     * @param array<string, string> $values
     */
    #[DataProvider('selectionValueProvider')]
    public function testNormalizeKeepsEveryAllowedSelectionValue(array $values): void
    {
        $configuration = (new DisplayConfigurationNormalizer())->normalize($values);

        self::assertSame($values['position'], $configuration->position);
        self::assertSame($values['theme'], $configuration->theme);
        self::assertSame($values['language'], $configuration->language);
    }

    /**
     * @return iterable<string, array{array{position: string, theme: string, language: string}}>
     */
    public static function selectionValueProvider(): iterable
    {
        yield 'oben links, automatisch, automatisch' => [['position' => 'top-left', 'theme' => 'auto', 'language' => 'auto']];
        yield 'oben rechts, hell, deutsch' => [['position' => 'top-right', 'theme' => 'light', 'language' => 'de']];
        yield 'unten links, dunkel, englisch' => [['position' => 'bottom-left', 'theme' => 'dark', 'language' => 'en']];
        yield 'unten rechts, automatisch, deutsch' => [['position' => 'bottom-right', 'theme' => 'auto', 'language' => 'de']];
    }

    /**
     * Jede direkte Konstruktion muss die neun Invarianten selbst schützen.
     * So kann keine Umgehung des Normalizers unsichere Werte erzeugen.
     *
     * @param int|string $invalidValue
     */
    #[DataProvider('invalidConstructorValueProvider')]
    public function testConfigurationRejectsEveryUnsafeDirectConstruction(string $field, int|string $invalidValue): void
    {
        $values = self::validConstructorValues();
        $values[$field] = $invalidValue;

        $this->expectException(\InvalidArgumentException::class);

        new DisplayConfiguration(...$values);
    }

    /**
     * @return iterable<string, array{string, int|string}>
     */
    public static function invalidConstructorValueProvider(): iterable
    {
        yield 'Schriftgröße' => ['fontSize', 25];
        yield 'Abstand' => ['offset', 97];
        yield 'vertikaler Innenabstand' => ['paddingY', 1];
        yield 'horizontaler Innenabstand' => ['paddingX', 3];
        yield 'Radius' => ['radius', 1000];
        yield 'Unschärfe' => ['blur', 25];
        yield 'Position' => ['position', 'center'];
        yield 'Theme' => ['theme', 'contrast'];
        yield 'Sprache' => ['language', 'fr'];
    }

    /**
     * @return array{fontSize: int, offset: int, paddingY: int, paddingX: int, radius: int, blur: int, position: string, theme: string, language: string}
     */
    private static function validConstructorValues(): array
    {
        return [
            'fontSize' => 6,
            'offset' => 12,
            'paddingY' => 5,
            'paddingX' => 9,
            'radius' => 999,
            'blur' => 10,
            'position' => 'bottom-right',
            'theme' => 'auto',
            'language' => 'auto',
        ];
    }
}
