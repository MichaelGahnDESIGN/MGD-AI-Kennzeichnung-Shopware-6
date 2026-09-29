<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Update;

/** GitHub-Antworten werden vollständig geprüft, bevor eine Datei heruntergeladen wird. */
final readonly class ReleaseMetadata
{
    public const REPOSITORY = 'MichaelGahnDESIGN/MGD-AI-Kennzeichnung-Shopware-6';
    public const MAX_DOWNLOAD_BYTES = 20_000_000;

    private function __construct(public string $version, public string $url, public string $sha256, public int $size) {}

    /** @param array<string, mixed> $release */
    public static function fromGitHub(array $release, string $currentVersion): self
    {
        $tag = $release['tag_name'] ?? '';
        if (!is_string($tag) || !preg_match('/^v?(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)$/D', $tag)
            || ($release['draft'] ?? true) !== false || ($release['prerelease'] ?? true) !== false) {
            throw new \RuntimeException('Nur veröffentlichte stabile Semver-Releases sind erlaubt.');
        }
        $version = ltrim($tag, 'v');
        if (!version_compare($version, $currentVersion, '>')) { throw new \RuntimeException('Keine neuere stabile Version verfügbar.'); }
        $assetName = 'MGDAIImageLabels-' . $version . '.zip';
        $matches = array_values(array_filter(is_array($release['assets'] ?? null) ? $release['assets'] : [], static fn(mixed $asset): bool => is_array($asset) && ($asset['name'] ?? '') === $assetName));
        if (count($matches) !== 1) { throw new \RuntimeException('Genau ein versionsgebundenes MGDAIImageLabels-ZIP wird benötigt.'); }
        $asset = $matches[0];
        $expected = 'https://github.com/' . self::REPOSITORY . '/releases/download/' . $tag . '/' . $assetName;
        if (($asset['browser_download_url'] ?? '') !== $expected || !is_int($asset['size'] ?? null)
            || $asset['size'] <= 0 || $asset['size'] > self::MAX_DOWNLOAD_BYTES
            || !is_string($asset['digest'] ?? null) || !preg_match('/^sha256:([a-f0-9]{64})$/D', $asset['digest'], $digest)) {
            throw new \RuntimeException('Release-Asset besitzt keine zulässige Adresse, Größe oder SHA256-Prüfsumme.');
        }
        return new self($version, $expected, $digest[1], $asset['size']);
    }
}
