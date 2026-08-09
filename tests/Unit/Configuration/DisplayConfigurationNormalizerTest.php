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
     * Nichttextliche Auswahlwerte dürfen niemals an die Anzeige gelangen.
     * Jede Kombination aus Feld und unerwartetem Typ fällt feldweise auf den
     * passenden sicheren Standard zurück.
     *
     * @param mixed $invalidValue
     */
    #[DataProvider('invalidSelectionValueProvider')]
    public function testNormalizeRejectsNonStringSelectionValues(string $field, mixed $invalidValue, string $expectedValue): void
    {
        $configuration = (new DisplayConfigurationNormalizer())->normalize([
            $field => $invalidValue,
        ]);

        $actualValue = match ($field) {
            'position' => $configuration->position,
            'theme' => $configuration->theme,
            'language' => $configuration->language,
            default => throw new \LogicException('Der Test kennt nur Auswahlfelder der Anzeige-Konfiguration.'),
        };

        self::assertSame($expectedValue, $actualValue);
    }

    /**
     * @return iterable<string, array{string, mixed, string}>
     */
    public static function invalidSelectionValueProvider(): iterable
    {
        yield 'Position als Array' => ['position', ['top-left'], 'bottom-right'];
        yield 'Position als Objekt' => ['position', new \stdClass(), 'bottom-right'];
        yield 'Position als Integer' => ['position', 1, 'bottom-right'];
        yield 'Theme als Array' => ['theme', ['light'], 'auto'];
        yield 'Theme als Objekt' => ['theme', new \stdClass(), 'auto'];
        yield 'Theme als Integer' => ['theme', 1, 'auto'];
        yield 'Sprache als Array' => ['language', ['de'], 'auto'];
        yield 'Sprache als Objekt' => ['language', new \stdClass(), 'auto'];
        yield 'Sprache als Integer' => ['language', 1, 'auto'];
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
     * @param \Closure(): void $constructUnsafeConfiguration
     */
    #[DataProvider('invalidConstructorValueProvider')]
    public function testConfigurationRejectsEveryUnsafeDirectConstruction(\Closure $constructUnsafeConfiguration): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $constructUnsafeConfiguration();
    }

    /**
     * @return iterable<string, array{\Closure(): void}>
     */
    public static function invalidConstructorValueProvider(): iterable
    {
        yield 'Schriftgröße' => [static function (): void { new DisplayConfiguration(25, 12, 5, 9, 999, 10, 'bottom-right', 'auto', 'auto'); }];
        yield 'Abstand' => [static function (): void { new DisplayConfiguration(6, 97, 5, 9, 999, 10, 'bottom-right', 'auto', 'auto'); }];
        yield 'vertikaler Innenabstand' => [static function (): void { new DisplayConfiguration(6, 12, 1, 9, 999, 10, 'bottom-right', 'auto', 'auto'); }];
        yield 'horizontaler Innenabstand' => [static function (): void { new DisplayConfiguration(6, 12, 5, 3, 999, 10, 'bottom-right', 'auto', 'auto'); }];
        yield 'Radius' => [static function (): void { new DisplayConfiguration(6, 12, 5, 9, 1000, 10, 'bottom-right', 'auto', 'auto'); }];
        yield 'Unschärfe' => [static function (): void { new DisplayConfiguration(6, 12, 5, 9, 999, 25, 'bottom-right', 'auto', 'auto'); }];
        yield 'Position' => [static function (): void { new DisplayConfiguration(6, 12, 5, 9, 999, 10, 'center', 'auto', 'auto'); }];
        yield 'Theme' => [static function (): void { new DisplayConfiguration(6, 12, 5, 9, 999, 10, 'bottom-right', 'contrast', 'auto'); }];
        yield 'Sprache' => [static function (): void { new DisplayConfiguration(6, 12, 5, 9, 999, 10, 'bottom-right', 'auto', 'fr'); }];
    }
}
