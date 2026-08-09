<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Storefront\Label;

use MGDAIImageLabels\Configuration\DisplayConfiguration;
use MGDAIImageLabels\Domain\LabelLanguage;
use MGDAIImageLabels\Domain\LabelPosition;
use MGDAIImageLabels\Domain\LabelStatus;
use MGDAIImageLabels\Domain\LabelTheme;

/**
 * Unveränderliches und vollständig geprüftes Viewmodell eines Storefront-Labels.
 *
 * Der private Konstruktor verhindert, dass Templates oder spätere Dienste rohe
 * Daten einschleusen. Sichtbare Instanzen entstehen ausschließlich aus Enums
 * und der bereits geprüften Darstellungskonfiguration. Snippet-Schlüssel und
 * Ausgabe-Locale werden intern aus geschlossenen Wertemengen abgeleitet.
 */
final readonly class LabelView
{
    /** Deutsche Locale für die ausdrücklich deutsche Snippet-Ausgabe. */
    private const GERMAN_LOCALE = 'de-DE';

    /** Englische Locale für die neutrale beziehungsweise erzwungene Ausgabe. */
    private const ENGLISH_LOCALE = 'en-GB';

    /**
     * @param bool $visible Gibt an, ob das Template das Label ausgeben darf.
     * @param string $status Sicheres Klassensuffix und fachlicher Status.
     * @param ?string $textSnippet Fester Schlüssel des sichtbaren Textes.
     * @param ?string $screenReaderSnippet Optionaler fester Zusatz für assistive Technik.
     * @param string $locale Explizite, feste Shopware-Ausgabe-Locale.
     * @param string $position Sicheres Positionssuffix aus der Domain.
     * @param string $theme Sicheres Themesuffix aus der Domain.
     * @param int $fontSize Schriftgröße in Pixeln.
     * @param int $offset Abstand zum Bildrand in Pixeln.
     * @param int $paddingY Vertikaler Innenabstand in Pixeln.
     * @param int $paddingX Horizontaler Innenabstand in Pixeln.
     * @param int $radius Eckenradius in Pixeln.
     * @param int $blur Hintergrundunschärfe in Pixeln.
     */
    private function __construct(
        public bool $visible,
        public string $status,
        public ?string $textSnippet,
        public ?string $screenReaderSnippet,
        public string $locale,
        public string $position,
        public string $theme,
        public int $fontSize,
        public int $offset,
        public int $paddingY,
        public int $paddingX,
        public int $radius,
        public int $blur,
    ) {
    }

    /**
     * Erzeugt die kanonische, unsichtbare Instanz ohne fremde Eingabewerte.
     */
    public static function hidden(): self
    {
        return new self(
            visible: false,
            status: LabelStatus::None->value,
            textSnippet: null,
            screenReaderSnippet: null,
            locale: self::ENGLISH_LOCALE,
            position: DisplayConfiguration::DEFAULT_POSITION,
            theme: DisplayConfiguration::DEFAULT_THEME,
            fontSize: DisplayConfiguration::DEFAULT_FONT_SIZE,
            offset: DisplayConfiguration::DEFAULT_OFFSET,
            paddingY: DisplayConfiguration::DEFAULT_PADDING_Y,
            paddingX: DisplayConfiguration::DEFAULT_PADDING_X,
            radius: DisplayConfiguration::DEFAULT_RADIUS,
            blur: DisplayConfiguration::DEFAULT_BLUR,
        );
    }

    /**
     * Erzeugt ein sichtbares Label ausschließlich aus geprüften Fachwerten.
     *
     * @param LabelStatus $status Sichtbarer Status; „none“ ist hier unzulässig.
     * @param LabelPosition $position Geprüfte individuelle oder globale Position.
     * @param LabelTheme $theme Geprüftes individuelles oder globales Theme.
     * @param string $language Vom Sprachresolver auf „de“ oder „en“ reduzierter Wert.
     * @param DisplayConfiguration $configuration Vollständig geprüfte Zahlenkonfiguration.
     */
    public static function visible(
        LabelStatus $status,
        LabelPosition $position,
        LabelTheme $theme,
        string $language,
        DisplayConfiguration $configuration,
    ): self {
        if ($status === LabelStatus::None) {
            throw new \InvalidArgumentException('Der Status „none“ darf kein sichtbares Label erzeugen.');
        }

        if ($language !== LabelLanguage::German->value && $language !== LabelLanguage::English->value) {
            throw new \InvalidArgumentException('Die Ausgabesprache muss vor dem Erzeugen des Labels aufgelöst sein.');
        }

        return new self(
            visible: true,
            status: $status->value,
            textSnippet: self::textSnippetFor($status),
            screenReaderSnippet: self::screenReaderSnippetFor($status),
            locale: $language === LabelLanguage::German->value ? self::GERMAN_LOCALE : self::ENGLISH_LOCALE,
            position: $position->value,
            theme: $theme->value,
            fontSize: $configuration->fontSize,
            offset: $configuration->offset,
            paddingY: $configuration->paddingY,
            paddingX: $configuration->paddingX,
            radius: $configuration->radius,
            blur: $configuration->blur,
        );
    }

    /** Liefert den unveränderlichen Textschlüssel für einen sichtbaren Status. */
    private static function textSnippetFor(LabelStatus $status): string
    {
        return match ($status) {
            LabelStatus::Generated => 'mgd-ai-image-labels.status.generated',
            LabelStatus::PartiallyGenerated => 'mgd-ai-image-labels.status.partiallyGenerated',
            LabelStatus::Modified => 'mgd-ai-image-labels.status.modified',
            LabelStatus::Deepfake => 'mgd-ai-image-labels.status.deepfake',
            LabelStatus::None => throw new \LogicException('Für „none“ existiert kein sichtbarer Textschlüssel.'),
        };
    }

    /** Liefert nur für Deepfakes den zusätzlichen Screenreader-Hinweis. */
    private static function screenReaderSnippetFor(LabelStatus $status): ?string
    {
        return $status === LabelStatus::Deepfake
            ? 'mgd-ai-image-labels.screenReader.deepfake'
            : null;
    }
}
