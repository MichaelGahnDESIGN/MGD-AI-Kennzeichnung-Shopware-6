<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Storefront;

use MGDAIImageLabels\Configuration\DisplayConfigurationProvider;
use MGDAIImageLabels\Media\MediaLabelMetadataNormalizer;
use MGDAIImageLabels\Storefront\Label\LabelLanguageResolver;
use MGDAIImageLabels\Storefront\Label\LabelViewResolver;
use MGDAIImageLabels\Storefront\Twig\LabelTwigExtension;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\PlatformRequest;
use Shopware\Core\SalesChannelRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\ExtensionInterface;

/**
 * Prüft die PHP-, Twig-, Übersetzungs- und Asset-Grenzen der Storefront-Komponente.
 */
final class LabelTemplateTest extends TestCase
{
    private const SALES_CHANNEL_ID = '0123456789abcdef0123456789abcdef';

    private const RESOURCE_ROOT = __DIR__ . '/../../src/Resources/';

    public function testTwigFunctionUsesOnlyTypedStorefrontRequestAttributes(): void
    {
        $request = new Request();
        $context = $this->createMock(SalesChannelContext::class);
        $context->expects(self::once())->method('getSalesChannelId')->willReturn(self::SALES_CHANNEL_ID);
        $request->attributes->set(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT, $context);
        $request->attributes->set(SalesChannelRequest::ATTRIBUTE_DOMAIN_LOCALE, 'de-DE');

        $systemConfig = $this->createSystemConfigServiceMock();
        $calls = [];
        $systemConfig->method('get')->willReturnCallback(
            static function (string $key, ?string $salesChannelId) use (&$calls): mixed {
                $calls[] = [$key, $salesChannelId];

                return null;
            },
        );

        $media = new MediaEntity();
        $media->setCustomFields(['mgd_ai_status' => 'generated']);

        $view = $this->createExtension(new RequestStack([$request]), $systemConfig)->resolve($media);

        self::assertTrue($view->visible);
        self::assertSame('de-DE', $view->locale);
        self::assertSame(
            ['MGDAIImageLabels.config.language', self::SALES_CHANNEL_ID],
            $calls[8],
        );
    }

    public function testTwigFunctionFallsBackToGlobalEnglishWithoutRequest(): void
    {
        $systemConfig = $this->createSystemConfigServiceMock();
        $calls = [];
        $systemConfig->method('get')->willReturnCallback(
            static function (string $key, ?string $salesChannelId) use (&$calls): mixed {
                $calls[] = [$key, $salesChannelId];

                return null;
            },
        );

        $media = new MediaEntity();
        $media->setCustomFields(['mgd_ai_status' => 'modified']);

        $view = $this->createExtension(new RequestStack(), $systemConfig)->resolve($media);

        self::assertTrue($view->visible);
        self::assertSame('en-GB', $view->locale);
        foreach ($calls as $call) {
            self::assertNull($call[1]);
        }
    }

    public function testTwigFunctionRejectsManipulatedRequestAttributes(): void
    {
        $request = new Request();
        $request->attributes->set(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT, self::SALES_CHANNEL_ID);
        $request->attributes->set(SalesChannelRequest::ATTRIBUTE_DOMAIN_LOCALE, ['de-DE']);

        $media = new MediaEntity();
        $media->setCustomFields(['mgd_ai_status' => 'deepfake']);

        $view = $this->createExtension(new RequestStack([$request]))->resolve($media);

        self::assertTrue($view->visible);
        self::assertSame('en-GB', $view->locale);
    }

    public function testTwigFunctionIsRegisteredAndNullMediaRemainsHidden(): void
    {
        $systemConfig = $this->createSystemConfigServiceMock();
        $systemConfig->expects(self::never())->method('get');
        $extension = $this->createExtension(new RequestStack(), $systemConfig);

        self::assertInstanceOf(ExtensionInterface::class, $extension);
        self::assertSame('mgd_ai_image_label', $extension->getFunctions()[0]->getName());
        self::assertFalse($extension->resolve(null)->visible);
    }

    public function testBadgeUsesAccessibleSemanticsAndOnlyTrustedViewValues(): void
    {
        $template = $this->readResource('views/storefront/component/mgd-ai-image-label/badge.html.twig');

        self::assertStringContainsString('{% if label.visible %}', $template);
        self::assertStringContainsString('role="note"', $template);
        self::assertStringContainsString("label.status == 'deepfake'", $template);
        self::assertStringContainsString('class="visually-hidden"', $template);
        self::assertSame(2, substr_count($template, '|trans({}, null, label.locale)|sw_sanitize'));
        self::assertStringNotContainsString('customFields', $template);
        self::assertStringNotContainsString('|raw', $template);
        self::assertStringNotContainsString('media.', $template);

        foreach (['fontSize', 'offset', 'paddingY', 'paddingX', 'radius', 'blur'] as $integerProperty) {
            self::assertMatchesRegularExpression(
                '/--mgd-ai-[a-z-]+:\s*{{\s*label\.' . $integerProperty . '\s*}}px/',
                $template,
            );
        }
    }

    public function testLabeledMediaContainsExactlyOneMediaBlockAndTheBadge(): void
    {
        $template = $this->readResource('views/storefront/component/mgd-ai-image-label/labeled-media.html.twig');

        self::assertSame(1, preg_match_all('/{%-?\s*block\s+mediaContent\s*-?%}/', $template));
        self::assertSame(1, preg_match_all('/{%-?\s*endblock\s*-?%}/', $template));
        self::assertSame(1, substr_count($template, 'badge.html.twig'));
        self::assertStringContainsString('mgd-ai-labeled-media', $template);
    }

    public function testStylesAreLocalAccessibleAndResponsive(): void
    {
        $main = $this->readResource('app/storefront/src/main.js');
        $base = $this->readResource('app/storefront/src/scss/base.scss');
        $component = $this->readResource('app/storefront/src/scss/component/_ai-image-label.scss');
        $allAssets = $main . "\n" . $base . "\n" . $component;

        self::assertStringContainsString("import './scss/base.scss';", $main);
        self::assertStringContainsString("@import 'component/ai-image-label';", $base);
        self::assertStringContainsString('pointer-events: none', $component);
        self::assertStringContainsString('@media (prefers-reduced-motion: reduce)', $component);
        self::assertStringContainsString('@media (prefers-color-scheme: light)', $component);
        self::assertMatchesRegularExpression('/@media\s*\([^)]*max-width/', $component);
        self::assertStringContainsString('#fff', $component);
        self::assertStringContainsString('#111', $component);
        self::assertDoesNotMatchRegularExpression('/https?:\/\//i', $allAssets);
        self::assertDoesNotMatchRegularExpression('/@import\s+url/i', $allAssets);
        self::assertDoesNotMatchRegularExpression('/@font-face|fonts?\.(?:googleapis|gstatic)/i', $allAssets);
    }

    public function testGermanAndEnglishStorefrontSnippetsAreCompleteAndStructurallyEqual(): void
    {
        $german = $this->decodeJsonResource('snippet/de-DE/storefront.de-DE.json');
        $english = $this->decodeJsonResource('snippet/en-GB/storefront.en-GB.json');

        self::assertSame($this->snippetPaths($german), $this->snippetPaths($english));
        self::assertSame([
            'mgd-ai-image-labels.screenReader.deepfake',
            'mgd-ai-image-labels.status.deepfake',
            'mgd-ai-image-labels.status.generated',
            'mgd-ai-image-labels.status.modified',
            'mgd-ai-image-labels.status.partiallyGenerated',
        ], $this->snippetPaths($german));
        self::assertSame('KI-GENERIERT', $this->snippetValue($german, 'mgd-ai-image-labels.status.generated'));
        self::assertSame('AI GENERATED', $this->snippetValue($english, 'mgd-ai-image-labels.status.generated'));
    }

    public function testServiceDefinitionRegistersTheCompatibleTwigExtension(): void
    {
        $document = new \DOMDocument();
        self::assertTrue($document->load(self::RESOURCE_ROOT . 'config/services.xml'));
        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('s', 'http://symfony.com/schema/dic/services');

        $serviceId = 'MGDAIImageLabels\\Storefront\\Twig\\LabelTwigExtension';
        self::assertSame(1, $this->queryLength($xpath, '//s:service[@id="' . $serviceId . '"]'));
        self::assertSame(1, $this->queryLength($xpath, '//s:service[@id="' . $serviceId . '"]/s:tag[@name="twig.extension"]'));
        self::assertSame(1, $this->queryLength($xpath, '//s:service[@id="' . $serviceId . '"]/s:argument[@type="service" and @id="Symfony\\Component\\HttpFoundation\\RequestStack"]'));
    }

    private function createExtension(
        RequestStack $requestStack,
        ?SystemConfigService $systemConfig = null,
    ): LabelTwigExtension {
        $systemConfig ??= $this->createSystemConfigServiceMock();

        return new LabelTwigExtension(
            new LabelViewResolver(
                new MediaLabelMetadataNormalizer(),
                new DisplayConfigurationProvider($systemConfig),
                new LabelLanguageResolver(),
            ),
            $requestStack,
        );
    }

    /** @return SystemConfigService&MockObject */
    private function createSystemConfigServiceMock(): SystemConfigService&MockObject
    {
        return $this->createMock(SystemConfigService::class);
    }

    private function readResource(string $relativePath): string
    {
        $contents = file_get_contents(self::RESOURCE_ROOT . $relativePath);
        self::assertIsString($contents);

        return $contents;
    }

    /** @return array<string, mixed> */
    private function decodeJsonResource(string $relativePath): array
    {
        $decoded = json_decode($this->readResource($relativePath), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded);

        return $this->stringKeyedArray($decoded);
    }

    /**
     * @param array<string, mixed> $snippets
     *
     * @return list<string>
     */
    private function snippetPaths(array $snippets, string $prefix = ''): array
    {
        $paths = [];

        foreach ($snippets as $key => $value) {
            $path = $prefix === '' ? $key : $prefix . '.' . $key;
            if (is_array($value)) {
                $paths = [...$paths, ...$this->snippetPaths($this->stringKeyedArray($value), $path)];
                continue;
            }

            self::assertIsString($value);
            self::assertNotSame('', trim($value));
            $paths[] = $path;
        }

        sort($paths);

        return $paths;
    }

    /**
     * Liest einen einzelnen Snippetwert, ohne ungesicherte Mixed-Offsets zu verwenden.
     *
     * @param array<string, mixed> $snippets
     */
    private function snippetValue(array $snippets, string $path): string
    {
        $segments = explode('.', $path);
        $current = $snippets;

        foreach ($segments as $index => $segment) {
            self::assertArrayHasKey($segment, $current);
            $value = $current[$segment];

            if ($index === array_key_last($segments)) {
                self::assertIsString($value);

                return $value;
            }

            self::assertIsArray($value);
            $current = $this->stringKeyedArray($value);
        }

        self::fail('Der angeforderte Snippetpfad darf nicht leer sein.');
    }

    /** Liefert die Trefferzahl einer gültigen XPath-Abfrage. */
    private function queryLength(\DOMXPath $xpath, string $query): int
    {
        $nodes = $xpath->query($query);
        self::assertNotFalse($nodes);

        return $nodes->length;
    }

    /**
     * Normalisiert fremde JSON-Teilstrukturen auf nachvollziehbare String-Schlüssel.
     *
     * @param array<mixed> $values
     *
     * @return array<string, mixed>
     */
    private function stringKeyedArray(array $values): array
    {
        $normalized = [];

        foreach ($values as $key => $value) {
            self::assertIsString($key);
            $normalized[$key] = $value;
        }

        return $normalized;
    }
}
