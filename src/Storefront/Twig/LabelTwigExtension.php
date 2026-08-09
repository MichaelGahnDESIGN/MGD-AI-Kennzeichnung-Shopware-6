<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Storefront\Twig;

use MGDAIImageLabels\Storefront\Label\LabelView;
use MGDAIImageLabels\Storefront\Label\LabelViewResolver;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\PlatformRequest;
use Shopware\Core\SalesChannelRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Stellt dem Storefront genau eine sichere Funktion für KI-Bildlabels bereit.
 *
 * Die Erweiterung liest keine globalen Request-Variablen. Ein Request gilt nur
 * dann als Storefront-Request, wenn sowohl Shopwares SalesChannelContext als
 * auch die Locale im dafür vorgesehenen Attribut den erwarteten Typ besitzen.
 */
final class LabelTwigExtension extends AbstractExtension
{
    public function __construct(
        private LabelViewResolver $resolver,
        private RequestStack $requestStack,
    ) {}

    /** @return list<TwigFunction> */
    public function getFunctions(): array
    {
        return [new TwigFunction('mgd_ai_image_label', $this->resolve(...))];
    }

    /**
     * Erzeugt das geprüfte Viewmodell für ein Shopware-Medium.
     *
     * Ohne Medium entsteht die kanonische unsichtbare Darstellung. Ohne einen
     * vollständig typgeprüften Storefront-Request gelten globale Einstellungen
     * und Englisch; manipulierte Request-Attribute können so nichts einschleusen.
     */
    public function resolve(?MediaEntity $media): LabelView
    {
        [$salesChannelId, $locale] = $this->storefrontContext();

        return $this->resolver->resolve(
            $media?->getCustomFields() ?? [],
            $salesChannelId,
            $locale,
        );
    }

    /**
     * @return array{?string, string} Verkaufskanal-ID und sichere Ausgangs-Locale.
     */
    private function storefrontContext(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return [null, 'en-GB'];
        }

        $context = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
        $locale = $request->attributes->get(SalesChannelRequest::ATTRIBUTE_DOMAIN_LOCALE);

        if (!$context instanceof SalesChannelContext || !is_string($locale) || trim($locale) === '') {
            return [null, 'en-GB'];
        }

        return [$context->getSalesChannelId(), $locale];
    }
}
