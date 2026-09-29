<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Update;

use Composer\IO\NullIO;
use MGDAIImageLabels\MGDAIImageLabels;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Plugin\PluginService;

/** Ausschließlich Administration und ScheduledTask rufen diesen Dienst auf. */
final class GitHubReleaseUpdater
{
    public function __construct(private readonly GitHubClient $client, private readonly AtomicInstaller $installer, private readonly PluginService $plugins, private readonly string $projectDirectory) {}

    /** @return array{updateAvailable: bool, prepared: bool, currentVersion: string, latestVersion: ?string} */
    public function checkAndPrepare(Context $context): array
    {
        $classFile = (new \ReflectionClass(MGDAIImageLabels::class))->getFileName();
        if (!is_string($classFile)) { throw new \RuntimeException('Plugin-Pfad ist nicht verfügbar.'); }
        $plugin = dirname($classFile);
        // Auch Composer-Installationen werden nicht unbemerkt überschrieben.
        $expected = $this->projectDirectory . '/custom/plugins/MGDAIImageLabels';
        if (realpath($plugin) !== realpath($expected)) { throw new \RuntimeException('GitHub-Updates erfordern die ZIP-Installation in custom/plugins/MGDAIImageLabels.'); }
        $composer = json_decode((string) file_get_contents($plugin . '/composer.json'), true, 32, JSON_THROW_ON_ERROR);
        if (!is_array($composer)) { throw new \RuntimeException('Installierte composer.json ist ungültig.'); }
        $current = $composer['version'] ?? '';
        if (!is_string($current) || !preg_match('/^\d+\.\d+\.\d+$/D', $current)) { throw new \RuntimeException('Installierte Version ist ungültig.'); }
        $release = $this->client->latest();
        $result = ['updateAvailable' => false, 'prepared' => false, 'currentVersion' => $current, 'latestVersion' => null];
        if ($release === null) { return $result; }
        $tag = $release['tag_name'] ?? '';
        if (is_string($tag) && preg_match('/^v?(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)$/D', $tag)) {
            $result['latestVersion'] = ltrim($tag, 'v');
            if (!version_compare($result['latestVersion'], $current, '>')) { return $result; }
        }
        $metadata = ReleaseMetadata::fromGitHub($release, $current);
        $private = $this->projectDirectory . '/var/mgd-ai-image-labels';
        foreach ([$this->projectDirectory . '/var', $private] as $directory) {
            if (is_link($directory)) { throw new \RuntimeException('Privates Update-Verzeichnis darf kein Symlink sein.'); }
            if (!is_dir($directory) && !mkdir($directory, 0o700)) { throw new \RuntimeException('Privates Update-Verzeichnis kann nicht erstellt werden.'); }
        }
        chmod($private, 0o700);
        $download = $private . '/download-' . bin2hex(random_bytes(12)) . '.zip';
        try {
            $this->client->download($metadata, $download);
            $this->installer->install($download, $metadata->version, $plugin, $private, function () use ($context): void {
                $errors = $this->plugins->refreshPlugins($context, new NullIO());
                if (count($errors) > 0) { throw new \RuntimeException('Shopware konnte die vorbereitete Plugin-Liste nicht aktualisieren.'); }
            });
        } finally { if (is_file($download)) { unlink($download); } }
        return ['updateAvailable' => true, 'prepared' => true, 'currentVersion' => $current, 'latestVersion' => $metadata->version];
    }
}
