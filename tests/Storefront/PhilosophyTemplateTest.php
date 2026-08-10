<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Storefront;

use MGDAIImageLabels\Cms\Philosophy\PhilosophyDefaultContent;
use MGDAIImageLabels\Storefront\Twig\PhilosophyTwigExtension;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Adapter\Twig\Extension\SwSanitizeTwigFilter;
use Shopware\Core\Framework\Util\HtmlSanitizer;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/** Prüft die sichere Ausgabegrenze des Rich-Text-CMS-Elements. */
final class PhilosophyTemplateTest extends TestCase
{
    public function testTemplateAcceptsOnlyStringsAndUsesAnExplicitSanitizerAllowlist(): void
    {
        $template = file_get_contents(
            __DIR__ . '/../../src/Resources/views/storefront/element/cms-element-mgd-ai-philosophy.html.twig',
        );
        self::assertIsString($template);
        self::assertStringContainsString('mgd_ai_philosophy_content(rawContent, locale)', $template);
        self::assertStringContainsString('|sw_sanitize(', $template);
        self::assertStringContainsString('true)', $template);

        foreach (['script', 'style', 'iframe', 'object', 'embed', 'form', 'onclick', 'onerror'] as $forbidden) {
            self::assertStringNotContainsString("'{$forbidden}':", $template);
        }
    }

    public function testRealTwigRenderingRemovesDangerousTagsAttributesAndProtocols(): void
    {
        $template = file_get_contents(
            __DIR__ . '/../../src/Resources/views/storefront/element/cms-element-mgd-ai-philosophy.html.twig',
        );
        self::assertIsString($template);
        $twig = new Environment(new ArrayLoader(['philosophy' => $template]), ['autoescape' => 'html']);
        $twig->addExtension(new PhilosophyTwigExtension(new PhilosophyDefaultContent()));
        $twig->addExtension(new SwSanitizeTwigFilter(new HtmlSanitizer(null, false)));
        $malicious = <<<'HTML'
<h2 onclick="alert(1)">Sicher</h2><script>alert(2)</script><style>body{display:none}</style><iframe src="https://example.invalid"></iframe><object></object><embed src="x"><form><input></form><a href="javascript:alert(3)" onmouseover="alert(4)">Link</a><p>Text</p>
HTML;

        $output = $twig->render('philosophy', ['element' => ['fieldConfig' => ['elements' => [
            'content' => ['value' => $malicious],
        ]]]]);

        self::assertStringContainsString('<h2>Sicher</h2>', $output);
        self::assertStringContainsString('<p>Text</p>', $output);
        foreach (['<script', '<style', '<iframe', '<object', '<embed', '<form', '<input', 'onclick', 'onmouseover', 'javascript:'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $output);
        }
    }

    public function testEmptyAndNonStringValuesRenderSafely(): void
    {
        $template = file_get_contents(
            __DIR__ . '/../../src/Resources/views/storefront/element/cms-element-mgd-ai-philosophy.html.twig',
        );
        self::assertIsString($template);
        $twig = new Environment(new ArrayLoader(['philosophy' => $template]), ['autoescape' => 'html']);
        $twig->addExtension(new PhilosophyTwigExtension(new PhilosophyDefaultContent()));
        $twig->addExtension(new SwSanitizeTwigFilter(new HtmlSanitizer(null, false)));

        foreach ([null, ['<script>'], new \stdClass()] as $value) {
            $output = $twig->render('philosophy', ['app' => ['request' => ['locale' => 'fr-FR']], 'element' => ['fieldConfig' => ['elements' => [
                'content' => ['value' => $value],
            ]]]]);
            self::assertStringContainsString('cms-element-mgd-ai-philosophy', $output);
            self::assertStringNotContainsString('<script>', $output);
            self::assertStringContainsString('Our approach to AI imagery', $output);
        }

        foreach ([
            'de-DE' => 'Unser Umgang mit KI-Bildern',
            'en-GB' => 'Our approach to AI imagery',
            'fr-FR' => 'Our approach to AI imagery',
        ] as $locale => $expectedHeading) {
            $output = $twig->render('philosophy', ['app' => ['request' => ['locale' => $locale]], 'element' => ['fieldConfig' => ['elements' => [
                'content' => ['value' => ''],
            ]]]]);
            self::assertStringContainsString($expectedHeading, $output);
        }
    }

    public function testManualEmptyElementUsesLocaleAwareDefaultsWithEnglishFallback(): void
    {
        $defaults = new PhilosophyDefaultContent();
        self::assertStringContainsString('Unser Umgang mit KI-Bildern', $defaults->forLocale('de-DE'));
        self::assertStringContainsString('Our approach to AI imagery', $defaults->forLocale('en-GB'));
        self::assertSame($defaults->forLocale('en-GB'), $defaults->forLocale('fr-FR'));
        self::assertSame($defaults->forLocale('en-GB'), $defaults->forLocale(null));
        self::assertSame('<p>Eigener Inhalt</p>', $defaults->resolve('<p>Eigener Inhalt</p>', 'de-DE'));
        self::assertSame($defaults->forLocale('de-DE'), $defaults->resolve('   ', 'de-DE'));
        self::assertSame($defaults->forLocale('en-GB'), $defaults->resolve(['falsch'], 'en-GB'));
    }

    public function testServerDefaultsAndAdministrationSnippetsRemainIdentical(): void
    {
        foreach ([
            'de-DE' => PhilosophyDefaultContent::german(),
            'en-GB' => PhilosophyDefaultContent::english(),
        ] as $locale => $expected) {
            $json = file_get_contents(
                __DIR__ . '/../../src/Resources/app/administration/src/snippet/' . $locale . '.json',
            );
            self::assertIsString($json);
            $snippet = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
            self::assertIsArray($snippet);
            $plugin = $snippet['mgd-ai-image-labels'] ?? null;
            self::assertIsArray($plugin);
            $philosophy = $plugin['philosophy'] ?? null;
            self::assertIsArray($philosophy);
            self::assertSame($expected, $philosophy['defaultContent'] ?? null);
        }
    }
}
