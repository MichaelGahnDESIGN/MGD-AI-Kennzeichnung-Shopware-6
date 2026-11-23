<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Kit\Platform\Shopware;

// cURL für die Release-ZIP: nur https, keine Weiterleitung, Größe begrenzt, Fehlertexte ohne Details
final class CurlDownload implements Download
{
    public const CONNECT_TIMEOUT = 10;
    public const TIMEOUT = 300;

    public function __construct(private readonly string $userAgent)
    {
    }

    public function get(string $url, string $file, int $maxBytes): array
    {
        if (!function_exists('curl_init')) {
            throw new \RuntimeException('PHP-Erweiterung curl fehlt.');
        }
        $fh = fopen($file, 'wb');
        if ($fh === false) {
            throw new \RuntimeException('Temporäre Datei nicht schreibbar.');
        }
        $location = '';
        $bytes = 0;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT => self::TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_HEADERFUNCTION => static function ($c, string $line) use (&$location): int {
                if (stripos($line, 'location:') === 0) {
                    $location = trim(substr($line, 9));
                }

                return strlen($line);
            },
            CURLOPT_WRITEFUNCTION => static function ($c, string $chunk) use ($fh, &$bytes, $maxBytes): int {
                $bytes += strlen($chunk);
                // zu groß > abbrechen, Datei wird eh verwrofen
                if ($bytes > $maxBytes) {
                    return -1;
                }

                return (int) fwrite($fh, $chunk);
            },
        ]);
        $ok = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        fclose($fh);
        if ($ok === false) {
            throw new \RuntimeException('Download fehlgeschlagen (Code ' . curl_errno($ch) . ').');
        }

        return ['status' => $status, 'location' => $location];
    }
}
