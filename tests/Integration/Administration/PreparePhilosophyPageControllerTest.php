<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Integration\Administration;

use MGDAIImageLabels\Administration\Controller\PreparePhilosophyPageController;
use MGDAIImageLabels\Tests\Integration\Setup\ShopwareIntegrationTestBootstrap;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\PlatformRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/** Prüft die im echten Kernel registrierte, authentifizierte Admin-API-Grenze. */
#[Group('integration')]
final class PreparePhilosophyPageControllerTest extends TestCase
{
    use IntegrationTestBehaviour;

    public static function setUpBeforeClass(): void
    {
        ShopwareIntegrationTestBootstrap::boot(dirname(__DIR__, 3) . '/composer.json');
    }

    public function testRouteIsPostOnlyApiScopedAndAclProtected(): void
    {
        $router = self::getContainer()->get('router');
        self::assertInstanceOf(RouterInterface::class, $router);
        $route = $router->getRouteCollection()->get('api.action.mgd_ai_image_labels.prepare_philosophy_page');
        self::assertNotNull($route);
        self::assertSame([Request::METHOD_POST], $route->getMethods());
        self::assertSame(['api'], $route->getDefault(PlatformRequest::ATTRIBUTE_ROUTE_SCOPE));
        self::assertSame([
            'system_config:update',
            'cms_page:create',
            'cms_section:create',
            'cms_block:create',
            'cms_slot:create',
        ], $route->getDefault(PlatformRequest::ATTRIBUTE_ACL));
    }

    public function testResponseContainsOnlyCreatedAndValidCmsPageId(): void
    {
        $controller = self::getContainer()->get(PreparePhilosophyPageController::class);
        self::assertInstanceOf(PreparePhilosophyPageController::class, $controller);

        $response = $controller(Context::createDefaultContext());
        $decoded = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        self::assertIsArray($decoded);
        self::assertSame(['created', 'cmsPageId'], array_keys($decoded));
        self::assertIsBool($decoded['created']);
        self::assertIsString($decoded['cmsPageId']);
        self::assertTrue(Uuid::isValid($decoded['cmsPageId']));
    }
}
