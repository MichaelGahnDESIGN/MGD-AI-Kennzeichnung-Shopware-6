<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Kit\Platform\Shopware;

// Shopware-Kleber. Nur hier stehen Shopware-Klassen, Typen locker (object), damit die Datei ohne Shopware lintet
// geprüft gegen shopware/shopware v6.5.8.18, v6.6.10.6, v6.7.16.1
final class ShopwareServices implements ShopwareApi, CatalogMediaDir
{
    public const RUNTIME_PARAM = 'shopware.deployment.runtime_extension_management';
    // Storefront-Typ in sales_channel.type_id
    public const STOREFRONT_TYPE = '8a243080f92e4c719546314b577cf82b';

    /**
     * @param object $management Shopware\Core\Framework\Plugin\PluginManagementService
     * @param object $plugins Shopware\Core\Framework\Plugin\PluginService
     * @param object $lifecycle Shopware\Core\Framework\Plugin\PluginLifecycleService
     * @param object $config Shopware\Core\System\SystemConfig\SystemConfigService
     * @param object|null $db Doctrine\DBAL\Connection
     * @param object|null $params parameter_bag (6.5 kennt den Runtime-Schalter noch nicht)
     */
    public function __construct(
        private readonly object $management,
        private readonly object $plugins,
        private readonly object $lifecycle,
        private readonly object $config,
        private readonly ?object $db = null,
        private readonly ?object $params = null,
    ) {
    }

    public function plugin(string $technicalName, ?object $context = null): ?array
    {
        try {
            $p = $this->plugins->getPluginByName($technicalName, $context ?? self::defaultContext());
        } catch (\Throwable) {
            return null;
        }

        return [
            'version' => (string) $p->getVersion(),
            'upgrade' => is_string($p->getUpgradeVersion()) ? $p->getUpgradeVersion() : null,
            'installed' => $p->getInstalledAt() !== null,
            'active' => (bool) $p->getActive(),
            'path' => (string) $p->getPath(),
            'composer' => (bool) $p->getManagedByComposer(),
        ];
    }

    // gleicher Weg wie downloadStorePlugin(): entpacken mit Typ plugin, danach refreshPlugins
    public function extract(string $zipFile, object $context): void
    {
        $this->management->extractPluginZip($zipFile, true, 'plugin');
        $this->plugins->refreshPlugins($context, new \Composer\IO\NullIO());
    }

    public function update(string $technicalName, object $context): void
    {
        $p = $this->plugins->getPluginByName($technicalName, $context);
        $this->lifecycle->updatePlugin($p, $context);
    }

    public function runtimeAllowed(): bool
    {
        try {
            if ($this->params === null || !$this->params->has(self::RUNTIME_PARAM)) {
                return true;
            }

            return (bool) $this->params->get(self::RUNTIME_PARAM);
        } catch (\Throwable) {
            // unklar = nein
            return false;
        }
    }

    // erste aktive Storefront-Domain, sonst APP_URL
    public function domain(): string
    {
        return self::domainOf($this->db);
    }

    /** auch für Lifecycle/Deinstallieren (UninstallShop), dort gibts nur DBAL, Befund slider: Domain war leer */
    public static function domainOf(?object $db): string
    {
        try {
            $url = $db?->fetchOne(
                'SELECT d.url FROM sales_channel_domain d INNER JOIN sales_channel s ON s.id = d.sales_channel_id '
                . 'WHERE s.type_id = UNHEX(:type) AND s.active = 1 ORDER BY d.created_at ASC LIMIT 1',
                ['type' => self::STOREFRONT_TYPE],
            );
            $host = is_string($url) ? (string) parse_url($url, PHP_URL_HOST) : '';
            if ($host !== '') {
                return strtolower($host);
            }
        } catch (\Throwable) {
        }
        $app = (string) ($_SERVER['APP_URL'] ?? $_ENV['APP_URL'] ?? getenv('APP_URL') ?: '');

        return strtolower((string) parse_url($app, PHP_URL_HOST));
    }

    // var/ liegt nicht unter public/ > Bilder gehen nur über die Admin-API raus
    public function catalogMediaDir(string $product): string
    {
        try {
            $root = $this->params !== null && $this->params->has('kernel.project_dir') ? (string) $this->params->get('kernel.project_dir') : '';
            if ($root === '' || preg_match(ShopwareKit::PRODUCT_RE, $product) !== 1 || !is_dir($root . '/var')) {
                return '';
            }

            return rtrim($root, '/') . '/var/mgd-kit/' . $product . '/catalog';
        } catch (\Throwable) {
            return '';
        }
    }

    public function get(string $key): mixed
    {
        return $this->config->get($key);
    }

    public function set(string $key, mixed $value): void
    {
        $this->config->set($key, $value);
    }

    private static function defaultContext(): object
    {
        return \Shopware\Core\Framework\Context::createDefaultContext();
    }
}
