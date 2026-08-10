<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Cms;

use MGDAIImageLabels\Cms\Philosophy\PhilosophyPageCreator;
use MGDAIImageLabels\Cms\Philosophy\PhilosophyDefaultContent;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Shopware\Core\Content\Cms\CmsPageCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\Uuid\Uuid;

/** Prüft den vollständigen, datenbankfreien Erstellungsvertrag der Philosophie-Seite. */
final class PhilosophyPageCreatorContractTest extends TestCase
{
    public function testStableIdsAndOwnershipMarkerAreValid(): void
    {
        self::assertTrue(Uuid::isValid(PhilosophyPageCreator::pageId()));
        self::assertTrue(Uuid::isValid(PhilosophyPageCreator::sectionId()));
        self::assertTrue(Uuid::isValid(PhilosophyPageCreator::blockId()));
        self::assertTrue(Uuid::isValid(PhilosophyPageCreator::slotId()));
        self::assertSame('mgd-ai-image-labels/philosophy-page/v1', PhilosophyPageCreator::OWNERSHIP_VALUE);
    }

    public function testPayloadContainsExactlyOneTranslatedPhilosophyElementWithoutAssignments(): void
    {
        $payload = PhilosophyPageCreator::createPayload();

        self::assertSame(PhilosophyPageCreator::pageId(), $payload['id']);
        self::assertSame('landingpage', $payload['type']);
        self::assertSame(
            [PhilosophyPageCreator::OWNERSHIP_FIELD => ['token' => PhilosophyPageCreator::OWNERSHIP_VALUE]],
            $payload['customFields'],
        );
        self::assertArrayNotHasKey('categories', $payload);
        self::assertArrayNotHasKey('landingPages', $payload);
        self::assertArrayNotHasKey('homeSalesChannels', $payload);

        $sections = $payload['sections'];
        self::assertIsArray($sections);
        self::assertCount(1, $sections);
        $section = $sections[0];
        self::assertIsArray($section);
        $blocks = $section['blocks'];
        self::assertIsArray($blocks);
        self::assertCount(1, $blocks);
        $block = $blocks[0];
        self::assertIsArray($block);
        $slots = $block['slots'];
        self::assertIsArray($slots);
        self::assertCount(1, $slots);
        $slot = $slots[0];
        self::assertIsArray($slot);
        self::assertSame('mgd-ai-philosophy', $slot['type']);
        self::assertSame('content', $slot['slot']);
        $translations = $slot['translations'];
        self::assertIsArray($translations);
        self::assertSame(
            ['de-DE', 'en-GB'],
            array_keys($translations),
        );
        $localizedValues = [];
        foreach ($translations as $locale => $translation) {
            self::assertIsString($locale);
            self::assertIsArray($translation);
            $config = $translation['config'];
            self::assertIsArray($config);
            $content = $config['content'];
            self::assertIsArray($content);
            self::assertSame('static', $content['source']);
            self::assertIsString($content['value']);
            self::assertNotSame('', trim($content['value']));
            $localizedValues[$locale] = $content['value'];
        }
        self::assertSame(
            PhilosophyDefaultContent::german(),
            $localizedValues['de-DE'],
        );
        self::assertSame(
            PhilosophyDefaultContent::english(),
            $localizedValues['en-GB'],
        );
    }

    public function testCmsPageDefinitionOfficiallyExposesTranslatedCustomFields(): void
    {
        $reflection = new \ReflectionClass(\Shopware\Core\Content\Cms\CmsPageDefinition::class);
        $file = $reflection->getFileName();
        self::assertIsString($file);
        $source = file_get_contents($file);
        self::assertIsString($source);
        self::assertStringContainsString("new TranslatedField('customFields')", $source);
    }

    public function testPrepareIsIdempotentAndCreatesOnlyOnce(): void
    {
        $rows = [];
        $createPayloads = [];
        $creator = new PhilosophyPageCreator($this->repository($rows, $createPayloads));
        $context = Context::createDefaultContext();

        $first = $creator->prepare($context);
        $second = $creator->prepare($context);

        self::assertTrue($first->created);
        self::assertFalse($second->created);
        self::assertSame($first->cmsPageId, $second->cmsPageId);
        self::assertCount(1, $createPayloads);
    }

    public function testForeignSameNameRemainsUntouched(): void
    {
        $foreignId = Uuid::randomHex();
        $rows = [$foreignId => ['id' => $foreignId, 'name' => 'Unser Umgang mit KI-Bildern']];
        $createPayloads = [];

        $result = (new PhilosophyPageCreator($this->repository($rows, $createPayloads)))
            ->prepare(Context::createDefaultContext());

        self::assertTrue($result->created);
        self::assertSame('Unser Umgang mit KI-Bildern', $rows[$foreignId]['name']);
        self::assertArrayHasKey(PhilosophyPageCreator::pageId(), $rows);
    }

    public function testForeignStableIdFailsBeforeMutation(): void
    {
        $rows = [PhilosophyPageCreator::pageId() => ['id' => PhilosophyPageCreator::pageId(), 'customFields' => []]];
        $createPayloads = [];

        $this->expectException(\RuntimeException::class);
        try {
            (new PhilosophyPageCreator($this->repository($rows, $createPayloads)))
                ->prepare(Context::createDefaultContext());
        } finally {
            self::assertSame([], $createPayloads);
        }
    }

    public function testForeignSameMarkerDoesNotBlockOrChangeOwnDeterministicPage(): void
    {
        $foreignId = Uuid::randomHex();
        $foreign = [
            'id' => $foreignId,
            'name' => 'Fremdes Layout',
            'customFields' => [PhilosophyPageCreator::OWNERSHIP_FIELD => ['token' => PhilosophyPageCreator::OWNERSHIP_VALUE]],
        ];
        $rows = [$foreignId => $foreign];
        $createPayloads = [];

        $result = (new PhilosophyPageCreator($this->repository($rows, $createPayloads)))
            ->prepare(Context::createDefaultContext());

        self::assertTrue($result->created);
        self::assertSame($foreign, $rows[$foreignId]);
        self::assertArrayHasKey(PhilosophyPageCreator::pageId(), $rows);
    }

    public function testConcurrentOwnInsertReturnsCreatedFalseWithoutOverwrite(): void
    {
        $rows = [];
        $createPayloads = [];
        $result = (new PhilosophyPageCreator($this->repository(
            $rows,
            $createPayloads,
            static function (array &$raceRows): void {
                $raceRows[PhilosophyPageCreator::pageId()] = PhilosophyPageCreator::createPayload();
                throw new \RuntimeException('Simulierter Duplicate-Key-Konflikt.');
            },
        )))->prepare(Context::createDefaultContext());

        self::assertFalse($result->created);
        self::assertSame(PhilosophyPageCreator::pageId(), $result->cmsPageId);
        self::assertCount(1, $createPayloads);
    }

    public function testConcurrentForeignInsertFailsClosed(): void
    {
        $rows = [];
        $createPayloads = [];
        $creator = new PhilosophyPageCreator($this->repository(
            $rows,
            $createPayloads,
            static function (array &$raceRows): void {
                $raceRows[PhilosophyPageCreator::pageId()] = [
                    'id' => PhilosophyPageCreator::pageId(),
                    'customFields' => [],
                ];
                throw new \RuntimeException('Simulierter fremder Duplicate-Key-Konflikt.');
            },
        ));

        $this->expectException(\RuntimeException::class);
        $creator->prepare(Context::createDefaultContext());
    }

    /**
     * @param array<string, array<string, mixed>> $rows
     * @param list<array<string, mixed>>          $createPayloads
     *
     * @return EntityRepository<CmsPageCollection>
     */
    private function repository(array &$rows, array &$createPayloads, ?\Closure $beforeCreate = null): EntityRepository
    {
        /** @var EntityRepository<CmsPageCollection>&MockObject $repository */
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('searchIds')->willReturnCallback(
            static function (Criteria $criteria, Context $context) use (&$rows): IdSearchResult {
                $ids = [];
                foreach ($rows as $id => $row) {
                    if ($criteria->getIds() !== [] && !in_array($id, $criteria->getIds(), true)) {
                        continue;
                    }
                    $matches = true;
                    foreach ($criteria->getFilters() as $filter) {
                        if (!$filter instanceof EqualsFilter) {
                            $matches = false;
                            break;
                        }
                        $field = $filter->getField();
                        $value = $row;
                        foreach (explode('.', $field) as $segment) {
                            $value = is_array($value) ? ($value[$segment] ?? null) : null;
                        }
                        if ($value !== $filter->getValue()) {
                            $matches = false;
                            break;
                        }
                    }
                    if ($matches) {
                        $ids[] = $id;
                    }
                }

                return IdSearchResult::fromIds($ids, $criteria, $context);
            },
        );
        $repository->method('create')->willReturnCallback(
            static function (array $payloads, Context $context) use (&$rows, &$createPayloads, $beforeCreate): EntityWrittenContainerEvent {
                foreach ($payloads as $payload) {
                    if (!is_array($payload) || !isset($payload['id']) || !is_string($payload['id'])) {
                        throw new \InvalidArgumentException('Ungültiger CMS-Testpayload.');
                    }
                    $createPayloads[] = $payload;
                    if ($beforeCreate !== null) {
                        $beforeCreate($rows);
                    }
                    $rows[$payload['id']] = $payload;
                }

                return EntityWrittenContainerEvent::createWithWrittenEvents([], $context, []);
            },
        );

        return $repository;
    }
}
