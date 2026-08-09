<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Setup;

use MGDAIImageLabels\Tests\Integration\Setup\ShopwareTestDatabaseConfiguration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Prüft die harte Sicherheitsgrenze vor dem Start einer echten Shopware-Testdatenbank. */
final class ShopwareTestDatabaseConfigurationTest extends TestCase
{
    /** Eine explizite MySQL-Testdatenbank wird unverändert an Shopware weitergegeben. */
    public function testAcceptsExplicitIsolatedMysqlTestDatabase(): void
    {
        $url = 'mysql://test-user:placeholder@localhost:3306/mgd_shopware_test?charset=utf8mb4';

        self::assertSame($url, ShopwareTestDatabaseConfiguration::validate($url));
    }

    /** Unsichere, leere oder nicht unterstützte Ziele werden vor jedem Bootstrap abgewiesen. */
    #[DataProvider('unsafeDatabaseUrls')]
    public function testRejectsUnsafeDatabaseUrl(?string $url): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('isolierte MySQL-Testdatenbank');

        ShopwareTestDatabaseConfiguration::validate($url);
    }

    /** @return iterable<string, array{?string}> */
    public static function unsafeDatabaseUrls(): iterable
    {
        yield 'nicht gesetzt' => [null];
        yield 'leer' => [''];
        yield 'nur Leerraum' => ['   '];
        yield 'nicht parsebar' => ['keine-url'];
        yield 'falsches Schema' => ['postgresql://test-user:placeholder@localhost/mgd_shopware_test'];
        yield 'ohne Host' => ['mysql:///mgd_shopware_test'];
        yield 'ohne Datenbank' => ['mysql://test-user:placeholder@localhost'];
        yield 'Produktionsname' => ['mysql://test-user:placeholder@localhost/mgd_shopware'];
        yield 'irreführendes contest' => ['mysql://test-user:placeholder@localhost/contest'];
        yield 'Doctrine-Override auf Produktion' => ['mysql://test-user:placeholder@localhost/mgd_shopware_test?dbname=production'];
        yield 'Doctrine-Override trotz Testname' => ['mysql://test-user:placeholder@localhost/mgd_shopware_test?dbname=another_test'];
        yield 'URL-kodierter Doctrine-Override' => ['mysql://test-user:placeholder@localhost/mgd_shopware_test?db%6Eame=production'];
        yield 'mehrfacher Doctrine-Override' => ['mysql://test-user:placeholder@localhost/mgd_shopware_test?dbname=mgd_shopware_test&dbname=production'];
    }
}
