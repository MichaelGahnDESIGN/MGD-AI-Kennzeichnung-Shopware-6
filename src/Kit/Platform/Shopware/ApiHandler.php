<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Kit\Platform\Shopware;

use MGDAIImageLabels\Kit\Install\{InstallClient, Settings};
use MGDAIImageLabels\Kit\Catalog\{CatalogItems, MediaCache, Recommendations};
use MGDAIImageLabels\Kit\License\LicenseClient;

// Logik hinter den Admin-API-Routen, ohne Symfony testbar. Antwort immer [HTTP-Status, Daten]
// Rechte werden hier nochmal geprüft (zusätzlich _acl an der Route)
final class ApiHandler
{
    // gemeinsam für alle MGD-Plugins im Shop, außerhalb jeder Plugin-Domäne
    public const COLORS_KEY = 'MgdKit.colors.mine';
    // wie Symfony JsonResponse: < > ' " & werden escaped
    public const JSON_FLAGS = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    public const ERROR = 'Das hat gerade nicht geklappt. Bitte lade die Seite neu und versuche es noch einmal.';
    public const DENIED = 'Dafür fehlen dir die Rechte.';

    public function __construct(
        private readonly string $pluginName,
        private readonly string $technicalName,
        private readonly ShopwareApi $shop,
        private readonly UpdateService $updates,
        private readonly PluginUpdater $updater,
        private readonly ?Recommendations $recommendations,
        private readonly ?InstallClient $install,
        private readonly LicenseClient $license,
        private readonly string $installSwitchKey = '',
        private readonly ?MediaCache $media = null,
    ) {
    }

    /** @return array{0:int,1:array<string,mixed>} */
    public function status(object $context, string $today): array
    {
        if (!Acl::allowed($context, Acl::READ)) {
            return self::denied();
        }

        return self::guard(function () use ($context, $today): array {
            $installed = (string) ($this->shop->plugin($this->technicalName, $context)['version'] ?? '');
            $out = [200, [
                'name' => $this->pluginName,
                'updates' => $this->updates->status($installed, $today),
                'canUpdate' => $this->updater->canUpdate($context),
                'fallbackSteps' => PluginUpdater::STEPS,
                'install' => $this->installData(),
                'license' => $this->licenseData(),
            ]];
            // Ersatz falls kein Scheduler läuft: erst NACH der Update-Prüfung (die meldet schon, setzt last_ping) > nie 2 Anfragen
            try {
                $this->install?->ping();
            } catch (\Throwable) {
            }

            return $out;
        });
    }

    /** @return array{0:int,1:array<string,mixed>} */
    public function recommendations(object $context): array
    {
        if (!Acl::allowed($context, Acl::READ)) {
            return self::denied();
        }
        if ($this->recommendations === null) {
            return [200, ['items' => [], 'source' => 'fallback', 'checkedAt' => 0]];
        }

        return self::guard(function (): array {
            $res = $this->recommendations->load();
            $m = $this->media;
            $m?->syncItems($res['items'], $res['source'] === 'fresh');
            // keine fremden Bild-URLs ans JS, nur Dateinamen aus dem eigenen Cache (Abruf über media())
            $items = CatalogItems::prepare($res['items'], $m !== null ? static fn (string $url): string => $m->local($url) : null);

            return [200, ['items' => $items, 'source' => $res['source'], 'checkedAt' => $res['checkedAt']]];
        });
    }

    /** Katalog-Bild als data:-URI aus dem Cache (Admin-Browser lädt nie von extern) @return array{0:int,1:array<string,mixed>} */
    public function media(object $context, mixed $name): array
    {
        if (!Acl::allowed($context, Acl::READ)) {
            return self::denied();
        }
        if (!is_string($name) || preg_match(MediaCache::FILE_RE, $name) !== 1) {
            return [400, ['message' => self::ERROR]];
        }

        return self::guard(function () use ($name): array {
            $data = $this->media?->dataUri($name) ?? '';

            return $data !== '' ? [200, ['data' => $data]] : [404, ['message' => self::ERROR]];
        });
    }

    /** @return array{0:int,1:array<string,mixed>} */
    public function update(object $context): array
    {
        return self::guard(fn (): array => self::result($this->updater->prepare($context)));
    }

    /** @return array{0:int,1:array<string,mixed>} */
    public function finish(object $context): array
    {
        return self::guard(fn (): array => self::result($this->updater->finish($context)));
    }

    /** @return array{0:int,1:array<string,mixed>} */
    public function installReport(object $context, mixed $enabled): array
    {
        if (!Acl::allowed($context, Acl::WRITE)) {
            return self::denied();
        }
        if (!is_bool($enabled) || $this->install === null) {
            return [400, ['message' => self::ERROR]];
        }

        return self::guard(function () use ($enabled): array {
            $this->install->setEnabled($enabled);
            // alter Schalter in der Plugin-Konfig gleich mitziehen
            if ($this->installSwitchKey !== '') {
                $this->shop->set($this->installSwitchKey, $enabled);
            }

            return [200, ['install' => $this->installData()]];
        });
    }

    /**
     * Farbwähler: $mine null = lesen, sonst speichern (nur mit Schreibrecht). Ein Schlüssel für alle MGD-Plugins
     * @return array{0:int,1:array<string,mixed>}
     */
    public function colors(object $context, mixed $mine): array
    {
        if (!Acl::allowed($context, $mine === null ? Acl::READ : Acl::WRITE)) {
            return self::denied();
        }

        return self::guard(function () use ($mine): array {
            if ($mine !== null) {
                $this->shop->set(self::COLORS_KEY, (string) json_encode(\MGDAIImageLabels\Kit\Ui\Colors::clean($mine, \MGDAIImageLabels\Kit\Ui\Colors::MAX_MINE)));
            }
            $raw = $this->shop->get(self::COLORS_KEY);
            $list = is_string($raw) ? json_decode($raw, true, 4) : null;

            return [200, ['mine' => \MGDAIImageLabels\Kit\Ui\Colors::clean(is_array($list) ? $list : [], \MGDAIImageLabels\Kit\Ui\Colors::MAX_MINE)]];
        });
    }

    /** @return array{0:int,1:array<string,mixed>} */
    public function license(object $context, mixed $action, mixed $token): array
    {
        if (!$this->license->enabled() || !in_array($action, ['save', 'remove'], true)) {
            return [400, ['message' => self::ERROR]];
        }
        if (!Acl::allowed($context, Acl::WRITE)) {
            return self::denied();
        }

        return self::guard(function () use ($action, $token): array {
            if ($action === 'remove') {
                $this->license->forget();
                $msg = 'Der Lizenzschlüssel ist entfernt.';
            } elseif ($this->license->setToken(is_string($token) ? $token : '')) {
                $msg = 'Der Lizenzschlüssel ist gespeichert.';
            } else {
                return [400, ['message' => 'Der Lizenzschlüssel sieht nicht richtig aus. Bitte kopiere ihn vollständig aus deinem Kundenkonto.']];
            }

            return [200, ['message' => $msg, 'license' => $this->licenseData()]];
        });
    }

    public static function encode(array $data): string
    {
        return (string) json_encode($data, self::JSON_FLAGS | JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    private function installData(): ?array
    {
        if ($this->install === null) {
            return null;
        }

        // Text 1:1 aus Settings (von Michael freigegeben)
        return ['enabled' => $this->install->isEnabled(), 'status' => $this->install->status(), 'label' => Settings::LABEL, 'hint' => Settings::HINT];
    }

    // Lizenz aus = gar nix rausgeben. Schlüssel nie, nur die letzten 4 Zeichen
    private function licenseData(): ?array
    {
        if (!$this->license->enabled()) {
            return null;
        }

        return ['status' => $this->license->status(), 'hasToken' => $this->license->hasToken(), 'hint' => $this->license->tokenHint(), 'pro' => $this->license->isPro()];
    }

    private static function result(array $r): array
    {
        if (($r['denied'] ?? false) === true) {
            return [403, $r];
        }

        return [200, $r];
    }

    private static function denied(): array
    {
        return [403, ['message' => self::DENIED]];
    }

    /** @param \Closure():array $fn */
    private static function guard(\Closure $fn): array
    {
        try {
            return $fn();
        } catch (\Throwable) {
            return [500, ['message' => self::ERROR]];
        }
    }
}
