<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Setup;

use MGDAIImageLabels\Setup\CustomFieldSetDefinitionFactory;
use MGDAIImageLabels\Setup\CustomFieldSetInstaller;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * Prüft das tatsächliche Ablaufverhalten des Installers mit kleinen
 * In-Memory-Repositories.
 *
 * Die Testdoubles speichern und filtern Datensätze wie die für diesen Ablauf
 * benötigte Teilmenge des DAL. Dadurch werden Ergebniszustände, Idempotenz und
 * Löschgrenzen geprüft – nicht bloß vorab programmierte Mock-Aufrufzahlen.
 */
final class CustomFieldSetInstallerTest extends TestCase
{
    /** Installation schreibt das Set, sucht eng nach seinem Namen und bindet nur Medien an. */
    public function testInstallPersistsDefinitionFindsItsIdAndCreatesMediaRelation(): void
    {
        $setRepository = new InMemoryEntityRepository();
        $relationRepository = new InMemoryEntityRepository();
        $context = Context::createDefaultContext();

        (new CustomFieldSetInstaller(
            $this->repository($setRepository),
            $this->repository($relationRepository),
        ))->install($context);

        self::assertSame([[CustomFieldSetDefinitionFactory::createSet()]], $setRepository->upsertPayloads);
        self::assertCount(1, $setRepository->searchCriteria);
        $this->assertOnlyOwnNameFilter($setRepository->searchCriteria[0]);

        $setId = CustomFieldSetDefinitionFactory::createSet()['id'];
        self::assertSame(
            [[CustomFieldSetDefinitionFactory::createRelation($setId)]],
            $relationRepository->upsertPayloads,
        );
        self::assertSame('media', array_values($relationRepository->rows)[0]['entityName']);
    }

    /** Wiederholte Installation aktualisiert dieselben stabilen Datensätze ohne Dubletten. */
    public function testInstallIsIdempotent(): void
    {
        $setRepository = new InMemoryEntityRepository();
        $relationRepository = new InMemoryEntityRepository();
        $installer = new CustomFieldSetInstaller(
            $this->repository($setRepository),
            $this->repository($relationRepository),
        );
        $context = Context::createDefaultContext();

        $installer->install($context);
        $installer->install($context);

        self::assertCount(1, $setRepository->rows);
        self::assertCount(1, $relationRepository->rows);
        self::assertCount(2, $setRepository->upsertPayloads);
        self::assertCount(2, $relationRepository->upsertPayloads);
    }

    /** Ein unerwartet fehlender Set-Datensatz wird mit einer statischen Meldung abgebrochen. */
    public function testInstallFailsClearlyWhenUpsertCannotBeFound(): void
    {
        $setRepository = new InMemoryEntityRepository(persistWrites: false);
        $relationRepository = new InMemoryEntityRepository();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('konnte nach dem Speichern nicht eindeutig gefunden werden');

        (new CustomFieldSetInstaller(
            $this->repository($setRepository),
            $this->repository($relationRepository),
        ))->install(Context::createDefaultContext());
    }

    /** Mehrere gleichnamige Sets sind kein sicher auflösbarer Zustand. */
    public function testInstallFailsClearlyWhenSetNameIsAmbiguous(): void
    {
        $setRepository = new InMemoryEntityRepository([
            ['id' => Uuid::randomHex(), 'name' => CustomFieldSetDefinitionFactory::SET_NAME],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('konnte nach dem Speichern nicht eindeutig gefunden werden');

        (new CustomFieldSetInstaller(
            $this->repository($setRepository),
            $this->repository(new InMemoryEntityRepository()),
        ))
            ->install(Context::createDefaultContext());
    }

    /** Ohne plugin-eigenes Set führt die Deinstallation keine Löschung aus. */
    public function testRemoveDoesNothingWhenOwnSetDoesNotExist(): void
    {
        $foreignId = Uuid::randomHex();
        $setRepository = new InMemoryEntityRepository([
            ['id' => $foreignId, 'name' => 'fremdes_set'],
        ]);

        (new CustomFieldSetInstaller(
            $this->repository($setRepository),
            $this->repository(new InMemoryEntityRepository()),
        ))
            ->remove(Context::createDefaultContext());

        self::assertSame([], $setRepository->deletePayloads);
        self::assertArrayHasKey($foreignId, $setRepository->rows);
        self::assertCount(1, $setRepository->searchCriteria);
        $this->assertOnlyOwnNameFilter($setRepository->searchCriteria[0]);
    }

    /** Die Deinstallation löscht alle und ausschließlich die eigenen gefundenen Set-IDs. */
    public function testRemoveDeletesOnlyOwnSetIdsAndKeepsForeignSets(): void
    {
        $ownIdA = Uuid::randomHex();
        $ownIdB = Uuid::randomHex();
        $foreignId = Uuid::randomHex();
        $setRepository = new InMemoryEntityRepository([
            ['id' => $ownIdA, 'name' => CustomFieldSetDefinitionFactory::SET_NAME],
            ['id' => $ownIdB, 'name' => CustomFieldSetDefinitionFactory::SET_NAME],
            ['id' => $foreignId, 'name' => 'fremdes_set'],
        ]);

        (new CustomFieldSetInstaller(
            $this->repository($setRepository),
            $this->repository(new InMemoryEntityRepository()),
        ))
            ->remove(Context::createDefaultContext());

        self::assertSame([[
            ['id' => $ownIdA],
            ['id' => $ownIdB],
        ]], $setRepository->deletePayloads);
        self::assertSame([$foreignId => ['id' => $foreignId, 'name' => 'fremdes_set']], $setRepository->rows);
    }

    /** Nur ein EqualsFilter auf den festen technischen Setnamen ist zulässig. */
    private function assertOnlyOwnNameFilter(Criteria $criteria): void
    {
        self::assertSame([], $criteria->getIds());
        self::assertCount(1, $criteria->getFilters());
        $filter = $criteria->getFilters()[0];
        self::assertInstanceOf(EqualsFilter::class, $filter);
        self::assertSame('name', $filter->getField());
        self::assertSame(CustomFieldSetDefinitionFactory::SET_NAME, $filter->getValue());
    }

    /** @return EntityRepository<covariant EntityCollection<covariant Entity>> */
    private function repository(InMemoryEntityRepository $state): EntityRepository
    {
        return $state->connect($this->createMock(EntityRepository::class));
    }
}
