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
    /** Installation prüft zuerst den Namen, schreibt das eigene Set und verifiziert dessen ID. */
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
        self::assertCount(3, $setRepository->searchCriteria);
        $this->assertOnlyOwnNameFilter($setRepository->searchCriteria[0]);
        $this->assertOnlyOwnIdLookup($setRepository->searchCriteria[1]);
        $this->assertOwnIdAndNameVerification($setRepository->searchCriteria[2]);

        self::assertSame(
            [[CustomFieldSetDefinitionFactory::createRelation(CustomFieldSetDefinitionFactory::setId())]],
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
        $this->expectExceptionMessage('konnte nach dem Speichern nicht über seine eigene ID verifiziert werden');

        (new CustomFieldSetInstaller(
            $this->repository($setRepository),
            $this->repository($relationRepository),
        ))->install(Context::createDefaultContext());
    }

    /** Eine fremde ID unter dem eigenen Namen blockiert vor jeder Mutation. */
    public function testInstallRejectsForeignIdWithOwnNameBeforeMutation(): void
    {
        $setRepository = new InMemoryEntityRepository([
            ['id' => Uuid::randomHex(), 'name' => CustomFieldSetDefinitionFactory::SET_NAME],
        ]);
        $relationRepository = new InMemoryEntityRepository();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('bereits von einem fremden Datensatz belegt');

        try {
            (new CustomFieldSetInstaller(
                $this->repository($setRepository),
                $this->repository($relationRepository),
            ))->install(Context::createDefaultContext());
        } finally {
            self::assertSame([], $setRepository->upsertPayloads);
            self::assertSame([], $relationRepository->upsertPayloads);
        }
    }

    /** Mehrere gleichnamige IDs blockieren ebenfalls vor jeder Mutation. */
    public function testInstallRejectsMultipleNameMatchesBeforeMutation(): void
    {
        $setRepository = new InMemoryEntityRepository([
            ['id' => Uuid::randomHex(), 'name' => CustomFieldSetDefinitionFactory::SET_NAME],
            ['id' => Uuid::randomHex(), 'name' => CustomFieldSetDefinitionFactory::SET_NAME],
        ]);
        $relationRepository = new InMemoryEntityRepository();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('mehrfach vorhanden');

        try {
            (new CustomFieldSetInstaller(
                $this->repository($setRepository),
                $this->repository($relationRepository),
            ))->install(Context::createDefaultContext());
        } finally {
            self::assertSame([], $setRepository->upsertPayloads);
            self::assertSame([], $relationRepository->upsertPayloads);
        }
    }

    /** Eine fremde Belegung der eigenen deterministischen ID blockiert ebenfalls vor dem Upsert. */
    public function testInstallRejectsForeignNameStoredUnderOwnIdBeforeMutation(): void
    {
        $setRepository = new InMemoryEntityRepository([
            ['id' => CustomFieldSetDefinitionFactory::setId(), 'name' => 'fremdes_set'],
        ]);
        $relationRepository = new InMemoryEntityRepository();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('eigene Custom-Field-Set-ID');

        try {
            (new CustomFieldSetInstaller(
                $this->repository($setRepository),
                $this->repository($relationRepository),
            ))->install(Context::createDefaultContext());
        } finally {
            self::assertSame([], $setRepository->upsertPayloads);
            self::assertSame([], $relationRepository->upsertPayloads);
        }
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
        $this->assertOnlyOwnIdLookup($setRepository->searchCriteria[0]);
    }

    /** Ein fremder Name unter der deterministischen eigenen ID wird niemals gelöscht. */
    public function testRemoveRejectsForeignNameStoredUnderOwnId(): void
    {
        $ownId = CustomFieldSetDefinitionFactory::setId();
        $setRepository = new InMemoryEntityRepository([
            ['id' => $ownId, 'name' => 'fremdes_set'],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('unerwarteten Namen');

        try {
            (new CustomFieldSetInstaller(
                $this->repository($setRepository),
                $this->repository(new InMemoryEntityRepository()),
            ))->remove(Context::createDefaultContext());
        } finally {
            self::assertSame([], $setRepository->deletePayloads);
            self::assertArrayHasKey($ownId, $setRepository->rows);
        }
    }

    /** Die Deinstallation löscht nur die eigene ID und schont gleichnamige fremde IDs. */
    public function testRemoveDeletesOnlyOwnIdAndKeepsForeignNameCollision(): void
    {
        $ownId = CustomFieldSetDefinitionFactory::setId();
        $foreignId = Uuid::randomHex();
        $setRepository = new InMemoryEntityRepository([
            ['id' => $ownId, 'name' => CustomFieldSetDefinitionFactory::SET_NAME],
            ['id' => $foreignId, 'name' => CustomFieldSetDefinitionFactory::SET_NAME],
        ]);

        (new CustomFieldSetInstaller(
            $this->repository($setRepository),
            $this->repository(new InMemoryEntityRepository()),
        ))
            ->remove(Context::createDefaultContext());

        self::assertSame([[
            ['id' => $ownId],
        ]], $setRepository->deletePayloads);
        self::assertSame([
            $foreignId => ['id' => $foreignId, 'name' => CustomFieldSetDefinitionFactory::SET_NAME],
        ], $setRepository->rows);
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

    /** Verifiziert die eigene ID gemeinsam mit dem erwarteten Namen. */
    private function assertOwnIdAndNameVerification(Criteria $criteria): void
    {
        self::assertSame([CustomFieldSetDefinitionFactory::setId()], $criteria->getIds());
        self::assertCount(1, $criteria->getFilters());
        $filter = $criteria->getFilters()[0];
        self::assertInstanceOf(EqualsFilter::class, $filter);
        self::assertSame('name', $filter->getField());
        self::assertSame(CustomFieldSetDefinitionFactory::SET_NAME, $filter->getValue());
    }

    /** Verifiziert, dass remove zunächst ausschließlich die eigene ID betrachtet. */
    private function assertOnlyOwnIdLookup(Criteria $criteria): void
    {
        self::assertSame([CustomFieldSetDefinitionFactory::setId()], $criteria->getIds());
        self::assertSame([], $criteria->getFilters());
    }

    /** @return EntityRepository<covariant EntityCollection<covariant Entity>> */
    private function repository(InMemoryEntityRepository $state): EntityRepository
    {
        return $state->connect($this->createMock(EntityRepository::class));
    }
}
