<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Administration;

use MGDAIImageLabels\Administration\Controller\PreparePhilosophyPageController;
use MGDAIImageLabels\Cms\Philosophy\PhilosophyPageCreator;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Cms\CmsPageCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\PlatformRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request};
use Symfony\Component\Routing\Attribute\Route;

/** Hält Methode, Scope und ACL der einzigen schreibenden Admin-Aktion fest. */
final class PreparePhilosophyPageControllerContractTest extends TestCase
{
    public function testActionWorksAsPlainControllerWithoutAServiceContainer(): void
    {
        /** @var EntityRepository<CmsPageCollection> $repository */
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('searchIds')->willReturnCallback(
            static fn (Criteria $criteria, Context $context): IdSearchResult => IdSearchResult::fromIds(
                [PhilosophyPageCreator::pageId()],
                $criteria,
                $context,
            ),
        );

        $controllerReflection = new \ReflectionClass(PreparePhilosophyPageController::class);
        self::assertFalse($controllerReflection->isSubclassOf(AbstractController::class));
        self::assertTrue($controllerReflection->isReadOnly());

        $controller = new PreparePhilosophyPageController(new PhilosophyPageCreator($repository));
        $response = $controller(Context::createDefaultContext());

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(
            ['created' => false, 'cmsPageId' => PhilosophyPageCreator::pageId()],
            json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR),
        );
    }

    public function testControllerAndActionUseApiScopePostAndSystemConfigurationAcl(): void
    {
        $controller = new \ReflectionClass(PreparePhilosophyPageController::class);
        $classRoute = $controller->getAttributes(Route::class)[0]->newInstance();
        self::assertSame(['api'], $classRoute->defaults[PlatformRequest::ATTRIBUTE_ROUTE_SCOPE]);

        $method = $controller->getMethod('__invoke');
        $route = $method->getAttributes(Route::class)[0]->newInstance();
        self::assertSame([Request::METHOD_POST], $route->methods);
        self::assertSame([
            'system_config:update',
            'cms_page:create',
            'cms_section:create',
            'cms_block:create',
            'cms_slot:create',
        ], $route->defaults[PlatformRequest::ATTRIBUTE_ACL]);
        $path = $route->path;
        self::assertIsString($path);
        self::assertStringStartsWith('/api/_action/', $path);
        self::assertSame([Context::class], array_map(
            static fn (\ReflectionParameter $parameter): string => (string) $parameter->getType(),
            $method->getParameters(),
        ));
    }
}
