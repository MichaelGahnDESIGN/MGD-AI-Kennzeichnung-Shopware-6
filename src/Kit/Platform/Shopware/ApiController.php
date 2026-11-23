<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Kit\Platform\Shopware;

use Symfony\Component\HttpFoundation\{JsonResponse, Request};
use Symfony\Component\Routing\Attribute\Route;

// Admin-API unter /api/_action/mgd-kit/mgd-ai-kennzeichnung-shopware/... - nur mit Admin-Login (Bearer), _acl prüft Shopware
// Logik steckt im ApiHandler, hier nur Symfony-Kleber
#[Route(defaults: ['_routeScope' => ['api']])]
final class ApiController
{
    public const PREFIX = '/api/_action/mgd-kit/mgd-ai-kennzeichnung-shopware';

    public function __construct(private readonly ShopwareKit $kit)
    {
    }

    #[Route(path: self::PREFIX . '/status', name: 'api.action.mgd_kit.mgd_ai_kennzeichnung_shopware.status', defaults: ['_acl' => ['system_config:read']], methods: ['GET'])]
    public function status(Request $request): JsonResponse
    {
        return self::json($this->kit->handler()->status(self::context($request), gmdate('Y-m-d')));
    }

    #[Route(path: self::PREFIX . '/recommendations', name: 'api.action.mgd_kit.mgd_ai_kennzeichnung_shopware.recommendations', defaults: ['_acl' => ['system_config:read']], methods: ['GET'])]
    public function recommendations(Request $request): JsonResponse
    {
        return self::json($this->kit->handler()->recommendations(self::context($request)));
    }

    // Bild nur mit Admin-Login, Name = 32 hex .webp
    #[Route(path: self::PREFIX . '/media/{name}', name: 'api.action.mgd_kit.mgd_ai_kennzeichnung_shopware.media', requirements: ['name' => '[a-f0-9]{32}\\.webp'], defaults: ['_acl' => ['system_config:read']], methods: ['GET'])]
    public function media(Request $request, string $name): JsonResponse
    {
        return self::json($this->kit->handler()->media(self::context($request), $name));
    }

    #[Route(path: self::PREFIX . '/update', name: 'api.action.mgd_kit.mgd_ai_kennzeichnung_shopware.update', defaults: ['_acl' => ['system.plugin_maintain', 'system.plugin_upload']], methods: ['POST'])]
    public function update(Request $request): JsonResponse
    {
        return self::json($this->kit->handler()->update(self::context($request)));
    }

    #[Route(path: self::PREFIX . '/update/finish', name: 'api.action.mgd_kit.mgd_ai_kennzeichnung_shopware.update_finish', defaults: ['_acl' => ['system.plugin_maintain', 'system.plugin_upload']], methods: ['POST'])]
    public function finish(Request $request): JsonResponse
    {
        return self::json($this->kit->handler()->finish(self::context($request)));
    }

    #[Route(path: self::PREFIX . '/install-report', name: 'api.action.mgd_kit.mgd_ai_kennzeichnung_shopware.install_report', defaults: ['_acl' => ['system_config:update']], methods: ['POST'])]
    public function installReport(Request $request): JsonResponse
    {
        return self::json($this->kit->handler()->installReport(self::context($request), self::body($request)['enabled'] ?? null));
    }

    #[Route(path: self::PREFIX . '/license', name: 'api.action.mgd_kit.mgd_ai_kennzeichnung_shopware.license', defaults: ['_acl' => ['system_config:update']], methods: ['POST'])]
    public function license(Request $request): JsonResponse
    {
        $b = self::body($request);

        return self::json($this->kit->handler()->license(self::context($request), $b['action'] ?? null, $b['token'] ?? null));
    }

    // Shopware legt den Context als Request-Attribut ab (PlatformRequest::ATTRIBUTE_CONTEXT_OBJECT)
    private static function context(Request $request): object
    {
        $c = $request->attributes->get('sw-context');

        return is_object($c) ? $c : new \stdClass();
    }

    private static function body(Request $request): array
    {
        $d = json_decode((string) $request->getContent(), true, 4);

        return is_array($d) ? $d : [];
    }

    /** @param array{0:int,1:array<string,mixed>} $r */
    private static function json(array $r): JsonResponse
    {
        return JsonResponse::fromJsonString(ApiHandler::encode($r[1]), $r[0], ['Cache-Control' => 'no-store']);
    }
}
