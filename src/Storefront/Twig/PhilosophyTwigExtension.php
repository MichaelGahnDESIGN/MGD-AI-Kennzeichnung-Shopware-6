<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Storefront\Twig;

use MGDAIImageLabels\Cms\Philosophy\PhilosophyDefaultContent;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/** Begrenzt untypisierte CMS-Konfiguration vor dem HTML-Sanitizer auf Text. */
final class PhilosophyTwigExtension extends AbstractExtension
{
    public function __construct(private readonly PhilosophyDefaultContent $defaultContent)
    {
    }

    public function getFunctions(): array
    {
        return [new TwigFunction('mgd_ai_philosophy_content', $this->normalize(...))];
    }

    public function normalize(mixed $content, mixed $locale = null): string
    {
        return $this->defaultContent->resolve($content, $locale);
    }
}
