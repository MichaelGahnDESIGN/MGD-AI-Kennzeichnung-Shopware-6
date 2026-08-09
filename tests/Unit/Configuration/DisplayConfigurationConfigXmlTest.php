<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Configuration;

use MGDAIImageLabels\Configuration\DisplayConfiguration;
use MGDAIImageLabels\Domain\LabelLanguage;
use MGDAIImageLabels\Domain\LabelPosition;
use MGDAIImageLabels\Domain\LabelTheme;
use PHPUnit\Framework\TestCase;
use Shopware\Core\System\SystemConfig\Util\ConfigReader;

/**
 * Prüft den vollständigen Vertrag zwischen Shopwares Konfigurationsmaske und
 * der sicheren PHP-Domäne, ohne versionsspezifische Reader-Typen anzunehmen.
 */
final class DisplayConfigurationConfigXmlTest extends TestCase
{
    /**
     * Jedes Feld, sein Wertebereich, seine Optionen und alle sichtbaren
     * deutschen sowie englischen Texte müssen dem PHP-Vertrag entsprechen.
     */
    public function testConfigXmlMatchesTheCompleteDisplayConfigurationDomain(): void
    {
        $cards = (new ConfigReader())->read(dirname(__DIR__, 3) . '/src/Resources/config/config.xml');
        $elements = $this->elementsByName($cards);

        self::assertSame([
            'language',
            'position',
            'theme',
            'fontSize',
            'offset',
            'paddingY',
            'paddingX',
            'radius',
            'blur',
        ], array_keys($elements));

        $this->assertIntField($elements['fontSize'], DisplayConfiguration::DEFAULT_FONT_SIZE, DisplayConfiguration::MIN_FONT_SIZE, DisplayConfiguration::MAX_FONT_SIZE);
        $this->assertIntField($elements['offset'], DisplayConfiguration::DEFAULT_OFFSET, DisplayConfiguration::MIN_OFFSET, DisplayConfiguration::MAX_OFFSET);
        $this->assertIntField($elements['paddingY'], DisplayConfiguration::DEFAULT_PADDING_Y, DisplayConfiguration::MIN_PADDING_Y, DisplayConfiguration::MAX_PADDING_Y);
        $this->assertIntField($elements['paddingX'], DisplayConfiguration::DEFAULT_PADDING_X, DisplayConfiguration::MIN_PADDING_X, DisplayConfiguration::MAX_PADDING_X);
        $this->assertIntField($elements['radius'], DisplayConfiguration::DEFAULT_RADIUS, DisplayConfiguration::MIN_RADIUS, DisplayConfiguration::MAX_RADIUS);
        $this->assertIntField($elements['blur'], DisplayConfiguration::DEFAULT_BLUR, DisplayConfiguration::MIN_BLUR, DisplayConfiguration::MAX_BLUR);

        $this->assertSelectField($elements['position'], DisplayConfiguration::DEFAULT_POSITION, $this->enumValues(LabelPosition::cases()));
        $this->assertSelectField($elements['theme'], DisplayConfiguration::DEFAULT_THEME, $this->enumValues(LabelTheme::cases()));
        $this->assertSelectField($elements['language'], DisplayConfiguration::DEFAULT_LANGUAGE, $this->enumValues(LabelLanguage::cases()));

        foreach ($cards as $card) {
            $this->assertTranslationsAreNotEmpty($card['title']);

            foreach ($card['elements'] as $element) {
                $this->assertTranslationsAreNotEmpty($element['label']);
                $this->assertTranslationsAreNotEmpty($element['helpText']);

                if (!isset($element['options']) || !is_array($element['options'])) {
                    continue;
                }

                foreach ($element['options'] as $option) {
                    self::assertIsArray($option);
                    self::assertArrayHasKey('name', $option);
                    self::assertIsArray($option['name']);
                    $this->assertTranslationsAreNotEmpty($option['name']);
                }
            }
        }
    }

    /**
     * @param array<string, mixed> $element Ein geparstes Shopware-Integerfeld.
     */
    private function assertIntField(array $element, int $default, int $min, int $max): void
    {
        self::assertSame('int', $element['type']);
        self::assertSame($default, $this->toInteger($element['defaultValue']));
        self::assertSame($min, $this->toInteger($element['min']));
        self::assertSame($max, $this->toInteger($element['max']));
    }

    /**
     * @param array<string, mixed> $element Ein geparstes Shopware-Auswahlfeld.
     * @param list<string> $expectedOptions Die vollständige Enum-Wertemenge.
     */
    private function assertSelectField(array $element, string $default, array $expectedOptions): void
    {
        self::assertSame('single-select', $element['type']);
        self::assertSame($default, $element['defaultValue']);

        $actualOptions = $this->optionIds($element);

        self::assertSame($expectedOptions, $actualOptions);
        self::assertSame([], array_values(array_diff($expectedOptions, $actualOptions)));
        self::assertSame([], array_values(array_diff($actualOptions, $expectedOptions)));
    }

    /**
     * Konvertiert Shopware-6.6-/6.7-Readerwerte sicher in Integer. Je nach
     * Reader-Version werden min/max als String oder Integer bereitgestellt.
     */
    private function toInteger(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && preg_match('/^-?\\d+$/D', $value) === 1) {
            return (int) $value;
        }

        self::fail('Ein Integer-Konfigurationswert muss als Integer oder Dezimalzahl vorliegen.');
    }

    /**
     * @param array<array<string, mixed>> $cards Shopwares geparste Karten.
     *
     * @return array<string, array<string, mixed>> Elemente nach ihrem Konfigurationsnamen.
     */
    private function elementsByName(array $cards): array
    {
        $elements = [];

        foreach ($cards as $card) {
            $cardElements = $card['elements'] ?? null;
            self::assertIsArray($cardElements);

            foreach ($cardElements as $element) {
                self::assertIsArray($element);
                $name = $element['name'] ?? null;
                self::assertIsString($name);
                $elements[$name] = $element;
            }
        }

        return $elements;
    }

    /**
     * @param array<string, mixed> $element Das von Shopware gelesene Auswahlfeld.
     *
     * @return list<string> Die Optionen in ihrer Konfigurationsreihenfolge.
     */
    private function optionIds(array $element): array
    {
        $options = $element['options'] ?? null;
        self::assertIsArray($options);

        $ids = [];
        foreach ($options as $option) {
            self::assertIsArray($option);
            $id = $option['id'] ?? null;
            self::assertIsString($id);
            $ids[] = $id;
        }

        return $ids;
    }

    /**
     * @param array<string, string|null> $translations Die Shopware-Übersetzungen eines sichtbaren Textes.
     */
    private function assertTranslationsAreNotEmpty(array $translations): void
    {
        foreach (['de-DE', 'en-GB'] as $locale) {
            self::assertArrayHasKey($locale, $translations);
            self::assertIsString($translations[$locale]);
            self::assertNotSame('', trim($translations[$locale]));
        }
    }

    /**
     * @param list<LabelLanguage|LabelPosition|LabelTheme> $cases Die Fachwerte eines Backed Enums.
     *
     * @return list<string> Die serialisierten Fachwerte.
     */
    private function enumValues(array $cases): array
    {
        return array_map(static fn (LabelLanguage|LabelPosition|LabelTheme $case): string => $case->value, $cases);
    }
}
