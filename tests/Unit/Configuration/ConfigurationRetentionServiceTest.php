<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Configuration;

use MGDAIImageLabels\Configuration\ConfigurationBackupEntry;
use MGDAIImageLabels\Configuration\ConfigurationBackupStorage;
use MGDAIImageLabels\Configuration\ConfigurationKeys;
use MGDAIImageLabels\Configuration\ConfigurationRetentionService;
use PHPUnit\Framework\TestCase;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/** Prüft den exakten Restore einschließlich leerer, globaler und kanalbezogener Werte. */
final class ConfigurationRetentionServiceTest extends TestCase
{
    public function testReinstallDeletesCoreDefaultsAndRestoresAllSnapshotScopes(): void
    {
        $snapshot = [
            new ConfigurationBackupEntry(ConfigurationKeys::LANGUAGE, null, 'de'),
            new ConfigurationBackupEntry(ConfigurationKeys::FONT_SIZE, null, 13),
            new ConfigurationBackupEntry(ConfigurationKeys::LANGUAGE, '018f123456789abcdef0123456789abc', 'en'),
        ];
        $current = [
            new ConfigurationBackupEntry(ConfigurationKeys::LANGUAGE, null, 'auto'),
            new ConfigurationBackupEntry(ConfigurationKeys::FONT_SIZE, null, 6),
        ];
        $storage = $this->createMock(ConfigurationBackupStorage::class);
        $storage->expects(self::once())->method('restoreTransaction')->willReturnCallback(
            static function (callable $restore) use ($snapshot, $current): bool {
                $restore($snapshot, $current);

                return true;
            },
        );
        $systemConfig = $this->createMock(SystemConfigService::class);
        $systemConfig->expects(self::exactly(2))->method('delete');
        $systemConfig->expects(self::exactly(3))->method('set');

        (new ConfigurationRetentionService($storage, $systemConfig))->restoreAfterInstall();
    }

    public function testExplicitEmptySnapshotDeletesEveryImportedDefault(): void
    {
        $current = [
            new ConfigurationBackupEntry(ConfigurationKeys::LANGUAGE, null, 'auto'),
            new ConfigurationBackupEntry(ConfigurationKeys::FONT_SIZE, null, 6),
        ];
        $storage = $this->createMock(ConfigurationBackupStorage::class);
        $storage->expects(self::once())->method('restoreTransaction')->willReturnCallback(
            static function (callable $restore) use ($current): bool {
                $restore([], $current);

                return true;
            },
        );
        $systemConfig = $this->createMock(SystemConfigService::class);
        $systemConfig->expects(self::exactly(2))->method('delete');
        $systemConfig->expects(self::never())->method('set');

        (new ConfigurationRetentionService($storage, $systemConfig))->restoreAfterInstall();
    }

    public function testFirstInstallWithoutSnapshotDoesNotInvokeWrites(): void
    {
        $storage = $this->createMock(ConfigurationBackupStorage::class);
        $storage->expects(self::once())->method('restoreTransaction')->willReturn(false);
        $systemConfig = $this->createMock(SystemConfigService::class);
        $systemConfig->expects(self::never())->method('delete');
        $systemConfig->expects(self::never())->method('set');

        (new ConfigurationRetentionService($storage, $systemConfig))->restoreAfterInstall();
    }

    public function testKeepAndNoKeepDelegateToTheOwnedStorageBoundary(): void
    {
        $storage = $this->createMock(ConfigurationBackupStorage::class);
        $storage->expects(self::once())->method('replaceSnapshot');
        $storage->expects(self::once())->method('dropTable');
        $service = new ConfigurationRetentionService($storage, $this->createStub(SystemConfigService::class));

        $service->snapshotBeforeKeepUninstall();
        $service->removeBackupWithoutUserData();
    }
}
