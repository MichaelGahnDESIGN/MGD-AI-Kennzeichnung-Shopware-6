<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Configuration;

use MGDAIImageLabels\Configuration\DisplayConfiguration;
use MGDAIImageLabels\Media\MediaLabelMetadata;
use PHPUnit\Framework\TestCase;
use Shopware\Core\System\SystemConfig\Util\ConfigReader;

/**
 * Prüft, dass die Shopware-Administrationsmaske dieselben Regeln anbietet wie
 * die PHP-Domäne. So können UI und sichere Laufzeitprüfung nicht auseinanderlaufen.
 */
final class DisplayConfigurationConfigXmlTest extends TestCase
{
    /**
     * Alle XML-Standards, Zahlenbereiche und Auswahllisten müssen zur Domain
     * passen. Der echte Shopware-Reader validiert dabei auch die XML-Struktur.
     */
    public function testConfigXmlMatchesTheDisplayConfigurationDomain(): void
    {
        $cards = (new ConfigReader())->read(dirname(__DIR__, 3) . '/src/Resources/config/config.xml');
        $elements = $this->elementsByName($cards);

        self::assertSame(DisplayConfiguration::DEFAULT_FONT_SIZE, $elements['fontSize']['defaultValue']);
        self::assertSame((string) DisplayConfiguration::MIN_FONT_SIZE, $elements['fontSize']['min']);
        self::assertSame((string) DisplayConfiguration::MAX_FONT_SIZE, $elements['fontSize']['max']);
        self::assertSame(DisplayConfiguration::DEFAULT_OFFSET, $elements['offset']['defaultValue']);
        self::assertSame((string) DisplayConfiguration::MIN_OFFSET, $elements['offset']['min']);
        self::assertSame((string) DisplayConfiguration::MAX_OFFSET, $elements['offset']['max']);
        self::assertSame(DisplayConfiguration::DEFAULT_PADDING_Y, $elements['paddingY']['defaultValue']);
        self::assertSame((string) DisplayConfiguration::MIN_PADDING_Y, $elements['paddingY']['min']);
        self::assertSame((string) DisplayConfiguration::MAX_PADDING_Y, $elements['paddingY']['max']);
        self::assertSame(DisplayConfiguration::DEFAULT_PADDING_X, $elements['paddingX']['defaultValue']);
        self::assertSame((string) DisplayConfiguration::MIN_PADDING_X, $elements['paddingX']['min']);
        self::assertSame((string) DisplayConfiguration::MAX_PADDING_X, $elements['paddingX']['max']);
        self::assertSame(DisplayConfiguration::DEFAULT_RADIUS, $elements['radius']['defaultValue']);
        self::assertSame((string) DisplayConfiguration::MIN_RADIUS, $elements['radius']['min']);
        self::assertSame((string) DisplayConfiguration::MAX_RADIUS, $elements['radius']['max']);
        self::assertSame(DisplayConfiguration::DEFAULT_BLUR, $elements['blur']['defaultValue']);
        self::assertSame((string) DisplayConfiguration::MIN_BLUR, $elements['blur']['min']);
        self::assertSame((string) DisplayConfiguration::MAX_BLUR, $elements['blur']['max']);

        self::assertSame(DisplayConfiguration::DEFAULT_POSITION, $elements['position']['defaultValue']);
        self::assertSame(['top-left', 'top-right', 'bottom-left', 'bottom-right'], $this->optionIds($elements['position']));
        self::assertSame(DisplayConfiguration::DEFAULT_THEME, $elements['theme']['defaultValue']);
        self::assertSame(['auto', 'light', 'dark'], $this->optionIds($elements['theme']));
        self::assertSame(DisplayConfiguration::DEFAULT_LANGUAGE, $elements['language']['defaultValue']);
        self::assertSame(['auto', 'de', 'en'], $this->optionIds($elements['language']));

        foreach ($this->optionIds($elements['position']) as $position) {
            self::assertTrue(MediaLabelMetadata::isAllowedPosition($position));
        }

        foreach ($this->optionIds($elements['theme']) as $theme) {
            self::assertTrue(MediaLabelMetadata::isAllowedTheme($theme));
        }

        foreach ($this->optionIds($elements['language']) as $language) {
            self::assertTrue(DisplayConfiguration::isAllowedLanguage($language));
        }
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
            foreach ($card['elements'] as $element) {
                if (isset($element['name']) && is_string($element['name'])) {
                    $elements[$element['name']] = $element;
                }
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
        $options = $element['options'] ?? [];

        self::assertIsArray($options);

        return array_values(array_map(
            static fn (array $option): string => (string) ($option['id'] ?? ''),
            $options,
        ));
    }
}
