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
        $repository->method('upsert')->willReturnCallback(
            fn (array $data, Context $context): EntityWrittenContainerEvent => $this->upsert($data, $context),
        );
        $repository->method('searchIds')->willReturnCallback(
            fn (Criteria $criteria, Context $context): IdSearchResult => $this->searchIds($criteria, $context),
        );
        $repository->method('delete')->willReturnCallback(
            fn (array $ids, Context $context): EntityWrittenContainerEvent => $this->delete($ids, $context),
        );

        return $repository;
    }

    /** @param array<array<string, mixed|null>> $data */
    private function upsert(array $data, Context $context): EntityWrittenContainerEvent
    {
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
            if ($this->matchesAllFilters($row, $criteria->getFilters())) {
                $matchingIds[] = $id;
            }
        }

        return IdSearchResult::fromIds($matchingIds, $criteria, $context);
    }

    /** @param array<array<string, mixed|null>> $ids */
    private function delete(array $ids, Context $context): EntityWrittenContainerEvent
    {
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
