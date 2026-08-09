<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Integration\Setup;

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
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetCollection;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSetRelation\CustomFieldSetRelationCollection;
use Shopware\Core\TestBootstrapper;

/**
 * Prüft Installation und Entfernung gegen eine echte Shopware-DAL-Testinstanz.
 *
 * Der Test benötigt eine ausdrücklich freigegebene, isolierte Testdatenbank.
 * Task 13 richtet die gemeinsame Integrationsumgebung ein und führt ihn mit
 * MGD_SHOPWARE_INTEGRATION_TESTS=1 aus. Shopwares TestBootstrapper ergänzt bei
 * Bedarf das Suffix „_test“ und verhindert so einen stillen Produktionszugriff.
 */
#[Group('integration')]
final class CustomFieldSetInstallerTest extends TestCase
{
    use KernelTestBehaviour;

    private static bool $shopwareBootstrapped = false;

    protected function setUp(): void
    {
        if (($_SERVER['MGD_SHOPWARE_INTEGRATION_TESTS'] ?? getenv('MGD_SHOPWARE_INTEGRATION_TESTS')) !== '1') {
            self::markTestSkipped(
                'Task 13: Nur mit MGD_SHOPWARE_INTEGRATION_TESTS=1 und einer isolierten Shopware-Testdatenbank ausführen.'
            );
        }

        if (!self::$shopwareBootstrapped) {
            (new TestBootstrapper())
                ->setPlatformEmbedded(false)
                ->setEnableCommercial(false)
                ->bootstrap();

            self::$shopwareBootstrapped = true;
        }
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

        $this->removeOwnSets($setRepository, $context);
        $setRepository->create([[
            'id' => $foreignId,
            'name' => $foreignName,
            'config' => ['label' => ['en-GB' => 'Foreign integration fixture']],
        ]], $context);

        try {
            $installer->install($context);
            $installer->install($context);

            $ownSetIds = $this->idsByField(
                $setRepository,
                'name',
                CustomFieldSetDefinitionFactory::SET_NAME,
                $context,
            );
            self::assertCount(1, $ownSetIds);

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
        } finally {
            $this->removeOwnSets($setRepository, $context);
            $setRepository->delete([['id' => $foreignId]], $context);
        }
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
     * Entfernt ausschließlich eventuell verbliebene Datensätze des Test-Plugins.
     *
     * @param EntityRepository<covariant EntityCollection<covariant Entity>> $repository
     */
    private function removeOwnSets(EntityRepository $repository, Context $context): void
    {
        $ids = $this->idsByField(
            $repository,
            'name',
            CustomFieldSetDefinitionFactory::SET_NAME,
            $context,
        );
        if ($ids === []) {
            return;
        }

        $repository->delete(
            array_map(static fn (string $id): array => ['id' => $id], $ids),
            $context,
        );
    }

    /**
     * @param EntityRepository<covariant EntityCollection<covariant Entity>> $repository
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
