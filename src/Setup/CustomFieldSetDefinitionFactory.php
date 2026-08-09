<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Setup;

use MGDAIImageLabels\Domain\LabelPosition;
use MGDAIImageLabels\Domain\LabelStatus;
use MGDAIImageLabels\Domain\LabelTheme;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\CustomField\CustomFieldTypes;

/**
 * Erstellt die vollständigen DAL-Nutzdaten des Medien-Custom-Field-Sets.
 *
 * Die Factory ist bewusst zustandslos: Installation, Aktualisierung und Tests
 * erhalten dadurch immer dieselbe Definition. Technische IDs werden aus den
 * unveränderlichen Namen abgeleitet. Das macht DAL-Upserts wiederholbar, ohne
 * feste UUIDs im Quellcode zu hinterlegen oder bestehende Datensätze erraten zu
 * müssen. Es werden weder personenbezogene Daten noch externe Dienste berührt.
 *
 * @phpstan-type TranslatedLabel array{'de-DE': string, 'en-GB': string}
 * @phpstan-type SelectOption array{label: TranslatedLabel, value: string}
 * @phpstan-type SelectConfig array{
 *     componentName: 'sw-single-select',
 *     customFieldType: 'select',
 *     type: 'select',
 *     customFieldPosition: int,
 *     label: TranslatedLabel,
 *     options: list<SelectOption>
 * }
 * @phpstan-type CustomFieldPayload array{
 *     id: string,
 *     name: string,
 *     type: 'select',
 *     active: true,
 *     config: SelectConfig
 * }
 * @phpstan-type CustomFieldSetPayload array{
 *     id: string,
 *     name: 'mgd_ai_image_labels',
 *     active: true,
 *     config: array{label: TranslatedLabel, translated: true},
 *     customFields: list<CustomFieldPayload>
 * }
 * @phpstan-type RelationPayload array{id: string, customFieldSetId: string, entityName: 'media'}
 */
final readonly class CustomFieldSetDefinitionFactory
{
    /** Technischer, plugin-eigener Name des Custom-Field-Sets. */
    public const SET_NAME = 'mgd_ai_image_labels';

    /** Verhindert eine versehentliche Instanziierung der zustandslosen Factory. */
    private function __construct()
    {
    }

    /**
     * Liefert das Set einschließlich seiner drei geschlossenen Auswahlfelder.
     *
     * Eine Spracheinstellung gehört absichtlich nicht hierher. Sie bleibt eine
     * SystemConfig-Einstellung und kann dadurch je Verkaufskanal aufgelöst
     * werden, ohne Medien mit redundanten Sprachwerten anzureichern.
     *
     * @return CustomFieldSetPayload
     */
    public static function createSet(): array
    {
        return [
            'id' => self::setId(),
            'name' => self::SET_NAME,
            'active' => true,
            'config' => [
                'label' => self::translatedLabel('KI-Bildkennzeichnung', 'AI image labeling'),
                'translated' => true,
            ],
            'customFields' => [
                self::createSelectField(
                    name: 'mgd_ai_status',
                    position: 1,
                    germanLabel: 'KI-Status',
                    englishLabel: 'AI status',
                    options: self::statusOptions(),
                ),
                self::createSelectField(
                    name: 'mgd_ai_position',
                    position: 2,
                    germanLabel: 'Position der Kennzeichnung',
                    englishLabel: 'Label position',
                    options: self::positionOptions(),
                ),
                self::createSelectField(
                    name: 'mgd_ai_theme',
                    position: 3,
                    germanLabel: 'Theme der Kennzeichnung',
                    englishLabel: 'Label theme',
                    options: self::themeOptions(),
                ),
            ],
        ];
    }

    /** Liefert die allein dem Plugin gehörende, reproduzierbare Set-ID. */
    public static function setId(): string
    {
        return self::technicalId(self::SET_NAME);
    }

    /**
     * Erstellt die eindeutige Zuordnung des Sets zur Shopware-Medienentität.
     *
     * @return RelationPayload
     */
    public static function createRelation(string $customFieldSetId): array
    {
        if (!Uuid::isValid($customFieldSetId)) {
            throw new \InvalidArgumentException('Für die Medienrelation ist eine gültige Shopware-ID erforderlich.');
        }

        return [
            'id' => self::technicalId($customFieldSetId . ':media'),
            'customFieldSetId' => $customFieldSetId,
            'entityName' => 'media',
        ];
    }

    /**
     * @param list<SelectOption> $options
     *
     * @return CustomFieldPayload
     */
    private static function createSelectField(
        string $name,
        int $position,
        string $germanLabel,
        string $englishLabel,
        array $options,
    ): array {
        return [
            'id' => self::technicalId($name),
            'name' => $name,
            'type' => CustomFieldTypes::SELECT,
            'active' => true,
            'config' => [
                'componentName' => 'sw-single-select',
                'customFieldType' => CustomFieldTypes::SELECT,
                'type' => CustomFieldTypes::SELECT,
                'customFieldPosition' => $position,
                'label' => self::translatedLabel($germanLabel, $englishLabel),
                'options' => $options,
            ],
        ];
    }

    /** @return list<SelectOption> */
    private static function statusOptions(): array
    {
        return array_map(
            static function (LabelStatus $status): array {
                [$german, $english] = match ($status) {
                    LabelStatus::None => ['Keine KI-Kennzeichnung', 'No AI label'],
                    LabelStatus::Generated => ['Vollständig KI-generiert', 'Fully AI-generated'],
                    LabelStatus::PartiallyGenerated => ['Teilweise KI-generiert', 'Partially AI-generated'],
                    LabelStatus::Modified => ['Mit KI verändert', 'Modified with AI'],
                    LabelStatus::Deepfake => ['Deepfake', 'Deepfake'],
                };

                return self::createOption($status->value, $german, $english);
            },
            LabelStatus::cases(),
        );
    }

    /** @return list<SelectOption> */
    private static function positionOptions(): array
    {
        return array_map(
            static function (LabelPosition $position): array {
                [$german, $english] = match ($position) {
                    LabelPosition::TopLeft => ['Oben links', 'Top left'],
                    LabelPosition::TopRight => ['Oben rechts', 'Top right'],
                    LabelPosition::BottomLeft => ['Unten links', 'Bottom left'],
                    LabelPosition::BottomRight => ['Unten rechts', 'Bottom right'],
                };

                return self::createOption($position->value, $german, $english);
            },
            LabelPosition::cases(),
        );
    }

    /** @return list<SelectOption> */
    private static function themeOptions(): array
    {
        return array_map(
            static function (LabelTheme $theme): array {
                [$german, $english] = match ($theme) {
                    LabelTheme::Auto => ['Automatisch', 'Automatic'],
                    LabelTheme::Light => ['Hell', 'Light'],
                    LabelTheme::Dark => ['Dunkel', 'Dark'],
                };

                return self::createOption($theme->value, $german, $english);
            },
            LabelTheme::cases(),
        );
    }

    /** @return SelectOption */
    private static function createOption(string $value, string $german, string $english): array
    {
        return [
            'label' => self::translatedLabel($german, $english),
            'value' => $value,
        ];
    }

    /** @return TranslatedLabel */
    private static function translatedLabel(string $german, string $english): array
    {
        return [
            'de-DE' => $german,
            'en-GB' => $english,
        ];
    }

    /** Leitet eine gültige, reproduzierbare UUID aus einem technischen Namen ab. */
    private static function technicalId(string $technicalName): string
    {
        return Uuid::fromStringToHex('MGDAIImageLabels:' . $technicalName);
    }
}
