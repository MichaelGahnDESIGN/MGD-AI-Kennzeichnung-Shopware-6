<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Setup;

use PHPUnit\Framework\TestCase;

/** Prüft die Trennung von schnellen Unit- und explizit freigegebenen DAL-Tests. */
final class IntegrationTestConfigurationTest extends TestCase
{
    /** PHPUnit entdeckt Integrationstests in einer eigenen, nicht zur Unit-Suite gehörenden Suite. */
    public function testPhpUnitDefinesSeparateIntegrationSuite(): void
    {
        $xml = simplexml_load_file(dirname(__DIR__, 3) . '/phpunit.xml.dist');
        self::assertNotFalse($xml);

        $suites = [];
        foreach ($xml->testsuites->testsuite as $suite) {
            $name = (string) $suite['name'];
            $suites[$name] = [];
            foreach ($suite->directory as $directory) {
                $suites[$name][] = (string) $directory;
            }
        }

        self::assertSame(['tests/Unit', 'tests/Storefront'], $suites['unit']);
        self::assertSame(['tests/Integration'], $suites['integration']);
    }

    /** Composer erzwingt bei der Integrationssuite, dass ein Skip nicht als Erfolg gilt. */
    public function testComposerDefinesFailOnSkippedIntegrationCommand(): void
    {
        $composerJson = file_get_contents(dirname(__DIR__, 3) . '/composer.json');
        self::assertNotFalse($composerJson);
        $composer = json_decode($composerJson, true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($composer);
        $scripts = $composer['scripts'] ?? null;
        self::assertIsArray($scripts);

        self::assertSame(
            'phpunit --testsuite integration --fail-on-skipped',
            $scripts['test:integration'] ?? null,
        );
    }
}
