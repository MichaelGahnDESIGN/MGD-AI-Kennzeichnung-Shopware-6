<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Configuration;

use MGDAIImageLabels\Configuration\DisplayConfigurationProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Prüft das sichere Einlesen über Shopwares öffentliche, cachefähige API.
 */
final class DisplayConfigurationProviderTest extends TestCase
{
    private const SALES_CHANNEL_ID_A = '0123456789abcdef0123456789abcdef';

    private const SALES_CHANNEL_ID_B = 'fedcba9876543210fedcba9876543210';

    /**
     * Jeder Aufruf liest neun öffentliche Konfigurationsschlüssel. Acht davon
     * sind immer global; nur die Sprache verwendet die jeweilige Kanal-ID.
     */
    public function testGetReadsGlobalDisplayValuesAndOnlyLanguagePerSalesChannel(): void
    {
        $service = $this->createSystemConfigServiceMock();
        $calls = [];

        $service->expects(self::exactly(36))
            ->method('get')
            ->willReturnCallback(static function (string $key, ?string $salesChannelId) use (&$calls): mixed {
                $calls[] = [$key, $salesChannelId];

                return match ($key) {
                    'MGDAIImageLabels.config.fontSize' => 6,
                    'MGDAIImageLabels.config.offset' => 0,
                    'MGDAIImageLabels.config.paddingY' => 2,
                    'MGDAIImageLabels.config.paddingX' => 4,
                    'MGDAIImageLabels.config.radius' => 0,
                    'MGDAIImageLabels.config.blur' => 0,
                    'MGDAIImageLabels.config.position' => 'top-right',
                    'MGDAIImageLabels.config.theme' => 'light',
                    'MGDAIImageLabels.config.language' => match ($salesChannelId) {
                        self::SALES_CHANNEL_ID_A => 'en',
                        self::SALES_CHANNEL_ID_B => 'de',
                        default => 'auto',
                    },
                    default => throw new \LogicException('Der Provider darf keine fremden Konfigurationsschlüssel lesen.'),
                };
            });

        $provider = new DisplayConfigurationProvider($service);
        $configurationA = $provider->get(self::SALES_CHANNEL_ID_A);
        $configurationB = $provider->get(self::SALES_CHANNEL_ID_B);
        $globalConfiguration = $provider->get();
        $configurationAAgain = $provider->get(self::SALES_CHANNEL_ID_A);

        self::assertSame(0, $configurationA->offset);
        self::assertSame(0, $configurationA->radius);
        self::assertSame(0, $configurationA->blur);
        self::assertSame('en', $configurationA->language);
        self::assertSame('de', $configurationB->language);
        self::assertSame('auto', $globalConfiguration->language);
        self::assertSame('en', $configurationAAgain->language);
        self::assertSame($this->expectedCallsFor(self::SALES_CHANNEL_ID_A), array_slice($calls, 0, 9));
        self::assertSame($this->expectedCallsFor(self::SALES_CHANNEL_ID_B), array_slice($calls, 9, 9));
        self::assertSame($this->expectedCallsFor(null), array_slice($calls, 18, 9));
        self::assertSame($this->expectedCallsFor(self::SALES_CHANNEL_ID_A), array_slice($calls, 27, 9));
    }

    /**
     * Manipulierte Dienstwerte bleiben unzuverlässig und müssen auf sichere
     * feldweise Standards zurückfallen, bevor sie die Darstellung erreichen.
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
     * @param ?string $salesChannelId Die erwartete ID nur für die Sprachabfrage.
     *
     * @return list<array{string, ?string}> Die neun erwarteten Shopware-Aufrufe.
     */
    private function expectedCallsFor(?string $salesChannelId): array
    {
        return [
            ['MGDAIImageLabels.config.fontSize', null],
            ['MGDAIImageLabels.config.offset', null],
            ['MGDAIImageLabels.config.paddingY', null],
            ['MGDAIImageLabels.config.paddingX', null],
            ['MGDAIImageLabels.config.radius', null],
            ['MGDAIImageLabels.config.blur', null],
            ['MGDAIImageLabels.config.position', null],
            ['MGDAIImageLabels.config.theme', null],
            ['MGDAIImageLabels.config.language', $salesChannelId],
        ];
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
