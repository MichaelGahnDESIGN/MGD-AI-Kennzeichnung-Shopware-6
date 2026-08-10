<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Storefront;

use PHPUnit\Framework\TestCase;
use MGDAIImageLabels\Storefront\Twig\PhilosophyTwigExtension;
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
        self::assertStringContainsString('mgd_ai_philosophy_content(rawContent)', $template);
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
        $twig->addExtension(new PhilosophyTwigExtension());
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
        $twig->addExtension(new PhilosophyTwigExtension());
        $twig->addExtension(new SwSanitizeTwigFilter(new HtmlSanitizer(null, false)));

        foreach ([null, ['<script>'], new \stdClass()] as $value) {
            $output = $twig->render('philosophy', ['element' => ['fieldConfig' => ['elements' => [
                'content' => ['value' => $value],
            ]]]]);
            self::assertStringContainsString('cms-element-mgd-ai-philosophy', $output);
            self::assertStringNotContainsString('<script>', $output);
        }
    }
}
