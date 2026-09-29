<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Update;

/** Kein extractTo: Jeder ZIP-Eintrag wird vor dem Schreiben unabhängig kontrolliert. */
final class ArchiveValidator
{
    public const MAX_ENTRIES = 500;
    public const MAX_UNCOMPRESSED_BYTES = 40_000_000;
    public const MAX_FILE_BYTES = 10_000_000;

    public static function allowedPath(string $path): bool
    {
        if (str_contains($path, '\\') || str_contains($path, "\0") || preg_match('/[\x00-\x1f\x7f]/', $path)
            || str_contains($path, ':') || str_contains($path, '//')) { return false; }
        $segments = explode('/', rtrim($path, '/'));
        foreach ($segments as $segment) {
            if ($segment === '.' || $segment === '..' || $segment === '') { return false; }
            if (str_starts_with($segment, '.') && !($segment === '.vite' && str_starts_with($path, 'MGDAIImageLabels/src/Resources/public/administration/.vite/'))) { return false; }
        }
        if (in_array($path, ['MGDAIImageLabels/composer.json', 'MGDAIImageLabels/LICENSE', 'MGDAIImageLabels/README.md', 'MGDAIImageLabels/README.en.md', 'MGDAIImageLabels/SECURITY.md', 'MGDAIImageLabels/CONTRIBUTING.md', 'MGDAIImageLabels/CHANGELOG.md'], true)) { return true; }
        if (in_array($path, ['MGDAIImageLabels/', 'MGDAIImageLabels/src/', 'MGDAIImageLabels/Dokumentation/'], true)) { return true; }
        if (str_starts_with($path, 'MGDAIImageLabels/Dokumentation/')) { return str_ends_with($path, '/') || (bool) preg_match('/\.md$/D', $path); }
        if (!str_starts_with($path, 'MGDAIImageLabels/src/')) { return false; }
        if (str_ends_with($path, '/')) { return true; }
        return (bool) preg_match('/\.(php|xml|twig|json|js|css|scss|map|svg|png|jpe?g|webp)$/D', $path);
    }

    /** @return list<string> Kontrollierte relative Dateinamen innerhalb MGDAIImageLabels. */
    public function validate(string $path, string $version): array
    {
        if (!is_file($path) || filesize($path) > ReleaseMetadata::MAX_DOWNLOAD_BYTES) { throw new \RuntimeException('ZIP überschreitet die Downloadgrenze.'); }
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) { throw new \RuntimeException('ZIP kann nicht geöffnet werden.'); }
        try {
            if ($zip->numFiles === 0 || $zip->numFiles > self::MAX_ENTRIES) { throw new \RuntimeException('ZIP enthält zu viele oder keine Einträge.'); }
            $total = 0;
            $files = [];
            $seen = [];
            for ($index = 0; $index < $zip->numFiles; ++$index) {
                $stat = $zip->statIndex($index);
                if ($stat === false) { throw new \RuntimeException('ZIP-Verzeichnis ist beschädigt.'); }
                $name = $stat['name'];
                if (!self::allowedPath($name) || isset($seen[strtolower(rtrim($name, '/'))])) { throw new \RuntimeException('ZIP enthält einen unerlaubten oder doppelten Pfad.'); }
                $seen[strtolower(rtrim($name, '/'))] = true;
                if (!$zip->getExternalAttributesIndex($index, $opsys, $attributes)) { throw new \RuntimeException('ZIP-Dateityp kann nicht geprüft werden.'); }
                if (!is_int($attributes)) { throw new \RuntimeException('ZIP-Dateirechte sind ungültig.'); }
                $type = ($attributes >> 16) & 0o170000;
                if ($opsys === \ZipArchive::OPSYS_UNIX && !in_array($type, [0, 0o100000, 0o040000], true)) { throw new \RuntimeException('ZIP enthält Symlinks oder Spezialdateien.'); }
                if ($stat['encryption_method'] !== 0) { throw new \RuntimeException('Verschlüsselte ZIP-Einträge sind nicht erlaubt.'); }
                $total += $stat['size'];
                if ($stat['size'] > self::MAX_FILE_BYTES || $total > self::MAX_UNCOMPRESSED_BYTES
                    || ($stat['size'] > 100000 && $stat['size'] / max(1, $stat['comp_size']) > 200)) { throw new \RuntimeException('ZIP überschreitet Entpack- oder Kompressionsgrenzen.'); }
                if (!str_ends_with($name, '/')) { $files[] = $name; }
            }
            $composer = $zip->getFromName('MGDAIImageLabels/composer.json');
            if (!is_string($composer) || strlen($composer) > 100000) { throw new \RuntimeException('composer.json fehlt oder ist zu groß.'); }
            try { $manifest = json_decode($composer, true, 32, JSON_THROW_ON_ERROR); } catch (\JsonException $error) { throw new \RuntimeException('composer.json ist ungültig.', 0, $error); }
            if (!is_array($manifest)) { throw new \RuntimeException('composer.json muss ein Objekt enthalten.'); }
            $extra = $manifest['extra'] ?? null;
            $autoload = $manifest['autoload'] ?? null;
            $psr4 = is_array($autoload) ? ($autoload['psr-4'] ?? null) : null;
            if (!is_array($extra) || !is_array($psr4)) { throw new \RuntimeException('Plugin-Metadaten fehlen.'); }
            if (($manifest['name'] ?? '') !== 'michaelgahn-design/mgd-ai-kennzeichnung-shopware-6' || ($manifest['type'] ?? '') !== 'shopware-platform-plugin'
                || ($manifest['version'] ?? '') !== $version || ($extra['shopware-plugin-class'] ?? '') !== 'MGDAIImageLabels\\MGDAIImageLabels'
                || ($psr4['MGDAIImageLabels\\'] ?? '') !== 'src/'
                || !in_array('MGDAIImageLabels/src/MGDAIImageLabels.php', $files, true)) { throw new \RuntimeException('ZIP besitzt eine falsche Plugin-Identität oder Version.'); }
            RequirementPolicy::normalize($manifest['require'] ?? null);
            return $files;
        } finally { $zip->close(); }
    }
}
