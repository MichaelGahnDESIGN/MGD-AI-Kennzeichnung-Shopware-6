<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Update;

/** Redirections werden einzeln vor jeder Verbindung geprüft; fremde Hosts bleiben unerreichbar. */
final class DownloadPolicy
{
    public function allowedRedirect(string $url): bool
    {
        $parts = parse_url($url);
        return is_array($parts) && ($parts['scheme'] ?? '') === 'https'
            && !isset($parts['user']) && !isset($parts['pass']) && !isset($parts['fragment'])
            && (!isset($parts['port']) || $parts['port'] === 443)
            && in_array($parts['host'] ?? '', ['github.com', 'release-assets.githubusercontent.com', 'objects.githubusercontent.com', 'github-releases.githubusercontent.com'], true);
    }

    public function verify(string $file, ReleaseMetadata $release): void
    {
        clearstatcache(true, $file);
        if (!is_file($file) || filesize($file) !== $release->size || !hash_equals($release->sha256, (string) hash_file('sha256', $file))) {
            throw new \RuntimeException('Download ist unvollständig oder stimmt nicht mit dem SHA256-Digest des Release-Assets überein.');
        }
    }
}
