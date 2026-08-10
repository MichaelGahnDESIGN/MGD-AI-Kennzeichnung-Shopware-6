<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Cms\Philosophy;

use Shopware\Core\Content\Cms\CmsPageCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * Erstellt ausschließlich das plugin-eigene, unverknüpfte Erlebniswelten-Layout.
 *
 * Shopwares CmsPageDefinition stellt `customFields` offiziell als übersetztes
 * API-Feld bereit. Die stabile Kennung wird in allen gelieferten Übersetzungen
 * und im Systemsprach-Fallback abgelegt. Der sichtbare Seitenname ist bewusst
 * kein Eigentumsnachweis: Eine fremde Seite mit demselben Namen bleibt unberührt.
 */
final readonly class PhilosophyPageCreator
{
    public const OWNERSHIP_FIELD = 'mgd_ai_image_labels_owner';

    public const OWNERSHIP_VALUE = 'mgd-ai-image-labels/philosophy-page/v1';

    private const PAGE_SEED = 'mgd-ai-image-labels:philosophy-page:v1';

    /** @param EntityRepository<CmsPageCollection> $cmsPageRepository */
    public function __construct(private EntityRepository $cmsPageRepository)
    {
    }

    /**
     * Erstellt das Layout höchstens einmal und überschreibt niemals fremde Daten.
     *
     * `create()` statt `upsert()` verhindert, dass eine kurz vor dem Schreiben
     * entstandene fremde Zeile unter unserer deterministischen ID überschrieben
     * wird. Nach einem konkurrierenden Insert wird Eigentum erneut geprüft.
     */
    public function prepare(Context $context): PhilosophyPageCreationResult
    {
        $existing = $this->verifyOwnershipState($context);
        if ($existing) {
            return new PhilosophyPageCreationResult(false, self::pageId());
        }

        try {
            $this->cmsPageRepository->create([self::createPayload()], $context);
        } catch (\Throwable $exception) {
            if ($this->verifyOwnershipState($context)) {
                return new PhilosophyPageCreationResult(false, self::pageId());
            }

            throw $exception;
        }

        if (!$this->verifyOwnershipState($context)) {
            throw new \RuntimeException('Die erstellte AI-Philosophie-Seite konnte nicht sicher als plugin-eigen verifiziert werden.');
        }

        return new PhilosophyPageCreationResult(true, self::pageId());
    }

    public static function pageId(): string
    {
        return Uuid::fromStringToHex(self::PAGE_SEED);
    }

    public static function sectionId(): string
    {
        return Uuid::fromStringToHex(self::PAGE_SEED . ':section');
    }

    public static function blockId(): string
    {
        return Uuid::fromStringToHex(self::PAGE_SEED . ':block');
    }

    public static function slotId(): string
    {
        return Uuid::fromStringToHex(self::PAGE_SEED . ':slot');
    }

    /**
     * Liefert den vollständigen DAL-Payload ohne Kategorien, Landingpages oder
     * Verkaufskanäle. Die Redaktion veröffentlicht und verknüpft ihn später
     * bewusst über Shopwares normale Erlebniswelten-Bearbeitung.
     *
     * @return array<string, mixed>
     */
    public static function createPayload(): array
    {
        /*
         * Ein Objektwert nutzt Shopwares offiziellen JsonField-Fallback für
         * unbekannte Custom Fields. So braucht die unsichtbare Eigentumskennung
         * kein redaktionell sichtbares Custom-Field-Set auf der CMS-Seite.
         */
        $marker = [self::OWNERSHIP_FIELD => ['token' => self::OWNERSHIP_VALUE]];

        return [
            'id' => self::pageId(),
            'name' => 'Our approach to AI imagery',
            'type' => 'landingpage',
            'locked' => false,
            'customFields' => $marker,
            'translations' => [
                'de-DE' => ['name' => 'Unser Umgang mit KI-Bildern', 'customFields' => $marker],
                'en-GB' => ['name' => 'Our approach to AI imagery', 'customFields' => $marker],
            ],
            'sections' => [[
                'id' => self::sectionId(),
                'type' => 'default',
                'position' => 0,
                'sizingMode' => 'boxed',
                'blocks' => [[
                    'id' => self::blockId(),
                    'type' => 'text',
                    'position' => 0,
                    'sectionPosition' => 'main',
                    'slots' => [[
                        'id' => self::slotId(),
                        'type' => 'mgd-ai-philosophy',
                        'slot' => 'content',
                        'config' => self::contentConfig(self::englishContent()),
                        'translations' => [
                            'de-DE' => ['config' => self::contentConfig(self::germanContent())],
                            'en-GB' => ['config' => self::contentConfig(self::englishContent())],
                        ],
                    ]],
                ]],
            ]],
        ];
    }

    /** Prüft ID und Marker getrennt, bevor irgendeine Mutation stattfinden darf. */
    private function verifyOwnershipState(Context $context): bool
    {
        $idsWithMarker = $this->searchIds(
            (new Criteria())->addFilter(new EqualsFilter(
                'customFields.' . self::OWNERSHIP_FIELD . '.token',
                self::OWNERSHIP_VALUE,
            )),
            $context,
        );
        if ($idsWithMarker !== [] && $idsWithMarker !== [self::pageId()]) {
            throw new \RuntimeException('Die Eigentumskennung der AI-Philosophie-Seite ist bereits einem fremden Datensatz zugeordnet.');
        }

        $idsAtOwnPrimaryKey = $this->searchIds(new Criteria([self::pageId()]), $context);
        if ($idsAtOwnPrimaryKey === []) {
            return false;
        }

        if ($idsAtOwnPrimaryKey !== [self::pageId()] || $idsWithMarker !== [self::pageId()]) {
            throw new \RuntimeException('Die stabile ID der AI-Philosophie-Seite ist bereits von einem fremden Datensatz belegt.');
        }

        return true;
    }

    /** @return list<string> */
    private function searchIds(Criteria $criteria, Context $context): array
    {
        $ids = $this->cmsPageRepository->searchIds($criteria, $context)->getIds();
        $normalized = [];
        foreach ($ids as $id) {
            if (!Uuid::isValid($id)) {
                throw new \RuntimeException('Die AI-Philosophie-Seite besitzt keine gültige Shopware-ID.');
            }
            $normalized[] = $id;
        }

        sort($normalized);

        return $normalized;
    }

    /** @return array{content: array{source: string, value: string}} */
    private static function contentConfig(string $content): array
    {
        return ['content' => ['source' => 'static', 'value' => $content]];
    }

    private static function germanContent(): string
    {
        return '<h2>Unser Umgang mit KI-Bildern</h2><p>Wir kennzeichnen Bilder transparent, wenn sie vollständig oder teilweise mit künstlicher Intelligenz erstellt oder bearbeitet wurden.</p><p>So können Sie Inhalte bewusst einordnen. Die Kennzeichnung verändert weder Produktinformationen noch Ihre Privatsphäre.</p>';
    }

    private static function englishContent(): string
    {
        return '<h2>Our approach to AI imagery</h2><p>We label images transparently when they were created or edited wholly or partly with artificial intelligence.</p><p>This helps you assess content consciously. The label changes neither product information nor your privacy.</p>';
    }
}
