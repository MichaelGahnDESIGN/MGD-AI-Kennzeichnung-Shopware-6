<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Domain;

use MGDAIImageLabels\Configuration\DisplayConfiguration;
use MGDAIImageLabels\Domain\LabelLanguage;
use MGDAIImageLabels\Domain\LabelPosition;
use MGDAIImageLabels\Domain\LabelStatus;
use MGDAIImageLabels\Domain\LabelTheme;
use MGDAIImageLabels\Media\MediaLabelMetadata;
use PHPUnit\Framework\TestCase;

/**
 * Prüft die vollständigen, zentralen Wertebereiche aller Label-Enume.
 */
final class LabelValueEnumTest extends TestCase
{
    /** Alle Statuswerte bilden den bestehenden öffentlichen Vertrag ab. */
    public function testLabelStatusCasesMatchThePublicStatusValidator(): void
    {
        self::assertSame(['none', 'generated', 'partially-generated', 'modified', 'deepfake'], $this->values(LabelStatus::cases()));

        foreach (LabelStatus::cases() as $status) {
            self::assertTrue(MediaLabelMetadata::isAllowedStatus($status->value));
        }

        self::assertFalse(MediaLabelMetadata::isAllowedStatus('unknown'));
    }

    /** Alle Positionswerte bilden den bestehenden öffentlichen Vertrag ab. */
    public function testLabelPositionCasesMatchThePublicPositionValidators(): void
    {
        self::assertSame(['top-left', 'top-right', 'bottom-left', 'bottom-right'], $this->values(LabelPosition::cases()));

        foreach (LabelPosition::cases() as $position) {
            self::assertTrue(MediaLabelMetadata::isAllowedPosition($position->value));
            self::assertTrue(DisplayConfiguration::isAllowedPosition($position->value));
        }

        self::assertFalse(MediaLabelMetadata::isAllowedPosition('center'));
    }

    /** Alle Theme-Werte bilden den bestehenden öffentlichen Vertrag ab. */
    public function testLabelThemeCasesMatchThePublicThemeValidators(): void
    {
        self::assertSame(['auto', 'light', 'dark'], $this->values(LabelTheme::cases()));

        foreach (LabelTheme::cases() as $theme) {
            self::assertTrue(MediaLabelMetadata::isAllowedTheme($theme->value));
            self::assertTrue(DisplayConfiguration::isAllowedTheme($theme->value));
        }

        self::assertFalse(MediaLabelMetadata::isAllowedTheme('contrast'));
    }

    /** Alle Sprachwerte bilden den bestehenden öffentlichen Vertrag ab. */
    public function testLabelLanguageCasesMatchThePublicLanguageValidator(): void
    {
        self::assertSame(['auto', 'de', 'en'], $this->values(LabelLanguage::cases()));

        foreach (LabelLanguage::cases() as $language) {
            self::assertTrue(DisplayConfiguration::isAllowedLanguage($language->value));
        }

        self::assertFalse(DisplayConfiguration::isAllowedLanguage('fr'));
    }

    /**
     * @param list<LabelLanguage|LabelPosition|LabelStatus|LabelTheme> $cases Backed-Enum-Fälle.
     *
     * @return list<string> Die serialisierten, stabilen Fachwerte.
     */
    private function values(array $cases): array
    {
        return array_map(static fn (LabelLanguage|LabelPosition|LabelStatus|LabelTheme $case): string => $case->value, $cases);
    }
}
