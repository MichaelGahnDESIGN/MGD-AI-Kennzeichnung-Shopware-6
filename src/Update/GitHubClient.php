<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Update;

/** Öffentliche GitHub-Verbindungen benötigen keine Tokens. Antworten und Weiterleitungen sind begrenzt. */
final class GitHubClient
{
    public function __construct(private readonly DownloadPolicy $policy) {}

    /** @return array<string, mixed>|null null bedeutet: noch kein veröffentlichtes Release. */
    public function latest(): ?array
    {
        $body = '';
        $status = $this->request(
            'https://api.github.com/repos/' . ReleaseMetadata::REPOSITORY . '/releases/latest',
            1_000_000,
            static function (string $chunk) use (&$body): void { $body .= $chunk; },
            false
        );
        if ($status === 404) { return null; }
        if ($status !== 200) { throw new \RuntimeException('GitHub-Release-Prüfung vorübergehend nicht verfügbar.'); }
        try { $release = json_decode($body, true, 32, JSON_THROW_ON_ERROR); } catch (\JsonException $error) { throw new \RuntimeException('Ungültige GitHub-Release-Antwort.', 0, $error); }
        if (!is_array($release) || array_is_list($release)) { throw new \RuntimeException('Ungültige GitHub-Release-Metadaten.'); }
        $fields = [];
        foreach ($release as $key => $value) {
            if (!is_string($key)) { throw new \RuntimeException('Ungültiger GitHub-Release-Schlüssel.'); }
            $fields[$key] = $value;
        }
        return $fields;
    }

    public function download(ReleaseMetadata $release, string $target): void
    {
        $handle = fopen($target, 'x+b');
        if ($handle === false) { throw new \RuntimeException('Private Download-Datei kann nicht erstellt werden.'); }
        chmod($target, 0o600);
        try {
            $status = $this->request(
                $release->url,
                min($release->size, ReleaseMetadata::MAX_DOWNLOAD_BYTES),
                static function (string $chunk) use ($handle): void {
                    if (fwrite($handle, $chunk) !== strlen($chunk)) { throw new \RuntimeException('Download kann nicht vollständig gespeichert werden.'); }
                },
                true
            );
            if ($status !== 200) { throw new \RuntimeException('Release-Download vorübergehend nicht verfügbar.'); }
            fflush($handle);
        } finally { fclose($handle); }
        $this->policy->verify($target, $release);
    }

    /**
     * CURLOPT_FOLLOWLOCATION bleibt aus: HTTPS und Hostgrenzen gelten vor jedem Redirect.
     * Signierte Asset-URLs erscheinen weder in Fehlermeldungen noch im Log.
     */
    private function request(string $url, int $limit, callable $consume, bool $allowRedirects): int
    {
        for ($redirect = 0; $redirect <= 3; ++$redirect) {
            if ($allowRedirects && !$this->policy->allowedRedirect($url)) { throw new \RuntimeException('GitHub leitet auf einen nicht erlaubten Host um.'); }
            $curl = curl_init($url);
            if ($curl === false) { throw new \RuntimeException('HTTPS-Verbindung kann nicht erstellt werden.'); }
            $location = null;
            $received = 0;
            $status = 0;
            $writeFailure = false;
            curl_setopt_array($curl, [
                CURLOPT_FOLLOWLOCATION => false, CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
                CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_TIMEOUT => 60,
                CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_HTTPHEADER => ['Accept: application/vnd.github+json', 'User-Agent: MGD-AI-Image-Labels-Updater', 'X-GitHub-Api-Version: 2022-11-28'],
                CURLOPT_HEADERFUNCTION => static function ($handle, string $header) use (&$location, &$status): int {
                    if (preg_match('/^HTTP\/\S+\s+(\d+)/', $header, $match)) { $status = (int) $match[1];
                        $location = null; }
                    if (stripos($header, 'Location:') === 0) { $location = trim(substr($header, 9)); }
                    return strlen($header);
                },
                CURLOPT_WRITEFUNCTION => static function ($handle, string $chunk) use (&$received, &$writeFailure, &$status, $limit, $consume): int {
                    $received += strlen($chunk);
                    if ($received > ($status === 200 ? $limit : 100000)) { $writeFailure = true;
                        return 0; }
                    if ($status === 200) {
                        try { $consume($chunk); } catch (\Throwable) { $writeFailure = true;
                            return 0; }
                    }
                    return strlen($chunk);
                },
            ]);
            $success = curl_exec($curl);
            $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
            unset($curl); // CurlHandle wird seit PHP 8 automatisch geschlossen.
            if ($success === false || $writeFailure) { throw new \RuntimeException('HTTPS-Anfrage fehlgeschlagen oder Antwort überschreitet die Größenbegrenzung.'); }
            if (!in_array($status, [301, 302, 303, 307, 308], true)) { return $status; }
            if (!$allowRedirects || $redirect === 3 || !is_string($location)) { throw new \RuntimeException('Unzulässige GitHub-Weiterleitung.'); }
            $url = $location;
        }
        throw new \RuntimeException('Zu viele GitHub-Weiterleitungen.');
    }
}
