<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Administration;

use MGDAIImageLabels\Administration\Controller\PreparePhilosophyPageController;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\PlatformRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/** Hält Methode, Scope und ACL der einzigen schreibenden Admin-Aktion fest. */
final class PreparePhilosophyPageControllerContractTest extends TestCase
{
    public function testControllerAndActionUseApiScopePostAndSystemConfigurationAcl(): void
    {
        $controller = new \ReflectionClass(PreparePhilosophyPageController::class);
        $classRoute = $controller->getAttributes(Route::class)[0]->newInstance();
        self::assertSame(['api'], $classRoute->getDefaults()[PlatformRequest::ATTRIBUTE_ROUTE_SCOPE]);

        $method = $controller->getMethod('__invoke');
        $route = $method->getAttributes(Route::class)[0]->newInstance();
        self::assertSame([Request::METHOD_POST], $route->getMethods());
        self::assertSame([
            'system_config:update',
            'cms_page:create',
            'cms_section:create',
            'cms_block:create',
            'cms_slot:create',
        ], $route->getDefaults()[PlatformRequest::ATTRIBUTE_ACL]);
        $path = $route->getPath();
        self::assertIsString($path);
        self::assertStringStartsWith('/api/_action/', $path);
        self::assertSame([Context::class], array_map(
            static fn (\ReflectionParameter $parameter): string => (string) $parameter->getType(),
            $method->getParameters(),
        ));
    }
}
