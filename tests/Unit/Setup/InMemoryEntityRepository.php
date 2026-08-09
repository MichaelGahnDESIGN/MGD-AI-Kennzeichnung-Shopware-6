<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Setup;

use PHPUnit\Framework\MockObject\MockObject;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;

/**
 * Zustandsbehaftetes Verhalten für ein minimal gemocktes DAL-Repository.
 *
 * Shopware kennzeichnet EntityRepository als final und sein echter Konstruktor
 * verlangt den vollständigen Schreibstack. Deshalb wird nur die technische
 * Repository-Hülle gemockt; sämtliche für den Installer relevanten Ergebnisse
 * entstehen aus diesem kleinen In-Memory-Datenbestand und nicht aus starren
 * Aufruferwartungen.
 *
 * @internal Ausschließlich für Unit-Tests vorgesehen.
 */
final class InMemoryEntityRepository
{
    /** @var array<string, array<string, mixed>> */
    public array $rows = [];

    /** @var list<array<array<string, mixed|null>>> */
    public array $upsertPayloads = [];

    /** @var list<array<array<string, mixed|null>>> */
    public array $deletePayloads = [];

    /** @var list<Criteria> */
    public array $searchCriteria = [];

    /**
     * @param list<array<string, mixed>> $initialRows
     */
    public function __construct(array $initialRows = [], private readonly bool $persistWrites = true)
    {
        foreach ($initialRows as $row) {
            $id = $row['id'] ?? null;
            if (!is_string($id)) {
                throw new \InvalidArgumentException('Testdatensätze benötigen eine Zeichenketten-ID.');
            }

            $this->rows[$id] = $row;
        }
    }

    /**
     * Verknüpft den Datenbestand mit den drei tatsächlich verwendeten Methoden.
     *
     * @param EntityRepository<covariant EntityCollection<covariant Entity>>&MockObject $repository
     *
     * @return EntityRepository<covariant EntityCollection<covariant Entity>>
     */
    public function connect(EntityRepository&MockObject $repository): EntityRepository
    {
        $repository->method('upsert')->willReturnCallback($this->upsertCallback());
        $repository->method('searchIds')->willReturnCallback(
            fn (Criteria $criteria, Context $context): IdSearchResult => $this->searchIds($criteria, $context),
        );
        $repository->method('delete')->willReturnCallback($this->deleteCallback());

        return $repository;
    }

    /** @return \Closure(array<array<string, mixed|null>>, Context): EntityWrittenContainerEvent */
    private function upsertCallback(): \Closure
    {
        return fn (array $data, Context $context): EntityWrittenContainerEvent => $this->upsert($data, $context);
    }

    /** @return \Closure(array<array<string, mixed|null>>, Context): EntityWrittenContainerEvent */
    private function deleteCallback(): \Closure
    {
        return fn (array $ids, Context $context): EntityWrittenContainerEvent => $this->delete($ids, $context);
    }

    /** @param array<mixed> $data */
    private function upsert(array $data, Context $context): EntityWrittenContainerEvent
    {
        $data = $this->normalizeRows($data);
        $this->upsertPayloads[] = $data;

        if ($this->persistWrites) {
            foreach ($data as $row) {
                $id = $row['id'] ?? null;
                if (!is_string($id)) {
                    throw new \InvalidArgumentException('Upsert-Testdaten benötigen eine Zeichenketten-ID.');
                }

                $this->rows[$id] = array_replace($this->rows[$id] ?? [], $row);
            }
        }

        return EntityWrittenContainerEvent::createWithWrittenEvents([], $context, []);
    }

    private function searchIds(Criteria $criteria, Context $context): IdSearchResult
    {
        $this->searchCriteria[] = clone $criteria;
        $matchingIds = [];

        foreach ($this->rows as $id => $row) {
            if ($criteria->getIds() !== [] && !in_array($id, $criteria->getIds(), true)) {
                continue;
            }

            if ($this->matchesAllFilters($row, $criteria->getFilters())) {
                $matchingIds[] = $id;
            }
        }

        return IdSearchResult::fromIds($matchingIds, $criteria, $context);
    }

    /** @param array<mixed> $ids */
    private function delete(array $ids, Context $context): EntityWrittenContainerEvent
    {
        $ids = $this->normalizeRows($ids);
        $this->deletePayloads[] = $ids;

        foreach ($ids as $idPayload) {
            $id = $idPayload['id'] ?? null;
            if (is_string($id)) {
                unset($this->rows[$id]);
            }
        }

        return EntityWrittenContainerEvent::createWithDeletedEvents([], $context, []);
    }

    /**
     * Prüft die untypisierten Callback-Grenzen des PHPUnit-Mocks wie ein DAL-Payload.
     *
     * @param array<mixed> $rows
     *
     * @return array<array<string, mixed|null>>
     */
    private function normalizeRows(array $rows): array
    {
        $normalizedRows = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                throw new \InvalidArgumentException('Repository-Testdaten müssen aus Datensatz-Arrays bestehen.');
            }

            $normalizedRow = [];
            foreach ($row as $key => $value) {
                if (!is_string($key)) {
                    throw new \InvalidArgumentException('Repository-Testdaten benötigen Zeichenketten-Schlüssel.');
                }

                $normalizedRow[$key] = $value;
            }

            $normalizedRows[] = $normalizedRow;
        }

        return $normalizedRows;
    }

    /**
     * @param array<string, mixed> $row
     * @param array<array-key, object> $filters
     */
    private function matchesAllFilters(array $row, array $filters): bool
    {
        foreach ($filters as $filter) {
            if (!$filter instanceof EqualsFilter || ($row[$filter->getField()] ?? null) !== $filter->getValue()) {
                return false;
            }
        }

        return true;
    }
}
