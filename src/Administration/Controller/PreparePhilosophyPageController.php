<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Administration\Controller;

use MGDAIImageLabels\Cms\Philosophy\PhilosophyPageCreator;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\ApiRouteScope;
use Shopware\Core\PlatformRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/** Authentifizierte Admin-API-Grenze für die ausdrücklich ausgelöste Erstellung. */
#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [ApiRouteScope::ID]])]
final class PreparePhilosophyPageController extends AbstractController
{
    public function __construct(private readonly PhilosophyPageCreator $creator)
    {
    }

    #[Route(
        path: '/api/_action/mgd-ai-image-labels/philosophy-page',
        name: 'api.action.mgd_ai_image_labels.prepare_philosophy_page',
        defaults: [PlatformRequest::ATTRIBUTE_ACL => [
            'system_config:update',
            'cms_page:create',
            'cms_section:create',
            'cms_block:create',
            'cms_slot:create',
        ]],
        methods: [Request::METHOD_POST],
    )]
    public function __invoke(Context $context): JsonResponse
    {
        $result = $this->creator->prepare($context);

        return new JsonResponse([
            'created' => $result->created,
            'cmsPageId' => $result->cmsPageId,
        ]);
    }
}
