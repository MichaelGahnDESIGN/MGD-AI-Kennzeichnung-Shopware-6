<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Storefront;

use MGDAIImageLabels\Configuration\DisplayConfiguration;
use MGDAIImageLabels\Configuration\DisplayConfigurationProvider;
use MGDAIImageLabels\Media\MediaLabelMetadataNormalizer;
use MGDAIImageLabels\Storefront\Label\LabelLanguageResolver;
use MGDAIImageLabels\Storefront\Label\LabelView;
use MGDAIImageLabels\Storefront\Label\LabelViewResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Prüft, dass im Storefront ausschließlich geprüfte, feste Darstellungswerte ankommen.
 */
final class LabelViewResolverTest extends TestCase
{
    private const SALES_CHANNEL_ID = '0123456789abcdef0123456789abcdef';

    /**
     * @param ?string $screenReaderSnippet Nur Deepfake benötigt den erweiterten Hinweis.
     */
    #[DataProvider('visibleStatusCases')]
    public function testResolveMapsEveryVisibleStatusToFixedSnippetKeys(
        string $status,
        string $textSnippet,
        ?string $screenReaderSnippet,
    ): void {
        $view = $this->createResolver()->resolve(
            ['mgd_ai_status' => $status],
            self::SALES_CHANNEL_ID,
            'de-DE',
        );

        self::assertTrue($view->visible);
        self::assertSame($status, $view->status);
        self::assertSame($textSnippet, $view->textSnippet);
        self::assertSame($screenReaderSnippet, $view->screenReaderSnippet);
        self::assertSame('de-DE', $view->locale);
    }

    /**
     * @return iterable<string, array{string, string, ?string}>
     */
    public static function visibleStatusCases(): iterable
    {
        yield 'vollständig generiert' => [
            'generated',
            'mgd-ai-image-labels.status.generated',
            null,
        ];
        yield 'teilweise generiert' => [
            'partially-generated',
            'mgd-ai-image-labels.status.partiallyGenerated',
            null,
        ];
        yield 'bearbeitet' => [
            'modified',
            'mgd-ai-image-labels.status.modified',
            null,
        ];
        yield 'Deepfake' => [
            'deepfake',
            'mgd-ai-image-labels.status.deepfake',
            'mgd-ai-image-labels.screenReader.deepfake',
        ];
    }

    public function testResolveReturnsTheImmutableHiddenViewForNoneWithoutReadingConfiguration(): void
    {
        $systemConfig = $this->createSystemConfigServiceMock();
        $systemConfig->expects(self::never())->method('get');
        $resolver = $this->createResolver($systemConfig);

        $view = $resolver->resolve(['mgd_ai_status' => 'none'], self::SALES_CHANNEL_ID, 'de-DE');

        self::assertEquals(LabelView::hidden(), $view);
        self::assertFalse($view->visible);
        self::assertSame('none', $view->status);
        self::assertTrue((new \ReflectionClass($view))->isReadOnly());
    }

    /**
     * Ungültige Medienwerte dürfen weder als Status noch als CSS-Suffixe in
     * das Viewmodell gelangen.
     */
    public function testResolveHidesManipulatedStatusAndDropsAllRawValues(): void
    {
        $systemConfig = $this->createSystemConfigServiceMock();
        $systemConfig->expects(self::never())->method('get');

        $view = $this->createResolver($systemConfig)->resolve([
            'mgd_ai_status' => '<script>alert(1)</script>',
            'mgd_ai_position' => 'top:0;background:red',
            'mgd_ai_theme' => 'url(https://example.org)',
            'fremdes_feld' => 'darf niemals ausgegeben werden',
        ], self::SALES_CHANNEL_ID, 'de-DE');

        self::assertEquals(LabelView::hidden(), $view);
        self::assertNotContains('<script>alert(1)</script>', (array) $view);
        self::assertNotContains('top:0;background:red', (array) $view);
        self::assertNotContains('url(https://example.org)', (array) $view);
    }

    public function testResolvePrefersIndividualPositionAndThemeAndKeepsIntegers(): void
    {
        $view = $this->createResolver(configuration: [
            'fontSize' => 24,
            'offset' => 96,
            'paddingY' => 24,
            'paddingX' => 40,
            'radius' => 999,
            'blur' => 24,
            'position' => 'bottom-right',
            'theme' => 'auto',
            'language' => 'en',
        ])->resolve([
            'mgd_ai_status' => 'deepfake',
            'mgd_ai_position' => 'top-left',
            'mgd_ai_theme' => 'dark',
        ], self::SALES_CHANNEL_ID, 'de-DE');

        self::assertSame('top-left', $view->position);
        self::assertSame('dark', $view->theme);
        self::assertSame('en-GB', $view->locale);
        self::assertSame(24, $view->fontSize);
        self::assertSame(96, $view->offset);
        self::assertSame(24, $view->paddingY);
        self::assertSame(40, $view->paddingX);
        self::assertSame(999, $view->radius);
        self::assertSame(24, $view->blur);
        self::assertContainsOnly('int', [
            $view->fontSize,
            $view->offset,
            $view->paddingY,
            $view->paddingX,
            $view->radius,
            $view->blur,
        ]);
    }

    public function testResolveUsesSafeGlobalFallbacksForInvalidMediaAndConfigurationValues(): void
    {
        $view = $this->createResolver(configuration: [
            'fontSize' => '24px',
            'offset' => -1,
            'paddingY' => 1,
            'paddingX' => 41,
            'radius' => 1000,
            'blur' => 25,
            'position' => 'center',
            'theme' => 'transparent',
            'language' => 'fr',
        ])->resolve([
            'mgd_ai_status' => 'generated',
            'mgd_ai_position' => 'left;background:red',
            'mgd_ai_theme' => '<style>',
        ], self::SALES_CHANNEL_ID, 'de-CH');

        self::assertSame(DisplayConfiguration::DEFAULT_POSITION, $view->position);
        self::assertSame(DisplayConfiguration::DEFAULT_THEME, $view->theme);
        self::assertSame('de-DE', $view->locale);
        self::assertSame(DisplayConfiguration::DEFAULT_FONT_SIZE, $view->fontSize);
        self::assertSame(DisplayConfiguration::DEFAULT_OFFSET, $view->offset);
        self::assertSame(DisplayConfiguration::DEFAULT_PADDING_Y, $view->paddingY);
        self::assertSame(DisplayConfiguration::DEFAULT_PADDING_X, $view->paddingX);
        self::assertSame(DisplayConfiguration::DEFAULT_RADIUS, $view->radius);
        self::assertSame(DisplayConfiguration::DEFAULT_BLUR, $view->blur);
    }

    public function testResolveAcceptsAllNumericLowerBounds(): void
    {
        $view = $this->createResolver(configuration: [
            'fontSize' => DisplayConfiguration::MIN_FONT_SIZE,
            'offset' => DisplayConfiguration::MIN_OFFSET,
            'paddingY' => DisplayConfiguration::MIN_PADDING_Y,
            'paddingX' => DisplayConfiguration::MIN_PADDING_X,
            'radius' => DisplayConfiguration::MIN_RADIUS,
            'blur' => DisplayConfiguration::MIN_BLUR,
        ])->resolve(['mgd_ai_status' => 'modified'], null, 'en-US');

        self::assertSame(DisplayConfiguration::MIN_FONT_SIZE, $view->fontSize);
        self::assertSame(DisplayConfiguration::MIN_OFFSET, $view->offset);
        self::assertSame(DisplayConfiguration::MIN_PADDING_Y, $view->paddingY);
        self::assertSame(DisplayConfiguration::MIN_PADDING_X, $view->paddingX);
        self::assertSame(DisplayConfiguration::MIN_RADIUS, $view->radius);
        self::assertSame(DisplayConfiguration::MIN_BLUR, $view->blur);
        self::assertSame('en-GB', $view->locale);
    }

    public function testResolvePassesTheSalesChannelIdOnlyToTheLanguageConfiguration(): void
    {
        $systemConfig = $this->createSystemConfigServiceMock();
        $calls = [];
        $systemConfig->expects(self::exactly(9))
            ->method('get')
            ->willReturnCallback(static function (string $key, ?string $salesChannelId) use (&$calls): mixed {
                $calls[] = [$key, $salesChannelId];

                return null;
            });

        $this->createResolver($systemConfig)->resolve(
            ['mgd_ai_status' => 'generated'],
            self::SALES_CHANNEL_ID,
            'en-GB',
        );

        self::assertCount(9, $calls);
        foreach (array_slice($calls, 0, 8) as $call) {
            self::assertNull($call[1]);
        }
        self::assertSame(
            ['MGDAIImageLabels.config.language', self::SALES_CHANNEL_ID],
            $calls[8],
        );
    }

    /**
     * Erzeugt den echten Resolver. Nur Shopwares externer Systemdienst wird
     * testweise ersetzt; alle projektspezifischen Normalizer laufen real.
     *
     * @param array<string, mixed> $configuration Unzuverlässige gespeicherte Testkonfiguration.
     */
    private function createResolver(
        ?SystemConfigService $systemConfig = null,
        array $configuration = [],
    ): LabelViewResolver {
        if ($configuration !== []) {
            if ($systemConfig !== null) {
                throw new \LogicException('Testkonfiguration und fertiger Testdienst dürfen nicht gemischt werden.');
            }

            $configuredSystemConfig = $this->createSystemConfigServiceMock();
            $configuredSystemConfig->method('get')
                ->willReturnCallback(static function (string $key) use ($configuration): mixed {
                    $shortKey = substr($key, (int) strrpos($key, '.') + 1);

                    return $configuration[$shortKey] ?? null;
                });

            $systemConfig = $configuredSystemConfig;
        }

        $systemConfig ??= $this->createSystemConfigServiceMock();

        return new LabelViewResolver(
            new MediaLabelMetadataNormalizer(),
            new DisplayConfigurationProvider($systemConfig),
            new LabelLanguageResolver(),
        );
    }

    /** @return SystemConfigService&MockObject */
    private function createSystemConfigServiceMock(): SystemConfigService&MockObject
    {
        return $this->createMock(SystemConfigService::class);
    }
}
