<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Storefront;

use PHPUnit\Framework\TestCase;
use Shopware\Storefront\Framework\Twig\TokenParser\ThumbnailTokenParser;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Source;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Schützt Ausgabe, Sicherheit und Badge-Einmaligkeit des CMS-Hintergrundbilds.
 */
final class BackgroundImageTemplateTest extends TestCase
{
    private const TEMPLATE = __DIR__ . '/../../src/Resources/views/storefront/element/cms-element-mgd-ai-background-image.html.twig';

    public function testUsesOnlyShopwareMediaAndClosedPresentationMappings(): void
    {
        $template = $this->template();

        self::assertStringContainsString("{% sw_thumbnails 'mgd-ai-background-image-thumbnails'", $template);
        self::assertStringContainsString('mgd_ai_image_label(element.data.media)', $template);
        self::assertStringNotContainsString('element.data.media.url', $template);
        self::assertStringNotContainsString('sw_encode_url', $template);
        self::assertStringNotContainsString('style=', $template);
        self::assertStringNotContainsString('|raw', $template);
        self::assertDoesNotMatchRegularExpression('/https?:\/\//i', $template);
        self::assertStringContainsString('presentation.heightClass', $template);
        self::assertStringContainsString('presentation.fallbackClass', $template);
        self::assertStringNotContainsString('element.translated.config', $template);
        self::assertStringNotContainsString('[minHeight]', $template);
    }

    public function testTemplateParsesWithShopwaresRealThumbnailTokenParser(): void
    {
        $twig = new Environment(new ArrayLoader());
        $twig->addTokenParser(new ThumbnailTokenParser());
        $twig->addFunction(new TwigFunction('mgd_ai_image_label', static fn(): array => ['visible' => false]));
        $twig->addFilter(new TwigFilter('trans', static fn(string $value): string => $value));

        $module = $twig->parse($twig->tokenize(new Source($this->template(), 'mgd-ai-background-image')));

        self::assertNotEmpty($module->getNode('body'));
    }

    public function testDecorativeSemanticsAreExplicitAndNonDecorativeImageHasFallbackAltText(): void
    {
        $template = $this->template();

        self::assertStringContainsString("presentation.decorative is same as(true)", $template);
        self::assertStringContainsString("'aria-hidden': 'true'", $template);
        self::assertStringContainsString("'alt': ''", $template);
        self::assertStringContainsString("'alt': presentation.altText", $template);
        self::assertStringNotContainsString('element.data.media.translated.alt', $template);
        self::assertStringNotContainsString('element.data.media.translated.title', $template);
        self::assertStringNotContainsString('element.data.media.fileName', $template);
        self::assertStringNotContainsString('fallbackAlt', $template);
    }

    public function testPassesTheResolvedLabelIntoStandardThumbnailIntegrationExactlyOnce(): void
    {
        $template = $this->template();
        $thumbnailOverride = file_get_contents(__DIR__ . '/../../src/Resources/views/storefront/utilities/thumbnail.html.twig');
        self::assertIsString($thumbnailOverride);

        self::assertSame(1, substr_count($template, 'mgd_ai_image_label('));
        self::assertSame(1, substr_count($template, 'mgdAiLabel: label'));
        self::assertStringContainsString('mgdAiLabel is defined', $thumbnailOverride);
        self::assertStringContainsString("name|default(null) == 'mgd-ai-background-image-thumbnails'", $thumbnailOverride);
        self::assertSame(1, substr_count($thumbnailOverride, 'mgd_ai_image_label(media)'));
        self::assertSame(0, substr_count($template, 'badge.html.twig'));
    }

    public function testRealTwigRenderingKeepsSemanticsEscapedAndOneLabelOnly(): void
    {
        $labelCalls = 0;
        $renderableTemplate = preg_replace(
            '/{%\s*sw_thumbnails\s+\'mgd-ai-background-image-thumbnails\'\s+with\s+\{.*?}\s*%}/s',
            "{% include 'thumbnail-stub' with { media: element.data.media, attributes: attributes, mgdAiLabel: label } %}",
            $this->template(),
            1,
            $replacementCount,
        );
        self::assertIsString($renderableTemplate);
        self::assertSame(1, $replacementCount);

        $twig = new Environment(new ArrayLoader([
            'cms-background' => $renderableTemplate,
            'thumbnail-stub' => <<<'TWIG'
<img class="{{ attributes.class }}" alt="{{ attributes.alt }}"{% if attributes['aria-hidden'] is defined %} aria-hidden="{{ attributes['aria-hidden'] }}"{% endif %}>{% if mgdAiLabel.visible %}<span class="rendered-label" role="note"></span>{% endif %}
TWIG,
        ]), ['autoescape' => 'html']);
        $twig->addFilter(new TwigFilter('trans', static fn(string $value): string => $value));
        $twig->addFunction(new TwigFunction(
            'mgd_ai_image_label',
            static function () use (&$labelCalls): array {
                ++$labelCalls;

                return ['visible' => true];
            },
        ));

        $decorative = $twig->render('cms-background', $this->renderContext(true, '<script>alert(1)</script>'));
        self::assertSame(1, $labelCalls);
        self::assertSame(1, substr_count($decorative, 'rendered-label'));
        self::assertStringContainsString('alt=""', $decorative);
        self::assertStringContainsString('aria-hidden="true"', $decorative);
        self::assertStringNotContainsString('<script>', $decorative);

        $semantic = $twig->render('cms-background', $this->renderContext(false, '<script>alert(1)</script>'));
        self::assertSame(2, $labelCalls);
        self::assertSame(1, substr_count($semantic, 'rendered-label'));
        self::assertStringNotContainsString('aria-hidden=', $semantic);
        self::assertStringContainsString('alt="&lt;script&gt;alert(1)&lt;/script&gt;"', $semantic);
        self::assertStringNotContainsString('<script>', $semantic);

        $emptySemantic = $twig->render('cms-background', $this->renderContext(false, ''));
        self::assertSame(3, $labelCalls);
        self::assertSame(1, substr_count($emptySemantic, 'rendered-label'));
        self::assertStringContainsString('alt=""', $emptySemantic);
        self::assertStringNotContainsString('aria-hidden=', $emptySemantic);
    }

    public function testResponsiveStylesUseOnlyFixedClassesAndPreserveBadgeLayout(): void
    {
        $scss = file_get_contents(__DIR__ . '/../../src/Resources/app/storefront/src/scss/component/_cms-background-image.scss');
        self::assertIsString($scss);

        self::assertStringContainsString('object-fit: cover', $scss);
        self::assertStringContainsString('@media (max-width:', $scss);
        self::assertStringContainsString('.mgd-ai-labeled-media', $scss);
        self::assertStringNotContainsString('url(', $scss);
        self::assertDoesNotMatchRegularExpression('/https?:\/\//i', $scss);
    }

    private function template(): string
    {
        $template = file_get_contents(self::TEMPLATE);
        self::assertIsString($template);

        return $template;
    }

    /** @return array<string, mixed> */
    private function renderContext(bool $decorative, string $alt): array
    {
        return ['element' => [
            'translated' => ['config' => [
                'minHeight' => ['value' => ['manipuliert']],
                'horizontalPosition' => ['value' => new \stdClass()],
                'verticalPosition' => ['value' => true],
                'fallbackColor' => ['value' => 42],
                'decorative' => ['value' => '<script>'],
            ]],
            'data' => ['media' => [
                'translated' => ['alt' => $alt, 'title' => 'Titel'],
                'fileName' => 'bild',
                'thumbnails' => [],
            ], 'presentation' => [
                'heightClass' => 'mgd-ai-background-image--height-320',
                'horizontalClass' => 'mgd-ai-background-image--horizontal-center',
                'verticalClass' => 'mgd-ai-background-image--vertical-center',
                'fallbackClass' => 'mgd-ai-background-image--fallback-neutral-light',
                'decorative' => $decorative,
                'altText' => $alt,
            ]],
        ]];
    }
}
