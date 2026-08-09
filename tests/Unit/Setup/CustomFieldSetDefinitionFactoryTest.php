<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Setup;

use MGDAIImageLabels\Domain\LabelPosition;
use MGDAIImageLabels\Domain\LabelStatus;
use MGDAIImageLabels\Domain\LabelTheme;
use MGDAIImageLabels\Setup\CustomFieldSetDefinitionFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Defaults;
use Shopware\Core\System\CustomField\CustomFieldTypes;

/**
 * Beschreibt den unveränderlichen DAL-Vertrag des Medien-Custom-Field-Sets.
 *
 * Die Tests vergleichen die gespeicherten Optionswerte absichtlich mit den
 * Domain-Enums. So kann keine zweite, unbemerkt abweichende Werteliste
 * entstehen, wenn sich der fachliche Wertebereich später kontrolliert ändert.
 */
final class CustomFieldSetDefinitionFactoryTest extends TestCase
{
    /** Das Set besitzt einen stabilen Namen und verständliche Fallback-Bezeichnungen. */
    public function testCreateSetDefinesTechnicalNameAndTranslatedLabels(): void
    {
        $set = CustomFieldSetDefinitionFactory::createSet();

        self::assertSame(CustomFieldSetDefinitionFactory::SET_NAME, $set['name']);
        self::assertSame('KI-Bildkennzeichnung', $set['config']['label']['de-DE']);
        self::assertSame('AI image labeling', $set['config']['label']['en-GB']);
        self::assertSame('AI image labeling', $set['config']['label'][Defaults::LANGUAGE_SYSTEM]);
    }

    /** Es werden ausschließlich die drei vorgesehenen geschlossenen Auswahllisten angelegt. */
    public function testCreateSetDefinesExactlyThreeSelectFields(): void
    {
        $customFields = CustomFieldSetDefinitionFactory::createSet()['customFields'];

        self::assertCount(3, $customFields);
        self::assertSame(
            ['mgd_ai_status', 'mgd_ai_position', 'mgd_ai_theme'],
            array_column($customFields, 'name'),
        );
        self::assertSame(
            [CustomFieldTypes::SELECT, CustomFieldTypes::SELECT, CustomFieldTypes::SELECT],
            array_column($customFields, 'type'),
        );
        self::assertSame([1, 2, 3], array_column(array_column($customFields, 'config'), 'customFieldPosition'));
        self::assertArrayNotHasKey('mgd_ai_language', array_column($customFields, null, 'name'));

        foreach ($customFields as $customField) {
            self::assertSame('sw-single-select', $customField['config']['componentName']);
            self::assertSame(CustomFieldTypes::SELECT, $customField['config']['customFieldType']);
            self::assertSame(CustomFieldTypes::SELECT, $customField['config']['type']);
            self::assertArrayHasKey('options', $customField['config']);
        }
    }

    /** Statusoptionen übernehmen vollständig die zentrale fachliche Wertemenge. */
    public function testStatusOptionsComeFromStatusEnumAndHaveGermanAndEnglishLabels(): void
    {
        $options = $this->fieldByName('mgd_ai_status')['config']['options'];

        self::assertSame(array_column(LabelStatus::cases(), 'value'), array_column($options, 'value'));
        self::assertCount(5, $options);
        $this->assertTranslatedOptions($options, [
            'none' => ['Keine KI-Kennzeichnung', 'No AI label'],
            'generated' => ['Vollständig KI-generiert', 'Fully AI-generated'],
            'partially-generated' => ['Teilweise KI-generiert', 'Partially AI-generated'],
            'modified' => ['Mit KI verändert', 'Modified with AI'],
            'deepfake' => ['Deepfake', 'Deepfake'],
        ]);
    }

    /** Positionsoptionen übernehmen vollständig die zentrale fachliche Wertemenge. */
    public function testPositionOptionsComeFromPositionEnumAndHaveGermanAndEnglishLabels(): void
    {
        $options = $this->fieldByName('mgd_ai_position')['config']['options'];

        self::assertSame(array_column(LabelPosition::cases(), 'value'), array_column($options, 'value'));
        self::assertCount(4, $options);
        $this->assertTranslatedOptions($options, [
            'top-left' => ['Oben links', 'Top left'],
            'top-right' => ['Oben rechts', 'Top right'],
            'bottom-left' => ['Unten links', 'Bottom left'],
            'bottom-right' => ['Unten rechts', 'Bottom right'],
        ]);
    }

    /** Themeoptionen übernehmen vollständig die zentrale fachliche Wertemenge. */
    public function testThemeOptionsComeFromThemeEnumAndHaveGermanAndEnglishLabels(): void
    {
        $options = $this->fieldByName('mgd_ai_theme')['config']['options'];

        self::assertSame(array_column(LabelTheme::cases(), 'value'), array_column($options, 'value'));
        self::assertCount(3, $options);
        $this->assertTranslatedOptions($options, [
            'auto' => ['Automatisch', 'Automatic'],
            'light' => ['Hell', 'Light'],
            'dark' => ['Dunkel', 'Dark'],
        ]);
    }

    /** Die Relation bindet ausschließlich das gewünschte Set an Medien. */
    public function testCreateRelationConnectsValidSetIdToMedia(): void
    {
        $setId = '61adbfee830f47be9b832585565bc5dd';

        $relation = CustomFieldSetDefinitionFactory::createRelation($setId);

        self::assertSame($setId, $relation['customFieldSetId']);
        self::assertSame('media', $relation['entityName']);
        self::assertSame(['id', 'customFieldSetId', 'entityName'], array_keys($relation));
    }

    /** Nicht valide IDs dürfen keine mehrdeutigen oder unbrauchbaren Relationen erzeugen. */
    #[DataProvider('invalidSetIds')]
    public function testCreateRelationRejectsInvalidSetId(string $setId): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('gültige Shopware-ID');

        CustomFieldSetDefinitionFactory::createRelation($setId);
    }

    /** @return iterable<string, array{string}> */
    public static function invalidSetIds(): iterable
    {
        yield 'leer' => [''];
        yield 'Leerzeichen' => ['   '];
        yield 'kein Hexadezimalwert' => ['nicht-gueltig'];
        yield 'zu kurz' => ['abc123'];
    }

    /**
     * @return array{name: string, type: string, config: array<string, mixed>}
     */
    private function fieldByName(string $name): array
    {
        $fields = array_column(CustomFieldSetDefinitionFactory::createSet()['customFields'], null, 'name');

        self::assertArrayHasKey($name, $fields);

        return $fields[$name];
    }

    /**
     * @param list<array{label: array<string, string>, value: string}> $options
     * @param array<string, array{string, string}> $expectedLabels
     */
    private function assertTranslatedOptions(array $options, array $expectedLabels): void
    {
        foreach ($options as $option) {
            [$german, $english] = $expectedLabels[$option['value']];
            self::assertSame($german, $option['label']['de-DE']);
            self::assertSame($english, $option['label']['en-GB']);
        }
    }
}
