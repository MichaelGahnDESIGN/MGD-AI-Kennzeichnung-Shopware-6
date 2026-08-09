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
        $this->customFieldSetRepository->upsert(
            [CustomFieldSetDefinitionFactory::createSet()],
            $context,
        );

        $setIds = $this->findOwnSetIds($context);
        if (count($setIds) !== 1) {
            throw new \RuntimeException(
                'Das Custom-Field-Set der KI-Bildkennzeichnung konnte nach dem Speichern nicht eindeutig gefunden werden.'
            );
        }

        $this->customFieldSetRelationRepository->upsert(
            [CustomFieldSetDefinitionFactory::createRelation($setIds[0])],
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
        $setIds = $this->findOwnSetIds($context);
        if ($setIds === []) {
            return;
        }

        $deletePayload = array_map(
            static fn (string $setId): array => ['id' => $setId],
            $setIds,
        );

        $this->customFieldSetRepository->delete($deletePayload, $context);
    }

    /**
     * Sucht ausschließlich per DAL-Kriterium nach dem technischen Setnamen.
     *
     * @return list<string>
     */
    private function findOwnSetIds(Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', CustomFieldSetDefinitionFactory::SET_NAME));

        $ids = $this->customFieldSetRepository->searchIds($criteria, $context)->getIds();
        foreach ($ids as $id) {
            if (!Uuid::isValid($id)) {
                throw new \RuntimeException(
                    'Das Custom-Field-Set der KI-Bildkennzeichnung besitzt keine gültige Shopware-ID.'
                );
            }
        }

        /** @var list<string> $ids */
        return $ids;
    }
}
