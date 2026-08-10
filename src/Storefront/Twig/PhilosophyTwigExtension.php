<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Storefront\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/** Begrenzt untypisierte CMS-Konfiguration vor dem HTML-Sanitizer auf Text. */
final class PhilosophyTwigExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [new TwigFunction('mgd_ai_philosophy_content', $this->normalize(...))];
    }

    public function normalize(mixed $content): string
    {
        return is_string($content) ? $content : '';
    }
}
