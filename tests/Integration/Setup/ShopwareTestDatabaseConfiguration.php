<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Integration\Setup;

/**
 * Prüft die explizite Datenbankfreigabe, bevor Shopwares TestBootstrapper läuft.
 *
 * Fehlertexte enthalten absichtlich weder URL noch Zugangsdaten, damit Secrets
 * auch in CI-Ausgaben und Fehlerprotokollen geschützt bleiben.
 */
final readonly class ShopwareTestDatabaseConfiguration
{
    private function __construct()
    {
    }

    public static function validate(?string $databaseUrl): string
    {
        $databaseUrl = is_string($databaseUrl) ? trim($databaseUrl) : '';
        $parts = $databaseUrl !== '' ? parse_url($databaseUrl) : false;

        if (!is_array($parts)) {
            throw self::unsafeDatabaseException();
        }

        $scheme = $parts['scheme'] ?? null;
        $host = $parts['host'] ?? null;
        $path = $parts['path'] ?? null;
        if ($scheme !== 'mysql' || !is_string($host) || $host === '' || !is_string($path)) {
            throw self::unsafeDatabaseException();
        }

        $databaseName = rawurldecode(ltrim($path, '/'));
        if (
            $databaseName === ''
            || str_contains($databaseName, '/')
            || preg_match('/(?:^|[_-])test(?:ing)?(?:$|[_-])/i', $databaseName) !== 1
        ) {
            throw self::unsafeDatabaseException();
        }

        return $databaseUrl;
    }

    /** Erzeugt eine statische Ausnahme ohne versehentliche Ausgabe von Secrets. */
    private static function unsafeDatabaseException(): \RuntimeException
    {
        return new \RuntimeException(
            'MGD_SHOPWARE_TEST_DATABASE_URL muss auf eine isolierte MySQL-Testdatenbank mit „test“ oder „testing“ im Datenbanknamen zeigen.'
        );
    }
}
