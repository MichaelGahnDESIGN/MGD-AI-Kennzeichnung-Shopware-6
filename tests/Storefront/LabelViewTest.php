<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Storefront;

use MGDAIImageLabels\Configuration\DisplayConfiguration;
use MGDAIImageLabels\Domain\LabelPosition;
use MGDAIImageLabels\Domain\LabelStatus;
use MGDAIImageLabels\Domain\LabelTheme;
use MGDAIImageLabels\Storefront\Label\LabelView;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Prüft die Invarianten des unveränderlichen Storefront-Viewmodells direkt.
 */
final class LabelViewTest extends TestCase
{
    public function testVisibleRejectsTheHiddenNoneStatus(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        LabelView::visible(
            LabelStatus::None,
            LabelPosition::BottomRight,
            LabelTheme::Auto,
            'de',
            $this->configuration(),
        );
    }

    #[DataProvider('unresolvedLanguageCases')]
    public function testVisibleRejectsEveryUnresolvedLanguage(string $language): void
    {
        $this->expectException(\InvalidArgumentException::class);

        LabelView::visible(
            LabelStatus::Generated,
            LabelPosition::BottomRight,
            LabelTheme::Auto,
            $language,
            $this->configuration(),
        );
    }

    /** @return iterable<string, array{string}> */
    public static function unresolvedLanguageCases(): iterable
    {
        yield 'Automatik ist noch nicht aufgelöst' => ['auto'];
        yield 'unbekannte Sprache' => ['fr'];
        yield 'freie Eingabe' => ['de-DE; color:red'];
        yield 'leerer Wert' => [''];
    }

    private function configuration(): DisplayConfiguration
    {
        return new DisplayConfiguration(
            fontSize: DisplayConfiguration::DEFAULT_FONT_SIZE,
            offset: DisplayConfiguration::DEFAULT_OFFSET,
            paddingY: DisplayConfiguration::DEFAULT_PADDING_Y,
            paddingX: DisplayConfiguration::DEFAULT_PADDING_X,
            radius: DisplayConfiguration::DEFAULT_RADIUS,
            blur: DisplayConfiguration::DEFAULT_BLUR,
            position: DisplayConfiguration::DEFAULT_POSITION,
            theme: DisplayConfiguration::DEFAULT_THEME,
            language: DisplayConfiguration::DEFAULT_LANGUAGE,
        );
    }
}
