<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Storefront;

use Composer\InstalledVersions;
use MGDAIImageLabels\Storefront\Thumbnail\ThumbnailPresentationResolver;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Adapter\Twig\TemplateFinderInterface;
use Shopware\Core\Framework\Adapter\Twig\TemplateScopeDetector;
use Shopware\Core\Framework\Adapter\Twig\TokenParser\EmbedTokenParser;
use Shopware\Core\Framework\Adapter\Twig\TokenParser\ExtendsTokenParser;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\TwigFunction;

/**
 * Schützt die zentrale Thumbnail-Integration gegen doppelte Bilder, Attribute
 * und gegen unbeabsichtigte Labels in der kleinen Galerie-Navigation.
 */
final class ThumbnailIntegrationTemplateTest extends TestCase
{
    private const TEMPLATE_PATH = __DIR__ . '/../../src/Resources/views/storefront/utilities/thumbnail.html.twig';

    public function testOverrideTargetsOnlyTheResearchedStableOuterBlock(): void
    {
        $template = $this->readTemplate();

        self::assertSame(1, preg_match_all('/{%-?\s*block\s+thumbnail_utility\s*-?%}/', $template));
        self::assertStringNotContainsString('{% block thumbnail_utility_img %}', $template);
        self::assertStringNotContainsString('srcsetValue', $template);
        self::assertStringNotContainsString('srcsetValues', $template);
        self::assertStringNotContainsString('sizesValue', $template);
        self::assertStringNotContainsString('sizesValues', $template);
    }

    public function testInstalledShopwareSourcesExposeTheVerifiedThumbnailAndGalleryContracts(): void
    {
        $version = InstalledVersions::getPrettyVersion('shopware/storefront');
        self::assertIsString($version);
        self::assertMatchesRegularExpression('/^v?6\.(?:6\.10|7)\./', $version);

        $shopwareViews = __DIR__ . '/../../vendor/shopware/storefront/Resources/views/storefront/';
        $thumbnail = file_get_contents($shopwareViews . 'utilities/thumbnail.html.twig');
        $gallery = file_get_contents($shopwareViews . 'element/cms-element-image-gallery.html.twig');
        self::assertIsString($thumbnail);
        self::assertIsString($gallery);

        $outerPosition = strpos($thumbnail, '{% block thumbnail_utility %}');
        $imagePosition = strpos($thumbnail, '{% block thumbnail_utility_img %}');
        self::assertIsInt($outerPosition);
        self::assertIsInt($imagePosition);
        self::assertLessThan($imagePosition, $outerPosition);
        self::assertStringContainsString("class: 'gallery-slider-image'", $gallery);
        self::assertStringContainsString("class: 'gallery-slider-thumbnails-image'", $gallery);
        self::assertStringContainsString("{% sw_thumbnails 'gallery-slider-image-thumbnails'", $gallery);
        self::assertStringContainsString("{% sw_thumbnails 'gallery-slider-thumbnails-image-thumbnails'", $gallery);
    }

    public function testUsesShopwareInheritanceAndRendersParentAndResolverExactlyOnce(): void
    {
        $template = $this->readTemplate();

        self::assertStringContainsString(
            "{% sw_extends '@Storefront/storefront/utilities/thumbnail.html.twig' %}",
            $template,
        );
        self::assertSame(1, substr_count($template, 'mgd_ai_image_label(media)'));
        self::assertSame(1, substr_count($template, 'mgd_ai_thumbnail_presentation('));
        self::assertSame(1, substr_count($template, 'parent()'));
        self::assertSame(1, substr_count($template, 'labeled-media.html.twig'));
        self::assertStringContainsString('presentation.labelAllowed', $template);
        self::assertStringContainsString('presentation.intrinsicLayout', $template);
        self::assertStringContainsString('presentation.floatEndLayout', $template);
        self::assertStringNotContainsString('|split', $template);
        self::assertStringNotContainsString('gallery-slider-thumbnails-image', $template);
    }

    public function testHiddenLabelReturnsTheUnchangedParentOutput(): void
    {
        $parentOutput = $this->renderParent();
        $labelResolverCalls = 0;
        $presentationResolverCalls = 0;

        $output = $this->renderOverride(
            ['visible' => false],
            'product-image is-contain',
            $labelResolverCalls,
            $presentationResolverCalls,
        );

        self::assertSame($parentOutput, $output);
        self::assertSame(1, $labelResolverCalls);
        self::assertSame(1, $presentationResolverCalls);
    }

    public function testVisibleLabelWrapsTheUnchangedParentContentExactlyOnce(): void
    {
        $labelResolverCalls = 0;
        $presentationResolverCalls = 0;

        $output = $this->renderOverride(
            ['visible' => true],
            'product-image is-contain',
            $labelResolverCalls,
            $presentationResolverCalls,
            ['intrinsicLayout' => true],
        );

        self::assertSame(1, $labelResolverCalls);
        self::assertSame(1, $presentationResolverCalls);
        self::assertSame(1, substr_count($output, '<img '));
        self::assertSame(1, substr_count($output, 'class="product-image is-contain"'));
        self::assertSame(1, substr_count($output, 'src="/media/product.webp"'));
        self::assertSame(1, substr_count($output, 'srcset="/media/product-400.webp 400w, /media/product-800.webp 800w"'));
        self::assertSame(1, substr_count($output, 'sizes="(min-width: 1200px) 800px, 100vw"'));
        self::assertSame(1, substr_count($output, 'alt="Ein &amp; sicheres Bild"'));
        self::assertSame(1, substr_count($output, 'title="Produkt &quot;Eins&quot;"'));
        self::assertSame(1, substr_count($output, 'loading="lazy"'));
        self::assertSame(1, substr_count($output, 'data-image-zoom="true"'));
        self::assertSame(1, substr_count($output, 'class="mgd-ai-labeled-media mgd-ai-labeled-media--fill"'));
        self::assertSame(1, substr_count($output, 'mgd-ai-test-badge'));
        self::assertStringContainsString('mgd-ai-labeled-media--fill', $output);
        self::assertStringNotContainsString('mgd-ai-labeled-media--intrinsic', $output);
    }

    public function testExcludesOnlyTheExactGalleryNavigationThumbnailClass(): void
    {
        $labelResolverCalls = 0;
        $presentationResolverCalls = 0;
        $excluded = $this->renderOverride(
            ['visible' => true],
            'gallery-slider-thumbnails-image js-load-img',
            $labelResolverCalls,
            $presentationResolverCalls,
            ['name' => 'gallery-slider-thumbnails-image-thumbnails'],
        );

        self::assertSame($this->renderParent('gallery-slider-thumbnails-image js-load-img'), $excluded);
        self::assertStringNotContainsString('mgd-ai-labeled-media', $excluded);

        foreach ([
            ['gallery-slider-image js-image-zoom-element js-load-img', 'gallery-slider-image-thumbnails', 'cover', 'fill'],
            ['product-image is-contain', 'product-image-thumbnails', null, 'fill'],
            ['cms-image', 'cms-image-thumbnails', 'standard', 'intrinsic'],
            ['img-fluid line-item-img', 'line-item-img-thumbnails', null, 'fill'],
            ['navigation-flyout-teaser-image img-fluid', 'navigation-flyout-teaser-image-thumbnails', null, 'intrinsic'],
            ['payment-method-image', 'payment-method-image-thumbnails', null, 'intrinsic-float-end'],
            ['shipping-method-image', 'shipping-method-image-thumbnails', null, 'intrinsic-float-end'],
            ['not-gallery-slider-thumbnails-image', 'theme-thumbnail', null, 'intrinsic'],
        ] as [$includedClass, $name, $displayMode, $layout]) {
            $output = $this->renderOverride(
                ['visible' => true],
                $includedClass,
                $labelResolverCalls,
                $presentationResolverCalls,
                ['name' => $name, 'displayMode' => $displayMode],
            );
            self::assertStringContainsString('mgd-ai-labeled-media--' . $layout, $output, $includedClass);
        }

        self::assertSame(9, $labelResolverCalls);
        self::assertSame(9, $presentationResolverCalls);
    }

    public function testMissingOptionalAttributesDoNotExcludeCmsImages(): void
    {
        $labelResolverCalls = 0;
        $presentationResolverCalls = 0;
        $output = $this->renderOverride(
            ['visible' => true],
            null,
            $labelResolverCalls,
            $presentationResolverCalls,
        );

        self::assertStringContainsString('mgd-ai-labeled-media--intrinsic', $output);
        self::assertSame(1, $labelResolverCalls);
        self::assertSame(1, $presentationResolverCalls);
    }

    public function testFillLayoutPreservesShopwaresFullWidthAndFullHeightImageContract(): void
    {
        $styles = file_get_contents(
            __DIR__ . '/../../src/Resources/app/storefront/src/scss/component/_ai-image-label.scss',
        );
        self::assertIsString($styles);
        self::assertMatchesRegularExpression(
            '/\.mgd-ai-labeled-media--fill\s*\{[^}]*width:\s*100%;[^}]*height:\s*100%;[^}]*\}/s',
            $styles,
        );
    }

    private function readTemplate(): string
    {
        $template = file_get_contents(self::TEMPLATE_PATH);
        self::assertIsString($template);

        return $template;
    }

    /**
     * Rendert das Override mit Shopwares echten Vererbungs-Tokenparsern. Nur die
     * Bundle-Pfade werden für den lokalen ArrayLoader eindeutig aufgelöst.
     *
     * @param array{visible: bool} $label
     * @param array<string, mixed> $additionalContext
     */
    private function renderOverride(
        array $label,
        ?string $mediaClass,
        int &$labelResolverCalls,
        int &$presentationResolverCalls,
        array $additionalContext = [],
    ): string {
        $labeledMedia = <<<'TWIG'
<div class="mgd-ai-labeled-media{% if intrinsicLayout is defined and intrinsicLayout is same as(true) %} mgd-ai-labeled-media--intrinsic{% else %} mgd-ai-labeled-media--fill{% endif %}{% if floatEndLayout is defined and floatEndLayout is same as(true) %} mgd-ai-labeled-media--intrinsic-float-end{% endif %}">{% block mediaContent %}{% endblock %}<span class="mgd-ai-test-badge"></span></div>
TWIG;
        $twig = new Environment(
            new ArrayLoader([
                'shopware-thumbnail' => $this->parentTemplate(),
                'labeled-media' => $labeledMedia,
                'override' => $this->readTemplate(),
            ]),
            ['autoescape' => 'html'],
        );
        $finder = $this->createTemplateFinder();
        $twig->addTokenParser(new ExtendsTokenParser($finder, new TemplateScopeDetector(new RequestStack())));
        $twig->addTokenParser(new EmbedTokenParser($finder));
        $twig->addFunction(new TwigFunction(
            'mgd_ai_image_label',
            static function () use ($label, &$labelResolverCalls): array {
                ++$labelResolverCalls;

                return $label;
            },
        ));
        $presentationResolver = new ThumbnailPresentationResolver();
        $twig->addFunction(new TwigFunction(
            'mgd_ai_thumbnail_presentation',
            static function (mixed $name, mixed $attributes, mixed $context) use ($presentationResolver, &$presentationResolverCalls): object {
                ++$presentationResolverCalls;

                return $presentationResolver->resolve($name, $attributes, $context);
            },
        ));

        $context = [
            'name' => 'product-image-thumbnails',
            ...$additionalContext,
            'media' => ['url' => '/media/product.webp'],
        ];
        if ($mediaClass !== null) {
            $context['attributes'] = ['class' => $mediaClass];
        }

        return $twig->render('override', $context);
    }

    private function renderParent(string $mediaClass = 'product-image is-contain'): string
    {
        $twig = new Environment(
            new ArrayLoader(['shopware-thumbnail' => $this->parentTemplate()]),
            ['autoescape' => 'html'],
        );

        return $twig->render('shopware-thumbnail', [
            'media' => ['url' => '/media/product.webp'],
            'attributes' => ['class' => $mediaClass],
        ]);
    }

    private function parentTemplate(): string
    {
        return <<<'TWIG'
{% block thumbnail_utility %}<img src="{{ media.url }}" srcset="/media/product-400.webp 400w, /media/product-800.webp 800w" sizes="(min-width: 1200px) 800px, 100vw" alt="Ein &amp; sicheres Bild" title="Produkt &quot;Eins&quot;" loading="lazy" data-image-zoom="true" class="{{ attributes.class|default('') }}">{% endblock %}
TWIG;
    }

    /**
     * Bildet ausschließlich die beiden im Test erlaubten Bundle-Pfade auf die
     * lokalen ArrayLoader-Namen ab. Unbekannte Pfade brechen bewusst hart ab.
     */
    private function createTemplateFinder(): TemplateFinderInterface
    {
        return new class () implements TemplateFinderInterface {
            public function getTemplateName(string $template): string
            {
                return $template;
            }

            public function find(string $template, mixed $ignoreMissing = false, ?string $source = null): string
            {
                return match ($template) {
                    '@Storefront/storefront/utilities/thumbnail.html.twig' => 'shopware-thumbnail',
                    '@MGDAIImageLabels/storefront/component/mgd-ai-image-label/labeled-media.html.twig' => 'labeled-media',
                    default => throw new \LogicException('Unerwarteter Template-Pfad: ' . $template),
                };
            }
        };
    }
}
