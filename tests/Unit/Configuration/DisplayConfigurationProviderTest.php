<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Configuration;

use MGDAIImageLabels\Configuration\DisplayConfigurationProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Prüft das cachegerechte und sichere Einlesen der Anzeigeeinstellungen.
 */
final class DisplayConfigurationProviderTest extends TestCase
{
    /**
     * Nur die Sprache ist verkaufskanalspezifisch. Alle Darstellungseinstellungen
     * werden mit null als globaler Ebene gelesen und danach zwischengespeichert.
     */
    public function testGetReadsOnlyLanguageForSalesChannelAndCachesTheConfiguration(): void
    {
        $salesChannelId = '0123456789abcdef0123456789abcdef';
        $service = $this->createSystemConfigServiceMock();
        $calls = [];

        $service->expects(self::exactly(9))
            ->method('get')
            ->willReturnCallback(static function (string $key, ?string $requestedSalesChannelId) use (&$calls, $salesChannelId): mixed {
                $calls[] = [$key, $requestedSalesChannelId];

                return match ($key) {
                    'MGDAIImageLabels.config.fontSize' => 18,
                    'MGDAIImageLabels.config.offset' => 32,
                    'MGDAIImageLabels.config.paddingY' => 8,
                    'MGDAIImageLabels.config.paddingX' => 15,
                    'MGDAIImageLabels.config.radius' => 20,
                    'MGDAIImageLabels.config.blur' => 4,
                    'MGDAIImageLabels.config.position' => 'top-right',
                    'MGDAIImageLabels.config.theme' => 'light',
                    'MGDAIImageLabels.config.language' => $requestedSalesChannelId === $salesChannelId ? 'en' : 'auto',
                    default => throw new \LogicException('Der Provider darf keine fremden Konfigurationsschlüssel lesen.'),
                };
            });

        $provider = new DisplayConfigurationProvider($service);
        $configuration = $provider->get($salesChannelId);
        $cachedConfiguration = $provider->get($salesChannelId);

        self::assertSame($configuration, $cachedConfiguration);
        self::assertSame(18, $configuration->fontSize);
        self::assertSame(32, $configuration->offset);
        self::assertSame(8, $configuration->paddingY);
        self::assertSame(15, $configuration->paddingX);
        self::assertSame(20, $configuration->radius);
        self::assertSame(4, $configuration->blur);
        self::assertSame('top-right', $configuration->position);
        self::assertSame('light', $configuration->theme);
        self::assertSame('en', $configuration->language);
        self::assertSame([
            ['MGDAIImageLabels.config.fontSize', null],
            ['MGDAIImageLabels.config.offset', null],
            ['MGDAIImageLabels.config.paddingY', null],
            ['MGDAIImageLabels.config.paddingX', null],
            ['MGDAIImageLabels.config.radius', null],
            ['MGDAIImageLabels.config.blur', null],
            ['MGDAIImageLabels.config.position', null],
            ['MGDAIImageLabels.config.theme', null],
            ['MGDAIImageLabels.config.language', $salesChannelId],
        ], $calls);
    }

    /**
     * Manipulierte Dienstwerte bleiben unzuverlässig. Der Provider muss sie
     * deshalb vor dem Cache-Eintrag durch den Normalizer zurückführen.
     */
    public function testGetRenormalizesManipulatedServiceValues(): void
    {
        $service = $this->createSystemConfigServiceMock();
        $service->expects(self::exactly(9))
            ->method('get')
            ->willReturnMap([
                ['MGDAIImageLabels.config.fontSize', null, '6px;background:red'],
                ['MGDAIImageLabels.config.offset', null, 97],
                ['MGDAIImageLabels.config.paddingY', null, -1],
                ['MGDAIImageLabels.config.paddingX', null, 20],
                ['MGDAIImageLabels.config.radius', null, 12],
                ['MGDAIImageLabels.config.blur', null, 8],
                ['MGDAIImageLabels.config.position', null, 'center'],
                ['MGDAIImageLabels.config.theme', null, 'dark'],
                ['MGDAIImageLabels.config.language', null, 'fr-FR'],
            ]);

        $configuration = (new DisplayConfigurationProvider($service))->get();

        self::assertSame(6, $configuration->fontSize);
        self::assertSame(12, $configuration->offset);
        self::assertSame(5, $configuration->paddingY);
        self::assertSame(20, $configuration->paddingX);
        self::assertSame(12, $configuration->radius);
        self::assertSame(8, $configuration->blur);
        self::assertSame('bottom-right', $configuration->position);
        self::assertSame('dark', $configuration->theme);
        self::assertSame('auto', $configuration->language);
    }

    /**
     * Der externe Shopware-Dienst ist die einzige unvermeidbare Test-Doppelung.
     * Sein öffentlicher get()-Vertrag wird direkt am Aufruf geprüft.
     *
     * @return SystemConfigService&MockObject
     */
    private function createSystemConfigServiceMock(): SystemConfigService&MockObject
    {
        return $this->createMock(SystemConfigService::class);
    }
}
