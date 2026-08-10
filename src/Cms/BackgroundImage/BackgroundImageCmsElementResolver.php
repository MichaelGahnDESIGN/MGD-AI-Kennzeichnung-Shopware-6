<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Cms\BackgroundImage;

use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\FieldConfig;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * Lädt für das CMS-Hintergrundbild ausschließlich ein statisch ausgewähltes
 * Shopware-Medium. Mappings, Standardpfade und freie URLs sind bewusst nicht
 * Teil dieses Elements und können deshalb keinen DAL-Zugriff auslösen.
 */
final class BackgroundImageCmsElementResolver extends AbstractCmsElementResolver
{
    private const TYPE = 'mgd-ai-background-image';

    public function getType(): string
    {
        return self::TYPE;
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        $mediaId = $this->mediaId($slot);
        if ($mediaId === null) {
            return null;
        }

        $collection = new CriteriaCollection();
        $collection->add($this->searchKey($slot, $mediaId), MediaDefinition::class, new Criteria([$mediaId]));

        return $collection;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $mediaId = $this->mediaId($slot);
        if ($mediaId === null) {
            return;
        }

        $searchResult = $result->get($this->searchKey($slot, $mediaId));
        if ($searchResult === null) {
            return;
        }

        $media = $searchResult->getEntities()->get($mediaId);
        if (!$media instanceof MediaEntity) {
            return;
        }

        $slot->setData(new CmsElementMediaStruct($mediaId, $media));
    }

    /**
     * Akzeptiert nur Shopwares statische Medienkonfiguration mit valider UUID.
     */
    private function mediaId(CmsSlotEntity $slot): ?string
    {
        $media = $slot->getFieldConfig()->get('media');
        if (!$media instanceof FieldConfig || !$media->isStatic()) {
            return null;
        }

        $value = $media->getValue();
        if (!is_string($value) || !Uuid::isValid($value)) {
            return null;
        }

        return $value;
    }

    /**
     * Element- und Medien-ID verhindern Kollisionen bei mehreren CMS-Slots.
     */
    private function searchKey(CmsSlotEntity $slot, string $mediaId): string
    {
        return 'mgd_ai_background_media_' . $slot->getUniqueIdentifier() . '_' . $mediaId;
    }
}
