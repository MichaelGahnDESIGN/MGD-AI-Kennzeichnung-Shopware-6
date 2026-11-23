<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Kit\Platform\Shopware;

use MGDAIImageLabels\Kit\Install\{InstallClient, Store};
use MGDAIImageLabels\Kit\Update\UpdateInfo;

// Update-Stand fürs Shopware-Dashboard: Update-JSON über den Install-Client (zählt mit), 12 Std Cache, bei Fehler 30 Min
final class UpdateService
{
    public const CACHE_OK = 43200;
    public const CACHE_FAIL = 1800;

    /** @var \Closure():int */
    private \Closure $clock;

    public function __construct(
        private readonly ?InstallClient $client,
        private readonly Store $cache,
        private readonly string $product,
        private readonly string $rootDir,
        ?\Closure $clock = null,
    ) {
        $this->clock = $clock ?? static fn (): int => time();
    }

    /** null = Server weg, Datei kaputt oder root_dir passt nicht */
    public function info(bool $fresh = false): ?UpdateInfo
    {
        try {
            $now = ($this->clock)();
            $st = $this->load();
            $raw = $st['raw'] ?? null;
            if ($fresh || !is_string($raw) || (int) ($st['until'] ?? 0) <= $now) {
                $raw = (string) ($this->client?->fetchUpdates() ?? '');
                $ok = $this->parse($raw) !== null;
                $this->save(['raw' => $ok ? $raw : '', 'until' => $now + ($ok ? self::CACHE_OK : self::CACHE_FAIL)]);
            }

            return $this->parse($raw);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Für die Admin-Seite, nur geprüfte Werte
     * @return array{available:bool,installed:string,newest:string,newer:bool,downloadUrl:string,changelog:string,notice:?array{text:string,url:string}}
     */
    public function status(string $installed, string $today, bool $fresh = false): array
    {
        $u = $this->info($fresh);
        $n = $u?->notice;
        $show = $n !== null && $n->shouldShow($installed, $today);

        return [
            'available' => $u !== null,
            'installed' => $installed,
            'newest' => $u?->version ?? '',
            'newer' => $u !== null && $u->isNewerThan($installed),
            'downloadUrl' => $u?->downloadUrl ?? '',
            'changelog' => $u?->changelog ?? '',
            'notice' => $show ? ['text' => $n->text, 'url' => $n->url] : null,
        ];
    }

    private function parse(string $raw): ?UpdateInfo
    {
        $u = UpdateInfo::parse($raw, $this->product);

        // root_dir = Plugin-Ordner in custom/plugins, sonst landet die ZIP woanders
        return $u !== null && $u->rootDir === $this->rootDir ? $u : null;
    }

    private function load(): array
    {
        try {
            return $this->cache->load();
        } catch (\Throwable) {
            return [];
        }
    }

    private function save(array $st): void
    {
        try {
            $this->cache->save($st);
        } catch (\Throwable) {
            // Cache kaputt egal, nächster Aufruf fragt halt neu
        }
    }
}
