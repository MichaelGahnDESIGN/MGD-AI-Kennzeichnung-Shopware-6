<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Update;

/** Bereitet vollständig vor und tauscht Verzeichnisse per Rename auf demselben Dateisystem aus. */
final class AtomicInstaller
{
    public function __construct(private readonly ArchiveValidator $validator) {}

    /**
     * Das Rückfallverzeichnis bleibt bewusst erhalten; ein Administrator kann es gesichert prüfen.
     * Der Callback aktualisiert nur die Plugin-Liste, nicht den Shopware-Update-Lebenszyklus.
     */
    public function install(string $archive, string $version, string $pluginDirectory, string $privateDirectory, callable $refresh): void
    {
        $plugin = realpath($pluginDirectory);
        if ($plugin === false || is_link($pluginDirectory) || basename($plugin) !== 'MGDAIImageLabels'
            || basename(dirname($plugin)) !== 'plugins' || basename(dirname($plugin, 2)) !== 'custom') {
            throw new \RuntimeException('Updates sind nur für das lokale custom/plugins/MGDAIImageLabels-Verzeichnis zulässig.');
        }
        $project = dirname($plugin, 3);
        if ($privateDirectory !== dirname($pluginDirectory, 3) . '/var/mgd-ai-image-labels' && $privateDirectory !== $project . '/var/mgd-ai-image-labels') { throw new \RuntimeException('Staging muss im festen privaten var-Verzeichnis liegen.'); }
        $privateDirectory = $project . '/var/mgd-ai-image-labels';
        $this->makePrivateDirectory($project . '/var');
        $this->makePrivateDirectory($privateDirectory);
        $stagingStat = stat($privateDirectory);
        $pluginStat = stat(dirname($plugin));
        if ($stagingStat === false || $pluginStat === false || $stagingStat['dev'] !== $pluginStat['dev']) { throw new \RuntimeException('Staging und Plugin müssen auf demselben Dateisystem liegen.'); }
        $lockPath = $privateDirectory . '/update.lock';
        if (is_link($lockPath)) { throw new \RuntimeException('Ungültige Update-Sperre.'); }
        $lock = fopen($lockPath, 'c');
        if ($lock === false) { throw new \RuntimeException('Update-Sperre kann nicht erstellt werden.'); }
        chmod($lockPath, 0o600);
        if (!flock($lock, LOCK_EX | LOCK_NB)) { fclose($lock);
            throw new \RuntimeException('Ein Update läuft bereits.'); }
        $stage = $privateDirectory . '/stage-' . bin2hex(random_bytes(12));
        $backup = $privateDirectory . '/backup-' . gmdate('Ymd-His') . '-' . bin2hex(random_bytes(6));
        $swapped = false;
        $backedUp = false;
        try {
            $current = json_decode((string) file_get_contents($plugin . '/composer.json'), true, 32, JSON_THROW_ON_ERROR);
            if (!is_array($current)) { throw new \RuntimeException('Installierte composer.json ist ungültig.'); }
            if (!is_string($current['version'] ?? null) || !version_compare($version, $current['version'], '>')) { throw new \RuntimeException('Auf der Platte liegt bereits diese oder eine höhere Version.'); }
            $files = $this->validator->validate($archive, $version);
            $this->makePrivateDirectory($stage);
            $zip = new \ZipArchive();
            if ($zip->open($archive) !== true) { throw new \RuntimeException('Geprüftes ZIP kann nicht erneut geöffnet werden.'); }
            try {
                // Vor jeder Änderung aktiver Dateien: auch neue/entfernte Abhängigkeiten abweisen.
                $candidate = json_decode((string) $zip->getFromName('MGDAIImageLabels/composer.json'), true, 32, JSON_THROW_ON_ERROR);
                if (!is_array($candidate)) { throw new \RuntimeException('Release-composer.json ist ungültig.'); }
                RequirementPolicy::assertIdentical($current['require'] ?? null, $candidate['require'] ?? null);
                foreach ($files as $file) {
                    $destination = $stage . '/' . substr($file, strlen('MGDAIImageLabels/'));
                    if (!is_dir(dirname($destination)) && !mkdir(dirname($destination), 0o755, true)) { throw new \RuntimeException('Staging-Unterordner kann nicht erstellt werden.'); }
                    $content = $zip->getFromName($file);
                    if (!is_string($content) || file_put_contents($destination, $content, LOCK_EX) !== strlen($content)) { throw new \RuntimeException('ZIP-Eintrag kann nicht vollständig geschrieben werden.'); }
                    chmod($destination, 0o644);
                }
            } finally { $zip->close(); }
            // Einzelne Renames sind atomar; zwischen beiden besteht eine kurze Umschaltlücke.
            if (!rename($plugin, $backup)) { throw new \RuntimeException('Original kann nicht in die Sicherung verschoben werden.'); }
            $backedUp = true;
            chmod($stage, 0o755);
            if (!rename($stage, $plugin)) { throw new \RuntimeException('Vorbereitete Version kann nicht eingesetzt werden.'); }
            $swapped = true;
            $refresh();
            // Lang laufende Worker sollen keine alten PHP-Dateien aus dem Opcache weiterverwenden.
            if (function_exists('opcache_invalidate')) {
                foreach ($files as $file) { if (str_ends_with($file, '.php')) { opcache_invalidate($project . '/custom/plugins/' . $file, true); } }
            }
        } catch (\Throwable $error) {
            if ($swapped && !rename($plugin, $stage)) { throw new \RuntimeException('Rückfall blockiert: neue Version konnte nicht entfernt werden. Sicherung: ' . $backup, 0, $error); }
            if ($backedUp) {
                if (!rename($backup, $plugin)) { throw new \RuntimeException('Rückfall blockiert: Original liegt in ' . $backup, 0, $error); }
                try { $refresh(); } catch (\Throwable) { /* Originaldateien sind wiederhergestellt; ursprünglichen Fehler beibehalten. */ }
            }
            throw $error;
        } finally {
            if (is_dir($stage)) { $this->removeStage($stage); }
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function makePrivateDirectory(string $directory): void
    {
        if (is_link($directory)) { throw new \RuntimeException('Private Update-Verzeichnisse dürfen keine Symlinks sein.'); }
        if (!is_dir($directory) && !mkdir($directory, 0o700)) { throw new \RuntimeException('Privates Update-Verzeichnis kann nicht erstellt werden.'); }
        if (!is_writable($directory)) { throw new \RuntimeException('Privates Update-Verzeichnis ist nicht beschreibbar.'); }
        if (basename($directory) !== 'var') { chmod($directory, 0o700); }
    }

    /** Ausschließlich das frisch erzeugte, vom Installer kontrollierte Staging wird gelöscht. */
    private function removeStage(string $stage): void
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($stage, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo) { continue; }
            $file->isDir() && !$file->isLink() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($stage);
    }
}
