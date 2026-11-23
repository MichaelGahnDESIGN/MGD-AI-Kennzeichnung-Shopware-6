<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Kit\Platform\Shopware;

use MGDAIImageLabels\Kit\Update\UpdateInfo;

// Knopf "Jetzt aktualisieren" (Plan: Shopware so einfach wie möglich). Läuft nur auf Klick, nie automatisch
// Schritt 1 prepare(): ZIP von GitHub laden, Größe + SHA-256 + Inhalt prüfen, über Shopware entpacken
// Schritt 2 finish(): eigene Anfrage, damit Shopware den neuen Code lädt > Lifecycle updatePlugin
// Bei jedem Zweifel abbrechen + Rückfall: Download-Link mit 3 Schritten
final class PluginUpdater
{
    public const MAX_HOPS = 3;
    // github.com leitet auf die Asset-Hosts weiter, mehr nicht (wie WordPress-Updater)
    public const HOSTS = ['github.com', 'objects.githubusercontent.com', 'release-assets.githubusercontent.com'];
    public const FALLBACK_URL = 'https://theme.michael-gahn.de/plugins';
    public const STEPS = [
        'Lade die ZIP-Datei über den Link herunter.',
        'Öffne in Shopware „Erweiterungen > Meine Erweiterungen“ und klicke auf „Erweiterung hochladen“. Wähle die ZIP-Datei aus.',
        'Klicke danach beim Plugin auf „Aktualisieren“.',
    ];

    /** @var \Closure():string */
    private \Closure $tempFile;

    public function __construct(
        private readonly ShopwareApi $shop,
        private readonly UpdateService $updates,
        private readonly Download $http,
        private readonly string $technicalName,
        private readonly string $rootDir,
        ?\Closure $tempFile = null,
    ) {
        $this->tempFile = $tempFile ?? static fn (): string => (string) tempnam(sys_get_temp_dir(), 'mgdkit');
    }

    /** darf der Benutzer + der Shop überhaupt? Für die Anzeige des Knopfs */
    public function canUpdate(object $context): bool
    {
        return Acl::allowed($context, Acl::PLUGIN) && $this->shop->runtimeAllowed();
    }

    /** @return array<string,mixed> */
    public function prepare(object $context): array
    {
        if (!Acl::allowed($context, Acl::PLUGIN)) {
            return self::denied();
        }
        $u = $this->updates->info(true);
        $err = $this->precheck($context, $u);
        if ($err !== null) {
            return $err;
        }
        $p = (array) $this->shop->plugin($this->technicalName, $context);
        if (!$u->isNewerThan((string) ($p['version'] ?? ''))) {
            return ['ok' => true, 'step' => 'current', 'message' => 'Du hast schon die aktuelle Version.'];
        }
        $tmp = ($this->tempFile)();
        try {
            if ($tmp === '' || !$this->fetch($u, $tmp)) {
                return self::fail('Der Download von GitHub hat nicht geklappt oder kam von einer nicht erlaubten Adresse. Es wurde nichts installiert.', $u);
            }
            if (!self::verify($tmp, $u) || !ZipCheck::ok($tmp, $this->rootDir, $this->technicalName, $u->version)) {
                return self::fail('Die Prüfung der ZIP-Datei ist fehlgeschlagen (Prüfsumme oder Inhalt passt nicht). Es wurde nichts installiert.', $u);
            }
            try {
                $this->shop->extract($tmp, $context);
            } catch (\Throwable) {
                return self::fail('Shopware konnte die neue Version nicht einspielen. Oft fehlen Schreibrechte im Ordner custom/plugins.', $u);
            }
        } finally {
            if ($tmp !== '' && is_file($tmp)) {
                @unlink($tmp);
            }
        }

        return ['ok' => true, 'step' => 'uploaded', 'version' => $u->version, 'message' => 'Version ' . $u->version . ' ist eingespielt. Jetzt wird das Plugin aktualisiert.'];
    }

    /** @return array<string,mixed> */
    public function finish(object $context): array
    {
        if (!Acl::allowed($context, Acl::PLUGIN)) {
            return self::denied();
        }
        $u = $this->updates->info();
        $err = $this->precheck($context, $u);
        if ($err !== null) {
            return $err;
        }
        $p = (array) $this->shop->plugin($this->technicalName, $context);
        if (($p['version'] ?? '') === $u->version) {
            return self::done($u);
        }
        // Shopware muss genau die geprüfte Version als neu kennen, sonst nix anfassen
        if (($p['upgrade'] ?? null) !== $u->version) {
            return self::fail('Die neue Version ist noch nicht eingespielt. Bitte klicke noch einmal auf „Jetzt aktualisieren“.', $u);
        }
        try {
            $this->shop->update($this->technicalName, $context);
        } catch (\Throwable) {
            return self::fail('Shopware konnte das Plugin nicht aktualisieren.', $u);
        }

        return self::done($u);
    }

    public static function allowedUrl(mixed $url): bool
    {
        $p = is_string($url) && strlen($url) <= 2000 ? parse_url($url) : false;

        return is_array($p) && ($p['scheme'] ?? '') === 'https' && in_array($p['host'] ?? '', self::HOSTS, true)
            && !isset($p['user']) && !isset($p['pass']) && ($p['port'] ?? 443) === 443;
    }

    public static function verify(string $file, UpdateInfo $u): bool
    {
        clearstatcache(true, $file);

        return is_file($file) && filesize($file) === $u->size && hash_equals($u->sha256, (string) hash_file('sha256', $file));
    }

    /** @return array<string,mixed>|null null = alles klar */
    private function precheck(object $context, ?UpdateInfo $u): ?array
    {
        if (!$this->shop->runtimeAllowed()) {
            return self::fail('In diesem Shop ist das Verwalten von Erweiterungen im Admin abgeschaltet.', $u);
        }
        if ($u === null) {
            return self::fail('Die Update-Daten von theme.michael-gahn.de fehlen gerade oder passen nicht zu diesem Plugin.', null);
        }
        $p = $this->shop->plugin($this->technicalName, $context);
        // nur unser Plugin, installiert, in custom/plugins und nicht per Composer verwaltet
        if ($p === null || !$p['installed'] || $p['composer'] || !str_starts_with($p['path'], 'custom/plugins/') || $u->rootDir !== $this->rootDir) {
            return self::fail('Dieses Plugin kann hier nicht automatisch aktualisiert werden.', $u);
        }

        return null;
    }

    // Weiterleitungen selbst folgen, jeder Hop muss auf die Host-Liste
    private function fetch(UpdateInfo $u, string $tmp): bool
    {
        $url = $u->downloadUrl;
        for ($hop = 0; $hop <= self::MAX_HOPS; ++$hop) {
            if (!self::allowedUrl($url)) {
                return false;
            }
            try {
                $r = $this->http->get($url, $tmp, $u->size);
            } catch (\Throwable) {
                return false;
            }
            $code = (int) ($r['status'] ?? 0);
            if ($code === 200) {
                return true;
            }
            if (!in_array($code, [301, 302, 303, 307, 308], true)) {
                return false;
            }
            $url = $r['location'] ?? '';
        }

        return false;
    }

    private static function done(UpdateInfo $u): array
    {
        return ['ok' => true, 'step' => 'done', 'version' => $u->version, 'message' => 'Fertig. Das Plugin ist jetzt auf Version ' . $u->version . '. Bitte lade die Seite neu.'];
    }

    private static function denied(): array
    {
        return ['ok' => false, 'denied' => true, 'message' => 'Dafür fehlen dir die Rechte. Du brauchst die Rechte zum Hochladen und Verwalten von Erweiterungen.'];
    }

    /** Rückfall immer mit Link + 3 Schritten */
    public static function fail(string $message, ?UpdateInfo $u): array
    {
        return [
            'ok' => false,
            'message' => $message,
            'fallback' => ['url' => $u?->downloadUrl ?? self::FALLBACK_URL, 'steps' => self::STEPS],
        ];
    }
}
