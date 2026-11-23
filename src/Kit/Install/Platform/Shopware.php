<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Kit\Install\Platform;

use MGDAIImageLabels\Kit\Install\{CallbackStore, Factory, InstallClient};

// Shopware-Anbindung über SystemConfigService. Lifecycle install/activate/update -> ping(true), ScheduledTask/Admin -> ping()
final class Shopware
{
    /**
     * @param object $systemConfig Shopware\Core\System\SystemConfig\SystemConfigService
     * @param string $configKey z. B. 'MgdSeo.config.installState'
     */
    /** null = Angaben ungültig, dann keine Meldung (Lifecycle darf nie scheitern) */
    public static function client(object $systemConfig, string $configKey, string $product, string $version, string $domain): ?InstallClient
    {
        try {
            return self::make($systemConfig, $configKey, $product, $version, $domain);
        } catch (\Throwable) {
            return null;
        }
    }

    private static function make(object $systemConfig, string $configKey, string $product, string $version, string $domain): InstallClient
    {
        $store = new CallbackStore(
            static fn (): mixed => $systemConfig->get($configKey),
            static function (string $v) use ($systemConfig, $configKey): void {
                $systemConfig->set($configKey, $v);
            },
        );

        return Factory::create($store, $product, 'shopware6', $version, $domain);
    }
}
