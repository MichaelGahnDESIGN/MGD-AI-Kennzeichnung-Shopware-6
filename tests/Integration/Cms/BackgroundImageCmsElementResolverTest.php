<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Integration\Cms;

use MGDAIImageLabels\Cms\BackgroundImage\BackgroundImageCmsElementResolver;
use MGDAIImageLabels\Cms\BackgroundImage\CmsElementMediaStruct;
use MGDAIImageLabels\Tests\Integration\Setup\ShopwareTestDatabaseConfiguration;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\FieldConfig;
use Shopware\Core\Content\Cms\DataResolver\FieldConfigCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Media\MediaCollection;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Content\Media\MediaType\DocumentType;
use Shopware\Core\Content\Media\MediaType\ImageType;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\TestBootstrapper;
use Symfony\Component\HttpFoundation\Request;

/**
 * Prüft den registrierten CMS-Resolver gegen Shopwares echten Kernel und DAL.
 *
 * Der Test startet nur nach doppelter ausdrücklicher Freigabe: Flag und durch
 * den gemeinsamen Task-4-Validator bestätigte isolierte Testdatenbank. Shopware
 * lädt keine .env-Datei. Das Transaktionsverhalten rollt jedes Medium zurück.
 * Die Fixtures folgen dem FileSaver-Vertrag der offiziellen Shopware-Tags
 * 6.6.10.22 und 6.7.13.0: serialisierter Medientyp im Systemkontext.
 */
#[Group('integration')]
final class BackgroundImageCmsElementResolverTest extends TestCase
{
    use IntegrationTestBehaviour;

    private const TASK_13_COMMAND = "MGD_SHOPWARE_INTEGRATION_TESTS=1 MGD_SHOPWARE_TEST_DATABASE_URL='mysql://.../mgd_shopware_test' php bin/phpunit custom/plugins/MGDAIImageLabels/tests/Integration/Cms/BackgroundImageCmsElementResolverTest.php --fail-on-skipped";

    public static function setUpBeforeClass(): void
    {
        if (($_SERVER['MGD_SHOPWARE_INTEGRATION_TESTS'] ?? getenv('MGD_SHOPWARE_INTEGRATION_TESTS')) !== '1') {
            self::markTestSkipped(
                'Task 13: Ausführen mit „' . self::TASK_13_COMMAND . '“. Die separate MGD_SHOPWARE_TEST_DATABASE_URL muss auf eine isolierte MySQL-Testdatenbank zeigen.',
            );
        }

        $databaseUrl = $_SERVER['MGD_SHOPWARE_TEST_DATABASE_URL']
            ?? $_ENV['MGD_SHOPWARE_TEST_DATABASE_URL']
            ?? getenv('MGD_SHOPWARE_TEST_DATABASE_URL');
        $validatedDatabaseUrl = ShopwareTestDatabaseConfiguration::validate(
            is_string($databaseUrl) ? $databaseUrl : null,
        );

        (new TestBootstrapper())
            ->setPlatformEmbedded(false)
            ->setEnableCommercial(false)
            ->setLoadEnvFile(false)
            ->setDatabaseUrl($validatedDatabaseUrl)
            ->addCallingPlugin(dirname(__DIR__, 3) . '/composer.json')
            ->setForceInstallPlugins(true)
            ->bootstrap();
    }

    /**
     * Verifiziert Tag-Registrierung, Kriterien und Enrichment über echte
     * Container- und Repository-Objekte innerhalb einer Rollback-Transaktion.
     */
    public function testTaggedResolverLoadsAndEnrichesRealMediaThroughDal(): void
    {
        $resolver = self::getContainer()->get(BackgroundImageCmsElementResolver::class);
        self::assertInstanceOf(BackgroundImageCmsElementResolver::class, $resolver);

        $mediaId = Uuid::randomHex();
        $slotId = Uuid::randomHex();
        $context = $this->systemContext();
        $repository = $this->mediaRepository();
        $repository->create([[
            'id' => $mediaId,
            'fileName' => 'mgd-cms-resolver-' . Uuid::randomHex(),
            'fileExtension' => 'png',
            'mimeType' => 'image/png',
            'mediaTypeRaw' => serialize(new ImageType()),
        ]], $context);
        $slot = $this->slot($slotId, $mediaId);

        $collection = $resolver->collect($slot, $this->resolverContext());

        self::assertNotNull($collection);
        $all = $collection->all();
        $key = 'mgd_ai_background_media_' . $slotId . '_' . $mediaId;
        self::assertSame([MediaDefinition::class], array_keys($all));
        self::assertSame([$key], array_keys($all[MediaDefinition::class]));
        self::assertSame([$mediaId], $all[MediaDefinition::class][$key]->getIds());

        $result = new ElementDataCollection();
        $result->add($key, $repository->search($all[MediaDefinition::class][$key], $context));
        $resolver->enrich($slot, $this->resolverContext(), $result);

        self::assertInstanceOf(CmsElementMediaStruct::class, $slot->getData());
        self::assertSame($mediaId, $slot->getData()->mediaId);
        self::assertSame($mediaId, $slot->getData()->media->getId());
    }

    /** Leere und manipulierte Werte erzeugen keine CriteriaCollection und damit keinen DAL-Aufruf. */
    public function testEmptyAndInvalidMediaConfigurationNeverCollectsDalCriteria(): void
    {
        $resolver = self::getContainer()->get(BackgroundImageCmsElementResolver::class);
        self::assertInstanceOf(BackgroundImageCmsElementResolver::class, $resolver);

        foreach (['', 'https://example.invalid/fremd.jpg', '<script>'] as $unsafeValue) {
            $slot = $this->slot(Uuid::randomHex(), $unsafeValue);
            self::assertNull($resolver->collect($slot, $this->resolverContext()));
            $resolver->enrich($slot, $this->resolverContext(), new ElementDataCollection());
            self::assertNull($slot->getData());
        }
    }

    /** Ein echtes PDF aus dem DAL darf trotz valider Medien-ID kein Bild-Struct erzeugen. */
    public function testRealPdfMediaIsRejectedDuringEnrichment(): void
    {
        $resolver = self::getContainer()->get(BackgroundImageCmsElementResolver::class);
        self::assertInstanceOf(BackgroundImageCmsElementResolver::class, $resolver);
        $mediaId = Uuid::randomHex();
        $slotId = Uuid::randomHex();
        $context = $this->systemContext();
        $repository = $this->mediaRepository();
        $repository->create([[
            'id' => $mediaId,
            'fileName' => 'mgd-cms-pdf-' . Uuid::randomHex(),
            'fileExtension' => 'pdf',
            'mimeType' => 'application/pdf',
            'mediaTypeRaw' => serialize(new DocumentType()),
        ]], $context);
        $slot = $this->slot($slotId, $mediaId);
        $collection = $resolver->collect($slot, $this->resolverContext());
        self::assertNotNull($collection);
        $all = $collection->all();
        $key = 'mgd_ai_background_media_' . $slotId . '_' . $mediaId;
        $result = new ElementDataCollection();
        $result->add($key, $repository->search($all[MediaDefinition::class][$key], $context));

        $resolver->enrich($slot, $this->resolverContext(), $result);

        self::assertNull($slot->getData());
    }

    /** @return EntityRepository<MediaCollection> */
    private function mediaRepository(): EntityRepository
    {
        /** @var EntityRepository<MediaCollection> $repository */
        $repository = self::getContainer()->get('media.repository');

        return $repository;
    }

    private function slot(string $slotId, string $mediaId): CmsSlotEntity
    {
        $slot = new CmsSlotEntity();
        $slot->setId($slotId);
        $slot->setFieldConfig(new FieldConfigCollection([
            new FieldConfig('media', FieldConfig::SOURCE_STATIC, $mediaId),
        ]));

        return $slot;
    }

    private function resolverContext(): ResolverContext
    {
        return new ResolverContext($this->createStub(SalesChannelContext::class), new Request());
    }

    /** Schreibgeschützte Medienfelder dürfen ausschließlich im Systemkontext gesetzt werden. */
    private function systemContext(): Context
    {
        $context = Context::createDefaultContext();
        self::assertSame(Context::SYSTEM_SCOPE, $context->getScope());

        return $context;
    }
}
