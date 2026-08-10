<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Cms;

use Composer\InstalledVersions;
use MGDAIImageLabels\Cms\BackgroundImage\BackgroundImageCmsElementResolver;
use MGDAIImageLabels\Cms\BackgroundImage\BackgroundImagePresentationNormalizer;
use MGDAIImageLabels\Cms\BackgroundImage\CmsElementMediaStruct;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\FieldConfig;
use Shopware\Core\Content\Cms\DataResolver\FieldConfigCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Media\MediaCollection;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\MediaType\DocumentType;
use Shopware\Core\Content\Media\MediaType\ImageType;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * Prüft den CMS-Resolver vollständig ohne Datenbank oder Shopware-Kernel.
 */
final class BackgroundImageCmsElementResolverTest extends TestCase
{
    private const SLOT_ID = '0123456789abcdef0123456789abcdef';

    private const MEDIA_ID = 'abcdefabcdefabcdefabcdefabcdefab';

    public function testExposesDedicatedElementType(): void
    {
        self::assertSame('mgd-ai-background-image', $this->resolver()->getType());
    }

    public function testCollectsOnlyTheStaticValidMediaIdWithStableKey(): void
    {
        $slot = $this->slot(self::MEDIA_ID);

        $collection = $this->resolver()->collect($slot, $this->context());

        self::assertNotNull($collection);
        $all = $collection->all();
        $key = 'mgd_ai_background_media_' . self::SLOT_ID . '_' . self::MEDIA_ID;
        self::assertSame([MediaDefinition::class], array_keys($all));
        self::assertSame([$key], array_keys($all[MediaDefinition::class]));
        self::assertSame([self::MEDIA_ID], $all[MediaDefinition::class][$key]->getIds());
    }

    /** @param array<mixed>|bool|float|int|string|null $value */
    #[DataProvider('unsafeMediaValues')]
    public function testDoesNotCollectForMissingOrUnsafeMediaValues(
        array|bool|float|int|string|null $value,
        string $source,
    ): void {
        $slot = $this->slot($value, $source);

        self::assertNull($this->resolver()->collect($slot, $this->context()));
    }

    /** @return iterable<string, array{array<mixed>|bool|float|int|string|null, string}> */
    public static function unsafeMediaValues(): iterable
    {
        yield 'leer' => ['', FieldConfig::SOURCE_STATIC];
        yield 'null' => [null, FieldConfig::SOURCE_STATIC];
        yield 'keine UUID' => ['https://example.invalid/image.jpg', FieldConfig::SOURCE_STATIC];
        yield 'Array' => [[self::MEDIA_ID], FieldConfig::SOURCE_STATIC];
        yield 'Mapping ist bewusst ausgeschlossen' => [self::MEDIA_ID, FieldConfig::SOURCE_MAPPED];
        yield 'Default ist bewusst ausgeschlossen' => [self::MEDIA_ID, FieldConfig::SOURCE_DEFAULT];
    }

    public function testEnrichesExactlyOneTypedStructWithTheMatchingMedia(): void
    {
        $slot = $this->slot(self::MEDIA_ID);
        $media = new MediaEntity();
        $media->setId(self::MEDIA_ID);
        $media->setMimeType('image/png');
        $media->setMediaType(new ImageType());
        $result = new ElementDataCollection();
        $result->add(
            'mgd_ai_background_media_' . self::SLOT_ID . '_' . self::MEDIA_ID,
            new EntitySearchResult(
                MediaDefinition::ENTITY_NAME,
                1,
                new MediaCollection([$media]),
                null,
                new Criteria([self::MEDIA_ID]),
                Context::createDefaultContext(),
            ),
        );

        $this->resolver()->enrich($slot, $this->context(), $result);

        self::assertInstanceOf(CmsElementMediaStruct::class, $slot->getData());
        self::assertSame(self::MEDIA_ID, $slot->getData()->mediaId);
        self::assertSame($media, $slot->getData()->media);
        self::assertSame('mgd-ai-background-image--height-320', $slot->getData()->presentation->heightClass);
    }

    public function testEnrichRejectsPdfAndContradictingMediaType(): void
    {
        foreach ([
            ['application/pdf', new DocumentType()],
            ['application/pdf', new ImageType()],
            ['image/png', new DocumentType()],
            ['image/png', null],
        ] as [$mimeType, $mediaType]) {
            $slot = $this->slot(self::MEDIA_ID);
            $media = new MediaEntity();
            $media->setId(self::MEDIA_ID);
            $media->setMimeType($mimeType);
            if ($mediaType !== null) {
                $media->setMediaType($mediaType);
            }
            $result = new ElementDataCollection();
            $result->add(
                'mgd_ai_background_media_' . self::SLOT_ID . '_' . self::MEDIA_ID,
                new EntitySearchResult(
                    MediaDefinition::ENTITY_NAME,
                    1,
                    new MediaCollection([$media]),
                    null,
                    new Criteria([self::MEDIA_ID]),
                    Context::createDefaultContext(),
                ),
            );

            $this->resolver()->enrich($slot, $this->context(), $result);

            self::assertNull($slot->getData());
        }
    }

    public function testEnrichRemainsNullSafeWhenSearchResultOrMediaIsMissing(): void
    {
        $resolver = $this->resolver();

        foreach ([$this->slot(''), $this->slot(self::MEDIA_ID)] as $slot) {
            $resolver->enrich($slot, $this->context(), new ElementDataCollection());
            self::assertNull($slot->getData());
        }
    }

    public function testServiceUsesTheStableShopwareResolverTag(): void
    {
        $xml = file_get_contents(__DIR__ . '/../../../src/Resources/config/services.xml');
        self::assertIsString($xml);
        self::assertStringContainsString(BackgroundImageCmsElementResolver::class, $xml);
        self::assertStringContainsString(BackgroundImagePresentationNormalizer::class, $xml);
        self::assertStringContainsString(
            '<argument type="service" id="MGDAIImageLabels\Cms\BackgroundImage\BackgroundImagePresentationNormalizer"/>',
            $xml,
        );
        self::assertStringContainsString('<tag name="shopware.cms.data_resolver"/>', $xml);
    }

    public function testInstalledShopwareApiExposesTheVerifiedResolverContract(): void
    {
        $version = InstalledVersions::getPrettyVersion('shopware/core');
        self::assertIsString($version);
        self::assertMatchesRegularExpression('/^v?6\.(?:6\.10|7)\./', $version);

        $reflection = new \ReflectionClass(\Shopware\Core\Content\Cms\DataResolver\Element\CmsElementResolverInterface::class);
        $sourceFile = $reflection->getFileName();
        self::assertIsString($sourceFile);
        $source = file_get_contents($sourceFile);
        self::assertIsString($source);
        self::assertStringContainsString('public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection;', $source);
        self::assertStringContainsString('public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void;', $source);
    }

    /** @param array<mixed>|bool|float|int|string|null $mediaValue */
    private function slot(array|bool|float|int|string|null $mediaValue, string $source = FieldConfig::SOURCE_STATIC): CmsSlotEntity
    {
        $slot = new CmsSlotEntity();
        $slot->setId(self::SLOT_ID);
        $slot->setFieldConfig(new FieldConfigCollection([
            new FieldConfig('media', $source, $mediaValue),
        ]));

        return $slot;
    }

    private function context(): ResolverContext
    {
        return new ResolverContext($this->createStub(SalesChannelContext::class), new Request());
    }

    private function resolver(): BackgroundImageCmsElementResolver
    {
        return new BackgroundImageCmsElementResolver(new BackgroundImagePresentationNormalizer());
    }
}
