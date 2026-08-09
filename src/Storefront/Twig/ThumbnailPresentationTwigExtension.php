<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Storefront\Twig;

use MGDAIImageLabels\Storefront\Thumbnail\ThumbnailPresentation;
use MGDAIImageLabels\Storefront\Thumbnail\ThumbnailPresentationResolver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Stellt Twig ausschließlich die typisierte Thumbnail-Präsentation bereit.
 */
final class ThumbnailPresentationTwigExtension extends AbstractExtension
{
    public function __construct(private ThumbnailPresentationResolver $resolver) {}

    /** @return list<TwigFunction> */
    public function getFunctions(): array
    {
        return [new TwigFunction('mgd_ai_thumbnail_presentation', $this->resolve(...))];
    }

    /**
     * Alle Eingänge bleiben absichtlich mixed: Erst der Resolver bildet die
     * nicht vertrauenswürdige Twig-Welt auf das geschlossene DTO ab.
     */
    public function resolve(mixed $name, mixed $attributes = null, mixed $context = null): ThumbnailPresentation
    {
        return $this->resolver->resolve($name, $attributes, $context);
    }
}
