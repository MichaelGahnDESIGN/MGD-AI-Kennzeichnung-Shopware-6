<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Kit\Platform\Shopware;

use MGDAIImageLabels\Kit\Install\{CallbackStore, CurlTransport, InstallClient, Transport};
use MGDAIImageLabels\Kit\Catalog\{MediaCache, Recommendations};
use MGDAIImageLabels\Kit\License\{CurlPost, HttpPost, LicenseClient};

// Einstieg für Shopware-Plugins (docs/PLUGIN-BAUKASTEN.md, Kapitel Shopware). Wird per services.xml gebaut
// Zustand in der SystemConfig unter <Technischer Name>.config.*
final class ShopwareKit
{
    public const PRODUCT_RE = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/D';
    public const TECH_RE = '/^[A-Z][A-Za-z0-9]{2,60}$/D';
    public const VERSION_RE = '/^[0-9]{1,5}\.[0-9]{1,5}\.[0-9]{1,5}$/D';

    // alles was das Kit in der SystemConfig ablegt
    public const CONFIG_NAMES = ['installState', 'installReport', 'mgdKitUpdates', 'mgdKitRecommendations', 'mgdKitLicense'];

    private ?ApiHandler $handler = null;

    /**
     * @param string $rootDir Ordner in der ZIP / in custom/plugins, '' = technischer Name
     * @param list<array<string,mixed>> $fallback mitgelieferte Liste "Weitere Plugins"
     */
    public function __construct(
        private readonly ShopwareApi $shop,
        private readonly string $product,
        private readonly string $technicalName,
        private readonly string $pluginName,
        private readonly string $catalogSlug = '',
        private readonly bool $licenseEnabled = false,
        private string $rootDir = '',
        private readonly array $fallback = [],
        private readonly ?Download $download = null,
        private readonly ?Transport $http = null,
        private readonly ?HttpPost $post = null,
        private readonly ?MediaCache $media = null,
    ) {
        if (preg_match(self::PRODUCT_RE, $product) !== 1 || preg_match(self::TECH_RE, $technicalName) !== 1) {
            throw new \InvalidArgumentException('Produkt oder technischer Name ungültig.');
        }
        $this->rootDir = $rootDir !== '' ? $rootDir : $technicalName;
    }

    public function handler(): ApiHandler
    {
        return $this->handler ??= $this->build();
    }

    public function configKey(string $name): string
    {
        return $this->technicalName . '.config.' . $name;
    }

    // eigener Bereich außerhalb von <Tech>.config: die Einstellungsseite schreibt beim Speichern alle config-Werte zurück
    // und könnte sonst einen neueren Zustand überschreiben (Befund theme). installReport bleibt in config (Schalter)
    public function stateKey(string $name): string
    {
        return $this->technicalName . '.mgdKit.' . $name;
    }

    /** Install-Client mit der Version aus der Plugin-Tabelle, null = Angaben fehlen */
    public function installClient(): ?InstallClient
    {
        $version = (string) ($this->shop->plugin($this->technicalName)['version'] ?? '');
        if (preg_match(self::VERSION_RE, $version) !== 1) {
            return null;
        }
        try {
            // wie Install\Platform\Shopware::client, nur mit austauschbarem Transport (Tests)
            return new InstallClient($this->http ?? new CurlTransport('MGDInstallClient/' . InstallClient::CLIENT_VERSION . ' (shopware6)'),
                $this->store('installState'), $this->product, 'shopware6', $version, $this->shop->domain());
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Aus Plugin::uninstall(): Kit für den UninstallContext bauen, nur mit SystemConfigService
     * @param object $uninstallContext Shopware\Core\Framework\Plugin\Context\UninstallContext
     * @param object $systemConfig Shopware\Core\System\SystemConfig\SystemConfigService
     * @param object|null $connection Doctrine\DBAL\Connection, ohne > aus dem SystemConfigService geholt
     */
    public static function forUninstall(object $uninstallContext, object $systemConfig, string $product, ?Transport $http = null, ?object $connection = null): ?self
    {
        try {
            $tech = (string) $uninstallContext->getPlugin()->getName();
            $version = (string) $uninstallContext->getCurrentPluginVersion();
            $db = $connection ?? UninstallShop::connectionOf($systemConfig);

            return new self(new UninstallShop($systemConfig, $tech, $version, $db), $product, $tech, $tech, http: $http);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Aus activate()/postUpdate(): Kit ohne services.xml bauen (Dienste gibts da noch nicht, Befund slider)
     * Version: bei UpdateContext die NEUE Version, sonst die aktuelle
     * @param object $context Shopware\Core\Framework\Plugin\Context\ActivateContext|UpdateContext|InstallContext
     */
    public static function forLifecycle(object $context, object $systemConfig, string $product, ?Transport $http = null, ?object $connection = null): ?self
    {
        try {
            $tech = (string) $context->getPlugin()->getName();
            $version = method_exists($context, 'getUpdatePluginVersion') ? (string) $context->getUpdatePluginVersion() : (string) $context->getCurrentPluginVersion();
            $db = $connection ?? UninstallShop::connectionOf($systemConfig);

            return new self(new UninstallShop($systemConfig, $tech, $version, $db), $product, $tech, $tech, http: $http);
        } catch (\Throwable) {
            return null;
        }
    }

    /** sofort melden, nur mit Zustimmung (Schalter Installation melden). Wirft nie */
    public function reportNow(): bool
    {
        try {
            $c = $this->installClient();

            return $c !== null && $c->isEnabled() && $c->ping(true);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Deinstallieren: Eintrag auf der Plattform löschen (immer), Kit-Config nur ohne keepUserData. Wirft nie
     * true = Install-Client war da
     */
    public function uninstall(object $uninstallContext): bool
    {
        $keep = true;
        try {
            $keep = !method_exists($uninstallContext, 'keepUserData') || $uninstallContext->keepUserData() !== false;
        } catch (\Throwable) {
        }
        $done = false;
        try {
            $c = $this->installClient();
            if ($c !== null) {
                $was = $c->isEnabled();
                $c->setEnabled(false);
                // Daten bleiben > Schalter wie vorher, nur ohne alte Anmeldung
                if ($keep && $was) {
                    $c->setEnabled(true);
                }
                $done = true;
            }
        } catch (\Throwable) {
        }
        if (!$keep) {
            foreach (self::CONFIG_NAMES as $name) {
                foreach ([$this->configKey($name), $this->stateKey($name)] as $k) {
                    try {
                        $this->shop->set($k, null);
                    } catch (\Throwable) {
                    }
                }
            }
        }

        return $done;
    }

    public function license(): LicenseClient
    {
        $on = $this->licenseEnabled;

        return new LicenseClient($on ? ($this->post ?? new CurlPost('MGDKit/1.0 (shopware6)')) : null, $this->store('mgdKitLicense'), $on ? $this->shop->domain() : '', $on);
    }

    private function build(): ApiHandler
    {
        $client = $this->installClient();
        $updates = new UpdateService($client, $this->store('mgdKitUpdates'), $this->product, $this->rootDir);
        $updater = new PluginUpdater($this->shop, $updates, $this->download ?? new CurlDownload('MGDKit/1.0 (shopware6)'), $this->technicalName, $this->rootDir);

        return new ApiHandler($this->pluginName, $this->technicalName, $this->shop, $updates, $updater, $this->recommendations(), $client, $this->license(), $this->configKey('installReport'), $this->mediaCache());
    }

    // Katalog-Bilder im privaten var/-Ordner, raus nur als data:-URI über die Admin-API
    private function mediaCache(): ?MediaCache
    {
        if ($this->media !== null) {
            return $this->media;
        }
        $dir = $this->shop instanceof CatalogMediaDir ? $this->shop->catalogMediaDir($this->product) : '';

        return $dir !== '' ? new MediaCache($dir, MediaCache::curl('MGDKit/1.0 (shopware6)')) : null;
    }

    private function recommendations(): ?Recommendations
    {
        try {
            return new Recommendations($this->http ?? new CurlTransport('MGDKit/1.0 (shopware6)'), $this->store('mgdKitRecommendations'), 'shopware6', $this->catalogSlug, $this->fallback);
        } catch (\Throwable) {
            return null;
        }
    }

    // neuer Schlüssel, alter Wert aus <Tech>.config wird 1x übernommen und dort gelöscht
    private function store(string $name): CallbackStore
    {
        $key = $this->stateKey($name);
        $old = $this->configKey($name);
        $shop = $this->shop;

        return new CallbackStore(static function () use ($shop, $key, $old): mixed {
            $v = $shop->get($key);
            if ($v !== null && $v !== '') {
                return $v;
            }
            $legacy = $shop->get($old);
            if (is_string($legacy) && $legacy !== '') {
                $shop->set($key, $legacy);
                $shop->set($old, null);
            }

            return $legacy;
        }, static function (string $v) use ($shop, $key): void {
            $shop->set($key, $v);
        });
    }
}
