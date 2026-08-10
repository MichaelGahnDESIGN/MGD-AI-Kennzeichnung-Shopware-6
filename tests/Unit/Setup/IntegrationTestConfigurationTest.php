<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Setup;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

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

        self::assertSame([
            '@php tests/Integration/preflight.php',
            'phpunit --testsuite integration --fail-on-skipped',
        ], $scripts['test:integration'] ?? null);
    }

    /** Der geplante CMS-Test nutzt denselben harten Datenbank- und Transaktionsvertrag. */
    public function testCmsResolverIntegrationTestIsDiscoverableAndSafelyBootstrapped(): void
    {
        $testFile = dirname(__DIR__, 2) . '/Integration/Cms/BackgroundImageCmsElementResolverTest.php';
        self::assertFileExists($testFile);
        $source = file_get_contents($testFile);
        self::assertIsString($source);

        self::assertStringContainsString('use IntegrationTestBehaviour;', $source);
        self::assertStringContainsString("MGD_SHOPWARE_INTEGRATION_TESTS", $source);
        self::assertStringContainsString('ShopwareTestDatabaseConfiguration::validate', $source);
        self::assertStringContainsString('->setLoadEnvFile(false)', $source);
        self::assertStringContainsString('->setDatabaseUrl($validatedDatabaseUrl)', $source);
        self::assertStringContainsString("->addCallingPlugin(dirname(__DIR__, 3) . '/composer.json')", $source);
        self::assertStringContainsString('->setForceInstallPlugins(true)', $source);
        self::assertStringContainsString("self::getContainer()->get(BackgroundImageCmsElementResolver::class)", $source);
        self::assertStringContainsString("self::getContainer()->get('media.repository')", $source);
        self::assertStringContainsString("'mimeType' => 'application/pdf'", $source);
        self::assertStringContainsString('testRealPdfMediaIsRejectedDuringEnrichment', $source);

        $pluginPosition = strpos($source, '->addCallingPlugin(');
        $bootstrapPosition = strpos($source, '->bootstrap();');
        self::assertIsInt($pluginPosition);
        self::assertIsInt($bootstrapPosition);
        self::assertLessThan($bootstrapPosition, $pluginPosition);
    }

    /** Ohne ausdrückliche Freigabe beendet der vorgeschaltete Prozess den DAL-Test verständlich mit Fehler. */
    public function testIntegrationPreflightFailsWithoutExplicitPermission(): void
    {
        $process = $this->runPreflight('0');

        self::assertSame(1, $process->getExitCode());
        self::assertStringContainsString('MGD_SHOPWARE_INTEGRATION_TESTS=1', $process->getErrorOutput());
        self::assertStringContainsString('isolierte MySQL-Testdatenbank', $process->getErrorOutput());
    }

    /** Mit ausdrücklicher Freigabe reicht der Preflight an PHPUnit weiter. */
    public function testIntegrationPreflightAcceptsExplicitPermission(): void
    {
        $process = $this->runPreflight('1');

        self::assertSame(0, $process->getExitCode());
        self::assertSame('', $process->getErrorOutput());
    }

    /** Startet nur das kleine sicherheitsrelevante Preflight-Skript in einem getrennten PHP-Prozess. */
    private function runPreflight(string $permission): Process
    {
        $projectDirectory = dirname(__DIR__, 3);
        $process = new Process(
            [PHP_BINARY, $projectDirectory . '/tests/Integration/preflight.php'],
            $projectDirectory,
            ['MGD_SHOPWARE_INTEGRATION_TESTS' => $permission],
        );
        $process->run();

        return $process;
    }
}
