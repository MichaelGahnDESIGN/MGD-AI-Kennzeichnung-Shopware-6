<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Configuration;

use MGDAIImageLabels\Configuration\ConfigurationBackupEntry;
use MGDAIImageLabels\Configuration\ConfigurationBackupStorage;
use MGDAIImageLabels\Configuration\ConfigurationRetentionService;
use MGDAIImageLabels\Configuration\ConfigurationKeys;
use PHPUnit\Framework\TestCase;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/** Prüft Restore, Cache-Invalidierung und das Entfernen verbrauchter Sicherungen. */
final class ConfigurationRetentionServiceTest extends TestCase
{
    public function testReinstallRestoresAllScopesAndClearsBackup(): void
    {
        $entries = [
            new ConfigurationBackupEntry(ConfigurationKeys::LANGUAGE, null, 'de'),
            new ConfigurationBackupEntry(ConfigurationKeys::FONT_SIZE, null, 13),
            new ConfigurationBackupEntry(ConfigurationKeys::LANGUAGE, '018f123456789abcdef0123456789abc', 'en'),
        ];
        $storage = $this->createMock(ConfigurationBackupStorage::class);
        $storage->expects(self::once())->method('restoreTransaction')->willReturnCallback(
            static function (callable $restore) use ($entries): void {
                $restore($entries);
            },
        );
        $systemConfig = $this->createMock(SystemConfigService::class);
        $systemConfig->expects(self::exactly(3))->method('set')->willReturnCallback(
            static function (string $key, mixed $value, ?string $salesChannelId) use ($entries): void {
                static $index = 0;
                $expected = $entries[$index++];
                self::assertSame($expected->key, $key);
                self::assertSame($expected->value, $value);
                self::assertSame($expected->salesChannelId, $salesChannelId);
            },
        );

        (new ConfigurationRetentionService($storage, $systemConfig))->restoreAfterInstall();
    }

    public function testFirstInstallWithoutBackupDoesNotWriteConfiguration(): void
    {
        $storage = $this->createMock(ConfigurationBackupStorage::class);
        $storage->expects(self::once())->method('restoreTransaction')->willReturnCallback(
            static function (callable $restore): void {
                $restore([]);
            },
        );
        $systemConfig = $this->createMock(SystemConfigService::class);
        $systemConfig->expects(self::never())->method('set');

        (new ConfigurationRetentionService($storage, $systemConfig))->restoreAfterInstall();
    }

    public function testKeepUninstallCreatesFreshSnapshot(): void
    {
        $storage = $this->createMock(ConfigurationBackupStorage::class);
        $storage->expects(self::once())->method('replaceSnapshot');

        (new ConfigurationRetentionService(
            $storage,
            $this->createStub(SystemConfigService::class),
        ))->snapshotBeforeKeepUninstall();
    }

    public function testNoKeepUninstallDropsOnlyBackupTable(): void
    {
        $storage = $this->createMock(ConfigurationBackupStorage::class);
        $storage->expects(self::once())->method('dropTable');
        $systemConfig = $this->createMock(SystemConfigService::class);
        $systemConfig->expects(self::never())->method('set');

        (new ConfigurationRetentionService($storage, $systemConfig))->removeBackupWithoutUserData();
    }
}
