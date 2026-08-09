<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Setup;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * Registriert und entfernt ausschließlich die plugin-eigenen Medienfelder.
 *
 * Der Dienst verwendet nur Shopwares DAL-Repositories. Dadurch greifen die
 * üblichen Validierungen, Ereignisse und Transaktionsmechanismen; direkte
 * Datenbankzugriffe oder Nebenwirkungen an Medien und CMS-Inhalten entfallen.
 */
final readonly class CustomFieldSetInstaller
{
    /**
     * @param EntityRepository<covariant EntityCollection<covariant Entity>> $customFieldSetRepository Repository der Custom-Field-Sets.
     * @param EntityRepository<covariant EntityCollection<covariant Entity>> $customFieldSetRelationRepository Repository ihrer Entitätszuordnungen.
     */
    public function __construct(
        private EntityRepository $customFieldSetRepository,
        private EntityRepository $customFieldSetRelationRepository,
    ) {
    }

    /**
     * Legt Definition und Medienrelation wiederholbar an oder aktualisiert sie.
     *
     * Nach dem Upsert wird die tatsächliche DAL-ID bewusst über den festen
     * Setnamen gelesen. Nur ein eindeutiger, gültiger Treffer darf anschließend
     * für eine Relation verwendet werden.
     */
    public function install(Context $context): void
    {
        $existingIds = $this->findIdsByName($context);
        if (count($existingIds) > 1) {
            throw new \RuntimeException(
                'Der technische Name des Custom-Field-Sets der KI-Bildkennzeichnung ist mehrfach vorhanden.'
            );
        }

        $ownSetId = CustomFieldSetDefinitionFactory::setId();
        if ($existingIds !== [] && $existingIds[0] !== $ownSetId) {
            throw new \RuntimeException(
                'Der technische Name des Custom-Field-Sets der KI-Bildkennzeichnung ist bereits von einem fremden Datensatz belegt.'
            );
        }

        $idsAtOwnPrimaryKey = $this->findOwnId($context);
        if ($idsAtOwnPrimaryKey !== [] && $existingIds !== [$ownSetId]) {
            throw new \RuntimeException(
                'Die eigene Custom-Field-Set-ID der KI-Bildkennzeichnung ist bereits von einem fremden Datensatz belegt.'
            );
        }

        $this->customFieldSetRepository->upsert(
            [CustomFieldSetDefinitionFactory::createSet()],
            $context,
        );

        $verifiedIds = $this->findOwnIdWithExpectedName($context);
        if ($verifiedIds !== [$ownSetId]) {
            throw new \RuntimeException(
                'Das Custom-Field-Set der KI-Bildkennzeichnung konnte nach dem Speichern nicht über seine eigene ID verifiziert werden.'
            );
        }

        $this->customFieldSetRelationRepository->upsert(
            [CustomFieldSetDefinitionFactory::createRelation($ownSetId)],
            $context,
        );
    }

    /**
     * Entfernt ausschließlich Sets mit dem festen plugin-eigenen Namen.
     *
     * Shopwares Cascade-Relation entfernt die zugehörigen Felddefinitionen und
     * Medienrelationen. Medien selbst, deren übrige Custom Fields sowie fremde
     * Sets bleiben unangetastet. Ein fehlendes Set ist ein sicherer No-op.
     */
    public function remove(Context $context): void
    {
        $ownSetId = CustomFieldSetDefinitionFactory::setId();
        $idsAtOwnPrimaryKey = $this->findOwnId($context);
        if ($idsAtOwnPrimaryKey === []) {
            return;
        }

        if ($idsAtOwnPrimaryKey !== [$ownSetId] || $this->findOwnIdWithExpectedName($context) !== [$ownSetId]) {
            throw new \RuntimeException(
                'Die eigene Custom-Field-Set-ID der KI-Bildkennzeichnung ist mit einem unerwarteten Namen belegt.'
            );
        }

        $this->customFieldSetRepository->delete([['id' => $ownSetId]], $context);
    }

    /**
     * Sucht vor der Mutation nach allen Belegungen des technischen Setnamens.
     *
     * @return list<string>
     */
    private function findIdsByName(Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', CustomFieldSetDefinitionFactory::SET_NAME));

        return $this->normalizeIds(
            $this->customFieldSetRepository->searchIds($criteria, $context)->getIds(),
        );
    }

    /** @return list<string> */
    private function findOwnId(Context $context): array
    {
        $criteria = new Criteria([CustomFieldSetDefinitionFactory::setId()]);

        return $this->normalizeIds(
            $this->customFieldSetRepository->searchIds($criteria, $context)->getIds(),
        );
    }

    /** @return list<string> */
    private function findOwnIdWithExpectedName(Context $context): array
    {
        $criteria = new Criteria([CustomFieldSetDefinitionFactory::setId()]);
        $criteria->addFilter(new EqualsFilter('name', CustomFieldSetDefinitionFactory::SET_NAME));

        return $this->normalizeIds(
            $this->customFieldSetRepository->searchIds($criteria, $context)->getIds(),
        );
    }

    /**
     * Verwirft unerwartete zusammengesetzte oder ungültige Primärschlüssel.
     *
     * @param list<string|array<string, string>> $ids
     *
     * @return list<string>
     */
    private function normalizeIds(array $ids): array
    {
        $normalizedIds = [];
        foreach ($ids as $id) {
            if (!is_string($id) || !Uuid::isValid($id)) {
                throw new \RuntimeException(
                    'Das Custom-Field-Set der KI-Bildkennzeichnung besitzt keine gültige Shopware-ID.'
                );
            }

            $normalizedIds[] = $id;
        }

        return $normalizedIds;
    }
}
