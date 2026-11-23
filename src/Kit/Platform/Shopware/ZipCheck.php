<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Kit\Platform\Shopware;

// ZIP vor dem Einspielen anschauen: alles unter <root_dir>/, kein .., composer.json passt zu Plugin + Version
// Shopware prüft beim Entpacken auch nochmal, doppelt hält besser
final class ZipCheck
{
    public const MAX_FILES = 5000;

    public static function ok(string $file, string $rootDir, string $technicalName, string $version): bool
    {
        if (!class_exists(\ZipArchive::class) || !is_file($file)) {
            return false;
        }
        $z = new \ZipArchive();
        if ($z->open($file, \ZipArchive::RDONLY) !== true) {
            return false;
        }
        try {
            return self::entriesOk($z, $rootDir) && self::composerOk($z, $rootDir, $technicalName, $version);
        } catch (\Throwable) {
            return false;
        } finally {
            $z->close();
        }
    }

    private static function entriesOk(\ZipArchive $z, string $rootDir): bool
    {
        $n = $z->numFiles;
        if ($n < 1 || $n > self::MAX_FILES) {
            return false;
        }
        for ($i = 0; $i < $n; ++$i) {
            $name = $z->getNameIndex($i);
            if (!is_string($name) || !str_starts_with($name, $rootDir . '/') || str_contains($name, '\\')
                || preg_match('#(^|/)\.\.(/|$)#', $name) === 1 || str_contains($name, "\0")) {
                return false;
            }
        }

        return true;
    }

    private static function composerOk(\ZipArchive $z, string $rootDir, string $technicalName, string $version): bool
    {
        $raw = $z->getFromName($rootDir . '/composer.json');
        $c = is_string($raw) && strlen($raw) < 65536 ? json_decode($raw, true, 16) : null;
        if (!is_array($c) || ($c['version'] ?? null) !== $version) {
            return false;
        }
        $class = is_array($c['extra'] ?? null) ? ($c['extra']['shopware-plugin-class'] ?? null) : null;

        return is_string($class) && basename(str_replace('\\', '/', $class)) === $technicalName;
    }
}
