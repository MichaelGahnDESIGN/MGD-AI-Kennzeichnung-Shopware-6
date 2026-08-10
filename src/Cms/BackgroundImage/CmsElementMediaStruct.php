<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Cms\BackgroundImage;

use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Framework\Struct\Struct;

/**
 * Enges CMS-Viewmodell für genau ein lokales Shopware-Medium.
 *
 * Freie URLs oder rohe Darstellungswerte gehören absichtlich nicht in diese
 * Struktur. Das Storefront-Template erhält nur das vom DAL geladene Medium und
 * die serverseitig normalisierte Darstellung.
 */
final class CmsElementMediaStruct extends Struct
{
    public function __construct(
        public readonly string $mediaId,
        public readonly MediaEntity $media,
        public readonly BackgroundImagePresentation $presentation,
    ) {}

    public function getApiAlias(): string
    {
        return 'mgd_ai_cms_element_media';
    }
}
