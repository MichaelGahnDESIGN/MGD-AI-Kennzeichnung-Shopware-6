<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Storefront;

use MGDAIImageLabels\Storefront\Thumbnail\ThumbnailPresentation;
use MGDAIImageLabels\Storefront\Thumbnail\ThumbnailPresentationResolver;
use MGDAIImageLabels\Storefront\Twig\ThumbnailPresentationTwigExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Prüft die geschlossene, fehlertolerante Präsentationswahl für Shopware-Thumbnails.
 */
final class ThumbnailPresentationResolverTest extends TestCase
{
    public function testResolverIsAvailableAsASeparateTypedBoundary(): void
    {
        self::assertTrue(class_exists(ThumbnailPresentationResolver::class));
    }

    public function testDedicatedTwigExtensionIsAvailable(): void
    {
        self::assertTrue(class_exists(ThumbnailPresentationTwigExtension::class));
    }

    public function testTwigExtensionRegistersOnlyTheTypedPresentationFunction(): void
    {
        $extension = new ThumbnailPresentationTwigExtension(new ThumbnailPresentationResolver());

        self::assertSame('mgd_ai_thumbnail_presentation', $extension->getFunctions()[0]->getName());
        self::assertEquals(
            ThumbnailPresentation::fill(),
            $extension->resolve(
                'line-item-img-thumbnails',
                ['class' => 'img-fluid line-item-img'],
            ),
        );
    }

    public function testServicesRegisterResolverAndTwigExtensionExplicitly(): void
    {
        $services = file_get_contents(__DIR__ . '/../../src/Resources/config/services.xml');
        self::assertIsString($services);
        self::assertStringContainsString(
            '<service id="MGDAIImageLabels\Storefront\Thumbnail\ThumbnailPresentationResolver"/>',
            $services,
        );
        self::assertStringContainsString(
            '<service id="MGDAIImageLabels\Storefront\Twig\ThumbnailPresentationTwigExtension">',
            $services,
        );
        self::assertStringContainsString(
            '<argument type="service" id="MGDAIImageLabels\Storefront\Thumbnail\ThumbnailPresentationResolver"/>',
            $services,
        );
    }

    /**
     * @param array<string, mixed>|list<mixed>|string|null $attributes
     * @param array<string, mixed>|string|null $context
     */
    #[DataProvider('provenFillCallers')]
    public function testUsesFillOnlyForProvenSlotFillingCallers(
        string $name,
        array|string|null $attributes,
        array|string|null $context,
    ): void {
        self::assertEquals(
            ThumbnailPresentation::fill(),
            (new ThumbnailPresentationResolver())->resolve($name, $attributes, $context),
        );
    }

    /** @return iterable<string, array{string, array<string, mixed>|list<mixed>|string|null, array<string, mixed>|string|null}> */
    public static function provenFillCallers(): iterable
    {
        yield 'MGD CMS-Hintergrundbild' => [
            'mgd-ai-background-image-thumbnails',
            ['class' => 'mgd-ai-background-image__media'],
            ['displayMode' => 'cover'],
        ];
        yield 'CMS-Blockhintergrund' => [
            'cms-block-background',
            ['class' => "cms-block-background\tmedia-mode--cover"],
            null,
        ];
        yield 'YouTube-Platzhalter ohne Attribute' => ['cms-element-youtube-video__placeholder', null, null];
        yield 'Vimeo-Platzhalter ohne Attribute' => ['cms-element-vimeo-video__placeholder', null, null];
        yield 'CMS-Bild-Slider' => [
            'cms-image-slider-thumbnails',
            ['class' => ['img-fluid', ['image-slider-image']]],
            ['displayMode' => 'standard'],
        ];
        yield 'Produktlisting' => [
            'product-image-thumbnails',
            ['class' => "product-image\ris-contain"],
            null,
        ];
        yield 'Warenkorb' => [
            'line-item-img-thumbnails',
            ['class' => ['img-fluid', 'line-item-img']],
            null,
        ];
        yield 'Variantenkonfigurator' => [
            'configurator-option-img-thumbnails',
            ['class' => "product-detail-configurator-option-image\fh-100"],
            null,
        ];
        yield 'Galerie Cover' => [
            'gallery-slider-image-thumbnails',
            ['class' => 'gallery-slider-image js-image-zoom-element'],
            ['displayMode' => 'cover'],
        ];
        yield 'Galerie Contain' => [
            'gallery-slider-image-thumbnails',
            ['class' => 'gallery-slider-image'],
            ['element' => ['displayMode' => 'contain']],
        ];
        yield 'CMS Cover' => [
            'cms-image-thumbnails',
            ['class' => 'cms-image'],
            ['configDisplayMode' => 'cover'],
        ];
        yield 'CMS Stretch' => [
            'cms-image-thumbnails',
            ['class' => 'cms-image'],
            ['elementDisplayMode' => 'stretch'],
        ];
    }

    /**
     * @param array<string, mixed>|list<mixed>|string|null $attributes
     * @param array<string, mixed>|string|null $context
     */
    #[DataProvider('intrinsicAndFallbackCallers')]
    public function testKeepsIntrinsicCallersAndUnknownInputsConservative(
        mixed $name,
        mixed $attributes,
        mixed $context,
    ): void {
        self::assertEquals(
            ThumbnailPresentation::intrinsic(),
            (new ThumbnailPresentationResolver())->resolve($name, $attributes, $context),
        );
    }

    /** @return iterable<string, array{mixed, mixed, mixed}> */
    public static function intrinsicAndFallbackCallers(): iterable
    {
        yield 'CMS-Standardbild' => ['cms-image-thumbnails', ['class' => 'cms-image'], ['displayMode' => 'standard']];
        yield 'CMS-Containbild' => ['cms-image-thumbnails', ['class' => 'cms-image'], ['displayMode' => 'contain']];
        yield 'Herstellerlogo' => ['cms-image-thumbnails', ['class' => 'cms-image product-detail-manufacturer-logo'], ['displayMode' => 'standard']];
        yield 'Galerie-Standardbild' => ['gallery-slider-image-thumbnails', ['class' => 'gallery-slider-image'], ['displayMode' => 'standard']];
        yield 'Quickview-Hauptbild' => ['minimal-image-thumbnails', ['class' => 'img-fluid quickview-minimal-img'], null];
        yield 'Quickview-Herstellerlogo' => ['quickview-minimal-product-manufacturer-logo', ['class' => 'quickview-minimal-product-manufacturer-logo'], null];
        yield 'altes Produktdetail-Herstellerlogo' => ['product-detail-manufacturer-image-thumbnails', ['class' => 'product-detail-manufacturer-logo'], null];
        yield 'Suchvorschau' => ['search-suggest-product-image-thumbnails', ['class' => 'search-suggest-product-image'], null];
        yield 'Navigationsteaser' => ['navigation-flyout-teaser-image-thumbnails', ['class' => "navigation-flyout-teaser-image\nimg-fluid"], null];
        yield 'Footer-Zahlungslogo' => ['footer-payment-image-thumbnails', ['class' => 'img-fluid footer-logo-image'], null];
        yield 'Footer-Versandlogo' => ['footer-shipping-image-thumbnails', ['class' => 'img-fluid footer-logo-image'], null];
        yield 'unbekannter Name trotz freiem Coverwert' => ['theme-controlled-thumbnail', ['class' => 'theme-image'], ['displayMode' => 'cover']];
        yield 'bekannter Name mit falscher Klasse' => ['product-image-thumbnails', ['class' => 'theme-controlled-image'], null];
        yield 'präfixierte Ausschlussklasse' => ['theme-controlled-thumbnail', ['class' => 'not-gallery-slider-thumbnails-image'], null];
        yield 'Alt-Text ist keine Klasse' => ['theme-controlled-thumbnail', ['alt' => 'gallery-slider-thumbnails-image'], null];
        yield 'manipulierte Typen' => [new \stdClass(), ['class' => [42, false, new \stdClass()]], ['displayMode' => new \stdClass()]];
        yield 'sehr tief verschachtelte Eingabe' => ['theme-controlled-thumbnail', ['class' => [[[[[[[[[[['gallery-slider-image']]]]]]]]]]]], null];
    }

    #[DataProvider('floatingMethodLogoCallers')]
    public function testUsesClosedFloatEndVariantOnlyForOfficialMethodLogos(string $name, string $class): void
    {
        self::assertEquals(
            ThumbnailPresentation::intrinsicFloatEnd(),
            (new ThumbnailPresentationResolver())->resolve($name, ['class' => $class]),
        );
    }

    /** @return iterable<string, array{string, string}> */
    public static function floatingMethodLogoCallers(): iterable
    {
        yield 'Zahlungsart' => ['payment-method-image-thumbnails', 'payment-method-image'];
        yield 'Versandart' => ['shipping-method-image-thumbnails', 'shipping-method-image'];
    }

    public function testDoesNotExposeFloatEndVariantThroughFreeThemeValues(): void
    {
        self::assertEquals(
            ThumbnailPresentation::intrinsic(),
            (new ThumbnailPresentationResolver())->resolve(
                'theme-controlled-thumbnail',
                ['class' => 'payment-method-image shipping-method-image'],
                ['floatEndLayout' => true],
            ),
        );
    }

    #[DataProvider('galleryNavigationInputs')]
    public function testExcludesOnlyExactGalleryNavigationSignals(mixed $name, mixed $attributes): void
    {
        self::assertEquals(
            ThumbnailPresentation::excluded(),
            (new ThumbnailPresentationResolver())->resolve($name, $attributes),
        );
    }

    /** @return iterable<string, array{mixed, mixed}> */
    public static function galleryNavigationInputs(): iterable
    {
        yield 'offizieller Name' => ['gallery-slider-thumbnails-image-thumbnails', null];
        yield 'Klasse mit allen ASCII-Leerzeichen' => [
            'theme-thumbnail',
            ['class' => "foo\t\ngallery-slider-thumbnails-image\f\r bar"],
        ];
        yield 'flache Klassenliste' => ['theme-thumbnail', ['foo', 'gallery-slider-thumbnails-image']];
        yield 'verschachtelte Klassenliste' => ['theme-thumbnail', [['foo'], [['gallery-slider-thumbnails-image']]]];
        yield 'verschachtelte Attributzuordnung' => [
            'theme-thumbnail',
            ['wrapper' => ['class' => ['foo', ['gallery-slider-thumbnails-image']]], 'alt' => 'kein Klassentoken'],
        ];
    }
}
