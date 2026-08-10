<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Integration\Cms;

use MGDAIImageLabels\Cms\Philosophy\PhilosophyPageCreator;
use MGDAIImageLabels\Tests\Integration\Setup\ShopwareIntegrationTestBootstrap;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Cms\CmsPageCollection;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;

/** Prüft Idempotenz, Eigentum und den exakten unverknüpften DAL-Baum. */
#[Group('integration')]
final class PhilosophyPageCreatorTest extends TestCase
{
    use IntegrationTestBehaviour;

    public static function setUpBeforeClass(): void
    {
        ShopwareIntegrationTestBootstrap::boot(dirname(__DIR__, 3) . '/composer.json');
    }

    public function testPrepareTwiceCreatesOneExactUnassignedLayoutAndPreservesForeignSameName(): void
    {
        $context = Context::createDefaultContext();
        $foreignId = Uuid::randomHex();
        $this->repository()->create([[
            'id' => $foreignId,
            'name' => 'Unser Umgang mit KI-Bildern',
            'type' => 'landingpage',
        ]], $context);

        $creator = self::getContainer()->get(PhilosophyPageCreator::class);
        self::assertInstanceOf(PhilosophyPageCreator::class, $creator);
        $first = $creator->prepare($context);
        $second = $creator->prepare($context);

        self::assertTrue($first->created);
        self::assertFalse($second->created);
        self::assertSame(PhilosophyPageCreator::pageId(), $first->cmsPageId);
        self::assertSame($first->cmsPageId, $second->cmsPageId);

        $criteria = new Criteria([$first->cmsPageId]);
        $criteria->addAssociations([
            'sections.blocks.slots',
            'categories',
            'landingPages',
            'homeSalesChannels',
        ]);
        $page = $this->repository()->search($criteria, $context)->first();
        self::assertInstanceOf(CmsPageEntity::class, $page);
        self::assertSame('landingpage', $page->getType());
        $customFields = $page->getCustomFields();
        self::assertIsArray($customFields);
        $ownership = $customFields[PhilosophyPageCreator::OWNERSHIP_FIELD] ?? null;
        self::assertIsArray($ownership);
        self::assertSame(PhilosophyPageCreator::OWNERSHIP_VALUE, $ownership['token'] ?? null);
        self::assertCount(1, $page->getSections() ?? []);
        $section = $page->getSections()?->first();
        self::assertNotNull($section);
        self::assertCount(1, $section->getBlocks() ?? []);
        $block = $section->getBlocks()?->first();
        self::assertNotNull($block);
        self::assertCount(1, $block->getSlots() ?? []);
        $slot = $block->getSlots()?->first();
        self::assertNotNull($slot);
        self::assertSame('mgd-ai-philosophy', $slot->getType());
        self::assertSame('content', $slot->getSlot());
        self::assertCount(0, $page->getCategories() ?? []);
        self::assertCount(0, $page->getLandingPages() ?? []);
        self::assertCount(0, $page->getHomeSalesChannels() ?? []);

        $foreign = $this->repository()->search(new Criteria([$foreignId]), $context)->first();
        self::assertInstanceOf(CmsPageEntity::class, $foreign);
        self::assertSame('Unser Umgang mit KI-Bildern', $foreign->getName());
    }

    public function testForeignStableIdFailsBeforeMutation(): void
    {
        $context = Context::createDefaultContext();
        $this->repository()->create([[
            'id' => PhilosophyPageCreator::pageId(),
            'name' => 'Fremdes Layout',
            'type' => 'page',
        ]], $context);

        $creator = self::getContainer()->get(PhilosophyPageCreator::class);
        self::assertInstanceOf(PhilosophyPageCreator::class, $creator);
        $this->expectException(\RuntimeException::class);
        $creator->prepare($context);
    }

    public function testForeignOwnershipMarkerFailsBeforeMutation(): void
    {
        $context = Context::createDefaultContext();
        $foreignId = Uuid::randomHex();
        $this->repository()->create([[
            'id' => $foreignId,
            'name' => 'Fremde Kennung',
            'type' => 'page',
            'customFields' => [
                PhilosophyPageCreator::OWNERSHIP_FIELD => ['token' => PhilosophyPageCreator::OWNERSHIP_VALUE],
            ],
        ]], $context);

        $creator = self::getContainer()->get(PhilosophyPageCreator::class);
        self::assertInstanceOf(PhilosophyPageCreator::class, $creator);
        $this->expectException(\RuntimeException::class);
        $creator->prepare($context);
    }

    /** @return EntityRepository<CmsPageCollection> */
    private function repository(): EntityRepository
    {
        /** @var EntityRepository<CmsPageCollection> $repository */
        $repository = self::getContainer()->get('cms_page.repository');

        return $repository;
    }
}
