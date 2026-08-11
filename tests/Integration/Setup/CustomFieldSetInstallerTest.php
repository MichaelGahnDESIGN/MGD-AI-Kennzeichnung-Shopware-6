<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Integration\Setup;

use Doctrine\DBAL\Connection;
use MGDAIImageLabels\MGDAIImageLabels;
use MGDAIImageLabels\Setup\CustomFieldSetDefinitionFactory;
use MGDAIImageLabels\Setup\CustomFieldSetInstaller;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetCollection;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSetRelation\CustomFieldSetRelationCollection;
use Shopware\Core\TestBootstrapper;
use Symfony\Component\DependencyInjection\Container;

/**
 * Prüft Installation und Entfernung gegen eine echte Shopware-DAL-Testinstanz.
 *
 * Task 13 führt zusätzlich den echten CLI-Installations-/Deinstallations-Smoke
 * aus. Dieser Test nutzt Shopwares Transaktionsverhalten und rollt alle während
 * eines Testfalls geschriebenen globalen DAL-Daten zuverlässig zurück.
 */
#[Group('integration')]
final class CustomFieldSetInstallerTest extends TestCase
{
    use IntegrationTestBehaviour;

    private const TASK_13_COMMAND = "MGD_SHOPWARE_INTEGRATION_TESTS=1 MGD_SHOPWARE_TEST_DATABASE_URL='mysql://.../mgd_shopware_test' php bin/phpunit custom/plugins/MGDAIImageLabels/tests/Integration/Setup/CustomFieldSetInstallerTest.php --fail-on-skipped";

    public static function setUpBeforeClass(): void
    {
        if (($_SERVER['MGD_SHOPWARE_INTEGRATION_TESTS'] ?? getenv('MGD_SHOPWARE_INTEGRATION_TESTS')) !== '1') {
            self::markTestSkipped(
                'Task 13: Ausführen mit „' . self::TASK_13_COMMAND . '“. Die separate MGD_SHOPWARE_TEST_DATABASE_URL muss auf eine isolierte MySQL-Testdatenbank zeigen.'
            );
        }

        $databaseUrl = $_SERVER['MGD_SHOPWARE_TEST_DATABASE_URL']
            ?? $_ENV['MGD_SHOPWARE_TEST_DATABASE_URL']
            ?? getenv('MGD_SHOPWARE_TEST_DATABASE_URL');
        $validatedDatabaseUrl = ShopwareTestDatabaseConfiguration::validate(
            is_string($databaseUrl) ? $databaseUrl : null,
        );

        (new TestBootstrapper())
            ->setPlatformEmbedded(false)
            ->setEnableCommercial(false)
            ->setLoadEnvFile(false)
            ->setDatabaseUrl($validatedDatabaseUrl)
            ->bootstrap();
    }

    /** Zwei Installationen bleiben eindeutig; Entfernung schont ein fremdes Set. */
    public function testInstallTwiceAndRemoveAgainstRealDal(): void
    {
        $context = Context::createDefaultContext();
        $setRepository = $this->customFieldSetRepository();
        $relationRepository = $this->customFieldSetRelationRepository();
        $installer = new CustomFieldSetInstaller($setRepository, $relationRepository);
        $foreignId = Uuid::randomHex();
        $foreignName = 'mgd_test_foreign_' . Uuid::randomHex();

        $setRepository->create([[
            'id' => $foreignId,
            'name' => $foreignName,
            'config' => ['label' => ['en-GB' => 'Foreign integration fixture']],
        ]], $context);

        $installer->install($context);
        $installer->install($context);

        $ownSetIds = $this->idsByField(
            $setRepository,
            'name',
            CustomFieldSetDefinitionFactory::SET_NAME,
            $context,
        );
        self::assertSame([CustomFieldSetDefinitionFactory::setId()], $ownSetIds);

        $relationCriteria = new Criteria();
        $relationCriteria->addFilter(new EqualsFilter('customFieldSetId', $ownSetIds[0]));
        $relationCriteria->addFilter(new EqualsFilter('entityName', 'media'));
        self::assertCount(1, $relationRepository->searchIds($relationCriteria, $context)->getIds());

        $installer->remove($context);

        self::assertSame([], $this->idsByField(
            $setRepository,
            'name',
            CustomFieldSetDefinitionFactory::SET_NAME,
            $context,
        ));
        self::assertSame([$foreignId], $this->idsByField($setRepository, 'name', $foreignName, $context));
    }

    /** Der Plugin-Lifecycle arbeitet auch ohne geladenen plugin-eigenen Dienst über Core-Repositories. */
    public function testPluginLifecycleUsesPublicCoreRepositoriesWithoutOwnService(): void
    {
        $context = Context::createDefaultContext();
        $setRepository = $this->customFieldSetRepository();
        $relationRepository = $this->customFieldSetRelationRepository();
        $container = new Container();
        $container->set('custom_field_set.repository', $setRepository);
        $container->set('custom_field_set_relation.repository', $relationRepository);
        // Der isolierte Container bildet alle öffentlichen Core-Abhängigkeiten
        // des aktuellen Lifecycles ab, aber bewusst keinen Plugin-Dienst.
        $container->set(Connection::class, self::getContainer()->get(Connection::class));
        $container->set(SystemConfigService::class, self::getContainer()->get(SystemConfigService::class));
        self::assertFalse($container->has(CustomFieldSetInstaller::class));

        $plugin = new MGDAIImageLabels(false, dirname(__DIR__, 3));
        $plugin->setContainer($container);
        $installContext = $this->createStub(InstallContext::class);
        $installContext->method('getContext')->willReturn($context);
        $uninstallContext = $this->createStub(UninstallContext::class);
        $uninstallContext->method('getContext')->willReturn($context);
        $uninstallContext->method('keepUserData')->willReturn(false);

        $plugin->install($installContext);
        self::assertSame(
            [CustomFieldSetDefinitionFactory::setId()],
            $this->idsByField($setRepository, 'name', CustomFieldSetDefinitionFactory::SET_NAME, $context),
        );

        $plugin->uninstall($uninstallContext);
        self::assertSame(
            [],
            $this->idsByField($setRepository, 'name', CustomFieldSetDefinitionFactory::SET_NAME, $context),
        );
    }

    /** @return EntityRepository<CustomFieldSetCollection> */
    private function customFieldSetRepository(): EntityRepository
    {
        /** @var EntityRepository<CustomFieldSetCollection> $repository */
        $repository = self::getContainer()->get('custom_field_set.repository');

        return $repository;
    }

    /** @return EntityRepository<CustomFieldSetRelationCollection> */
    private function customFieldSetRelationRepository(): EntityRepository
    {
        /** @var EntityRepository<CustomFieldSetRelationCollection> $repository */
        $repository = self::getContainer()->get('custom_field_set_relation.repository');

        return $repository;
    }

    /**
     * @template TCollection of EntityCollection<covariant Entity>
     *
     * @param EntityRepository<TCollection> $repository
     *
     * @return list<string>
     */
    private function idsByField(
        EntityRepository $repository,
        string $field,
        string $value,
        Context $context,
    ): array {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter($field, $value));

        $ids = $repository->searchIds($criteria, $context)->getIds();
        self::assertContainsOnly('string', $ids);

        /** @var list<string> $ids */
        return $ids;
    }
}
