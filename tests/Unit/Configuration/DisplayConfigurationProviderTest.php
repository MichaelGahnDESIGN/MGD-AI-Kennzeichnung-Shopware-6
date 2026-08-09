<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Configuration;

use MGDAIImageLabels\Configuration\DisplayConfigurationProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Prüft das sichere Einlesen der globalen und verkaufskanalspezifischen Werte.
 */
final class DisplayConfigurationProviderTest extends TestCase
{
    /**
     * Der Provider muss mit aktivierter Vererbung lesen, exakt den eigenen
     * Präfix entfernen und fremde Schlüssel vollständig ignorieren.
     */
    public function testGetReadsInheritedSalesChannelDomainAndIgnoresForeignKeys(): void
    {
        $service = $this->createSystemConfigServiceMock();
        $service->expects(self::once())
            ->method('getDomain')
            ->with('MGDAIImageLabels.config.', 'sales-channel-id', true)
            ->willReturn([
                'MGDAIImageLabels.config.fontSize' => 18,
                'MGDAIImageLabels.config.offset' => 32,
                'MGDAIImageLabels.config.paddingY' => 8,
                'MGDAIImageLabels.config.paddingX' => 15,
                'MGDAIImageLabels.config.radius' => 20,
                'MGDAIImageLabels.config.blur' => 4,
                'MGDAIImageLabels.config.position' => 'top-right',
                'MGDAIImageLabels.config.theme' => 'light',
                'MGDAIImageLabels.config.language' => 'en',
                'OtherPlugin.config.fontSize' => 24,
                'MGDAIImageLabels.fontSize' => 24,
            ]);

        $configuration = (new DisplayConfigurationProvider($service))->get('sales-channel-id');

        self::assertSame(18, $configuration->fontSize);
        self::assertSame(32, $configuration->offset);
        self::assertSame(8, $configuration->paddingY);
        self::assertSame(15, $configuration->paddingX);
        self::assertSame(20, $configuration->radius);
        self::assertSame(4, $configuration->blur);
        self::assertSame('top-right', $configuration->position);
        self::assertSame('light', $configuration->theme);
        self::assertSame('en', $configuration->language);
    }

    /**
     * Auch Werte aus der Datenbank bleiben nicht vertrauenswürdig und müssen
     * vor jeder Verwendung erneut durch den Normalizer laufen.
     */
    public function testGetRenormalizesManipulatedStoredValuesAndUsesDefaultsForEmptyDomain(): void
    {
        $service = $this->createSystemConfigServiceMock();
        $service->expects(self::exactly(2))
            ->method('getDomain')
            ->with('MGDAIImageLabels.config.', null, true)
            ->willReturnOnConsecutiveCalls(
                [
                    'MGDAIImageLabels.config.fontSize' => '6px;background:red',
                    'MGDAIImageLabels.config.offset' => 97,
                    'MGDAIImageLabels.config.position' => 'center',
                    'MGDAIImageLabels.config.theme' => 'dark',
                    'MGDAIImageLabels.config.language' => 'fr-FR',
                ],
                [],
            );

        $provider = new DisplayConfigurationProvider($service);
        $manipulatedConfiguration = $provider->get();
        $defaultConfiguration = $provider->get();

        self::assertSame(6, $manipulatedConfiguration->fontSize);
        self::assertSame(12, $manipulatedConfiguration->offset);
        self::assertSame('bottom-right', $manipulatedConfiguration->position);
        self::assertSame('dark', $manipulatedConfiguration->theme);
        self::assertSame('auto', $manipulatedConfiguration->language);
        self::assertSame(6, $defaultConfiguration->fontSize);
        self::assertSame('bottom-right', $defaultConfiguration->position);
    }

    /**
     * Der externe Dienst ist die einzige unvermeidbare Test-Doppelung.
     * Sein Vertrag wird über die Erwartungen der aufrufenden Tests geprüft.
     *
     * @return SystemConfigService&MockObject
     */
    private function createSystemConfigServiceMock(): SystemConfigService&MockObject
    {
        return $this->createMock(SystemConfigService::class);
    }
}
