<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Structure;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Schützt den menschenlesbaren Dokumentations- und Release-Vertrag.
 *
 * Das Paket wird hier wie ein fremder Download geprüft. Dadurch fällt früh
 * auf, wenn wichtige Laufzeitdateien fehlen oder Entwicklungs- und
 * Geheimnisdateien versehentlich veröffentlicht würden.
 */
#[CoversNothing]
final class DocumentationAndReleaseTest extends TestCase
{
    private const ROOT = __DIR__ . '/../..';

    /** @var list<string> */
    private const REQUIRED_DOCUMENTS = [
        'README.md',
        'README.en.md',
        'SECURITY.md',
        'CONTRIBUTING.md',
        'CHANGELOG.md',
        'LICENSE',
        'Dokumentation/Architektur.md',
        'Dokumentation/Datenschutz-und-Sicherheit.md',
        'Dokumentation/Integration-eigener-Themes.md',
        'Dokumentation/Deployment-und-Rueckfall.md',
    ];

    /** @var list<string> */
    private const REQUIRED_WIKI_DOCUMENTS = [
        'Home.md',
        'Installation-und-Updates.md',
        'Bilder-kennzeichnen.md',
        'Sprache-und-Gestaltung.md',
        'Erlebniswelten-und-Hintergrundbilder.md',
        'Themes-und-individuelle-Templates.md',
        'Rechte-und-Rollen.md',
        'Datenschutz-und-Sicherheit.md',
        'Barrierefreiheit.md',
        'Deinstallation-und-Wiederherstellung.md',
        'Fehlerbehebung.md',
        'Entwicklerarchitektur.md',
        'Tests-und-Releaseprozess.md',
        'FAQ.md',
        '_Sidebar.md',
        '_Footer.md',
    ];

    /**
     * Prüft den CI-Vertrag anhand der tatsächlich geparsten YAML-Struktur.
     *
     * Reine Text- oder Regex-Prüfungen könnten auskommentierte bzw. wirkungslose
     * Zeilen irrtümlich als aktive CI-Schritte akzeptieren. Die Strukturprüfung
     * navigiert deshalb durch Jobs, Matrix, Schritte und Bedingungen.
     */
    public function testQualityWorkflowCoversTheSupportedVersionsAndSafeReleasePath(): void
    {
        $workflowPath = self::ROOT . '/.github/workflows/quality.yml';
        self::assertFileExists($workflowPath);

        $workflow = Yaml::parseFile($workflowPath);
        self::assertIsArray($workflow);
        self::assertSame(['contents' => 'read'], $workflow['permissions'] ?? null);

        $triggers = $workflow['on'] ?? null;
        self::assertIsArray($triggers);
        self::assertArrayHasKey('push', $triggers);
        self::assertArrayHasKey('pull_request', $triggers);

        $concurrency = $workflow['concurrency'] ?? null;
        self::assertIsArray($concurrency);
        self::assertSame('${{ github.workflow }}-${{ github.ref }}', $concurrency['group'] ?? null);
        self::assertTrue($concurrency['cancel-in-progress'] ?? false);

        $jobs = $workflow['jobs'] ?? null;
        self::assertIsArray($jobs);
        $qualityJob = $jobs['quality'] ?? null;
        self::assertIsArray($qualityJob);
        self::assertSame('ubuntu-latest', $qualityJob['runs-on'] ?? null);

        $strategy = $qualityJob['strategy'] ?? null;
        self::assertIsArray($strategy);
        self::assertFalse($strategy['fail-fast'] ?? true);
        $matrix = $strategy['matrix'] ?? null;
        self::assertIsArray($matrix);
        self::assertSame(['6.6.10', '6.7'], $matrix['shopware'] ?? null);
        self::assertSame(['8.2', '8.4'], $matrix['php'] ?? null);

        $steps = $qualityJob['steps'] ?? null;
        self::assertIsArray($steps);
        self::assertSame(
            'actions/checkout@3d3c42e5aac5ba805825da76410c181273ba90b1',
            $this->workflowStep($steps, 'Quellcode sicher auschecken')['uses'] ?? null,
        );
        self::assertSame(
            'shivammathur/setup-php@bf6b4fbd49ca58e4608c9c89fba0b8d90bd2a39f',
            $this->workflowStep($steps, 'PHP einrichten')['uses'] ?? null,
        );
        self::assertSame('${{ matrix.php }}', $this->workflowStep($steps, 'PHP einrichten')['with']['php-version'] ?? null);
        self::assertSame('composer:v2', $this->workflowStep($steps, 'PHP einrichten')['with']['tools'] ?? null);
        self::assertSame(
            'actions/setup-node@249970729cb0ef3589644e2896645e5dc5ba9c38',
            $this->workflowStep($steps, 'Node.js einrichten')['uses'] ?? null,
        );
        self::assertSame('24.x', $this->workflowStep($steps, 'Node.js einrichten')['with']['node-version'] ?? null);
        self::assertSame(
            'actions/setup-go@924ae3a1cded613372ab5595356fb5720e22ba16',
            $this->workflowStep($steps, 'Go einrichten')['uses'] ?? null,
        );
        self::assertSame('1.25.x', $this->workflowStep($steps, 'Go einrichten')['with']['go-version'] ?? null);

        $dependencyStep = $this->workflowStep($steps, 'Shopware-Abhängigkeiten frisch auflösen');
        $dependencyCommand = $dependencyStep['run'] ?? null;
        self::assertIsString($dependencyCommand);
        self::assertStringContainsString('composer require --no-update', $dependencyCommand);
        self::assertStringContainsString('shopware/core:', $dependencyCommand);
        self::assertStringContainsString('shopware/storefront:', $dependencyCommand);
        self::assertStringContainsString('composer update --prefer-dist --no-interaction --no-progress --with-all-dependencies', $dependencyCommand);

        self::assertSame(
            'composer validate --strict',
            $this->workflowStep($steps, 'Composer-Metadaten prüfen')['run'] ?? null,
        );

        $requiredCommands = [
            'Abhängigkeiten auf bekannte Schwachstellen prüfen' => 'composer audit --locked --no-interaction',
            'PHP-Unit-Tests ohne Datenbank ausführen' => 'composer test:unit',
            'PHPStan ausführen' => 'vendor/bin/phpstan analyse -c phpstan.neon.dist',
            'PHP-Code-Stil prüfen' => 'vendor/bin/php-cs-fixer fix --dry-run --diff',
            'Administration headless testen' => 'npm run test:administration',
            'Storefront headless testen' => 'npm run test:storefront',
            'JSON und XML prüfen' => 'jq empty',
        ];
        foreach ($requiredCommands as $stepName => $command) {
            $run = $this->workflowStep($steps, $stepName)['run'] ?? null;
            self::assertIsString($run, sprintf('CI-Schritt "%s" benötigt einen ausführbaren Befehl.', $stepName));
            self::assertStringContainsString($command, $run);
        }

        $shopwareCliInstall = $this->workflowStep($steps, 'Shopware CLI in fester Version installieren')['run'] ?? null;
        self::assertIsString($shopwareCliInstall);
        self::assertStringContainsString('go install github.com/shopware/shopware-cli@0.15.12', $shopwareCliInstall);

        $unitCommand = $this->workflowStep($steps, 'PHP-Unit-Tests ohne Datenbank ausführen')['run'] ?? '';
        self::assertStringNotContainsString('test:integration', (string) $unitCommand);
        self::assertStringNotContainsString('MGD_SHOPWARE_INTEGRATION_TESTS', (string) $unitCommand);

        $restoreStep = $this->workflowStep($steps, 'Veröffentlichbare Composer-Metadaten wiederherstellen');
        self::assertSame('git restore --source=HEAD -- composer.json composer.lock', $restoreStep['run'] ?? null);

        $releaseStep = $this->workflowStep($steps, 'Release-ZIP reproduzierbar bauen');
        self::assertSame("matrix.shopware == '6.7' && matrix.php == '8.4'", $releaseStep['if'] ?? null);
        self::assertSame('bash scripts/build-release.sh', $releaseStep['run'] ?? null);

        $cleanSourceStep = $this->workflowStep($steps, 'Release-Quellstand vollständig sauber prüfen');
        self::assertSame($releaseStep['if'] ?? null, $cleanSourceStep['if'] ?? null);
        self::assertSame(
            'test -z "$(git status --porcelain=v1 --untracked-files=all)"',
            $cleanSourceStep['run'] ?? null,
        );

        $structureStep = $this->workflowStep($steps, 'Vollständigen Release-Strukturvertrag prüfen');
        self::assertSame($releaseStep['if'] ?? null, $structureStep['if'] ?? null);
        self::assertSame(
            'vendor/bin/phpunit --fail-on-skipped tests/Structure/DocumentationAndReleaseTest.php',
            $structureStep['run'] ?? null,
        );
        self::assertStringNotContainsString('--filter', (string) ($structureStep['run'] ?? ''));
        self::assertLessThan(
            $this->workflowStepIndex($steps, 'Release-Quellstand vollständig sauber prüfen'),
            $this->workflowStepIndex($steps, 'Veröffentlichbare Composer-Metadaten wiederherstellen'),
        );
        self::assertLessThan(
            $this->workflowStepIndex($steps, 'Vollständigen Release-Strukturvertrag prüfen'),
            $this->workflowStepIndex($steps, 'Release-Quellstand vollständig sauber prüfen'),
        );
        self::assertLessThan(
            $this->workflowStepIndex($steps, 'Release-ZIP reproduzierbar bauen'),
            $this->workflowStepIndex($steps, 'Vollständigen Release-Strukturvertrag prüfen'),
        );

        $validationStep = $this->workflowStep($steps, 'Ausgeliefertes Shopware-Erweiterungspaket validieren');
        self::assertSame($releaseStep['if'] ?? null, $validationStep['if'] ?? null);
        self::assertSame(
            'shopware-cli --no-interaction extension validate dist/MGDAIImageLabels-0.1.2.zip',
            $validationStep['run'] ?? null,
        );
        self::assertGreaterThan(
            $this->workflowStepIndex($steps, 'Release-ZIP reproduzierbar bauen'),
            $this->workflowStepIndex($steps, 'Ausgeliefertes Shopware-Erweiterungspaket validieren'),
        );

        $shopwareInstallStep = $this->workflowStep($steps, 'Shopware CLI in fester Version installieren');
        self::assertSame($releaseStep['if'] ?? null, $shopwareInstallStep['if'] ?? null);

        $uploadStep = $this->workflowStep($steps, 'Release-ZIP als Artefakt bereitstellen');
        self::assertSame(
            "github.event_name != 'pull_request' && matrix.shopware == '6.7' && matrix.php == '8.4'",
            $uploadStep['if'] ?? null,
        );
        self::assertSame(
            'actions/upload-artifact@043fb46d1a93c77aae656e7c1c64a875d1fc6a0a',
            $uploadStep['uses'] ?? null,
        );
        self::assertSame('error', $uploadStep['with']['if-no-files-found'] ?? null);

        $secretJob = $jobs['secret-scan'] ?? null;
        self::assertIsArray($secretJob);
        $secretSteps = $secretJob['steps'] ?? null;
        self::assertIsArray($secretSteps);
        self::assertSame('1.25.x', $this->workflowStep($secretSteps, 'Go einrichten')['with']['go-version'] ?? null);
        $secretInstall = $this->workflowStep($secretSteps, 'Gitleaks in fester Version installieren')['run'] ?? null;
        self::assertIsString($secretInstall);
        self::assertStringContainsString('go install github.com/zricethezav/gitleaks/v8@v8.30.1', $secretInstall);
        self::assertStringContainsString('echo "$(go env GOPATH)/bin" >> "$GITHUB_PATH"', $secretInstall);
        $secretScan = $this->workflowStep($secretSteps, 'Git-Historie und Arbeitsbaum auf Geheimnisse prüfen')['run'] ?? null;
        self::assertIsString($secretScan);
        self::assertStringContainsString('gitleaks git', $secretScan);
        self::assertStringContainsString('gitleaks dir', $secretScan);
        self::assertStringContainsString('--redact', $secretScan);

        $serializedWorkflow = json_encode($workflow, JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('--no-check-' . 'version', $serializedWorkflow);
        self::assertStringNotContainsString('extension validate ' . '.', $serializedWorkflow);
        self::assertStringNotContainsString('secrets.', $serializedWorkflow);
        self::assertStringNotContainsString('pull_request_target', $serializedWorkflow);
        self::assertStringNotContainsString('contents: write', (string) file_get_contents($workflowPath));

        foreach ($jobs as $job) {
            self::assertIsArray($job);
            $jobSteps = $job['steps'] ?? null;
            self::assertIsArray($jobSteps);
            foreach ($jobSteps as $step) {
                self::assertIsArray($step);
                if (!isset($step['uses'])) {
                    continue;
                }
                self::assertIsString($step['uses']);
                self::assertMatchesRegularExpression(
                    '~^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+@[0-9a-f]{40}$~',
                    $step['uses'],
                    'Externe Actions müssen auf einen unveränderlichen Commit festgesetzt sein.',
                );
            }
        }

        $this->assertShopwareMetadataIsComplete();
    }

    public function testReleaseVersionIsStoredOutsideTheComposerRootVersion(): void
    {
        $composer = json_decode((string) file_get_contents(self::ROOT . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($composer);
        self::assertArrayNotHasKey('version', $composer);
        self::assertSame('0.1.2', $composer['extra']['mgd-release-version'] ?? null);

        $scripts = $composer['scripts'] ?? null;
        self::assertIsArray($scripts);
        $serializedScripts = json_encode($scripts, JSON_THROW_ON_ERROR);
        self::assertStringContainsString('composer validate --strict', $serializedScripts);
        self::assertStringNotContainsString('--no-check-' . 'version', $serializedScripts);

        $buildScript = (string) file_get_contents(self::ROOT . '/scripts/build-release.sh');
        self::assertStringContainsString('$data["extra"]["mgd-release-version"]', $buildScript);
        self::assertStringNotContainsString('$data["version"]', $buildScript);

        $contributing = (string) file_get_contents(self::ROOT . '/CONTRIBUTING.md');
        self::assertStringContainsString('composer validate --strict', $contributing);
        self::assertStringNotContainsString('--no-check-' . 'version', $contributing);

        $plan = (string) file_get_contents(self::ROOT . '/docs/superpowers/plans/2026-08-09-mgd-ai-kennzeichnung-shopware-6.md');
        self::assertStringContainsString(
            'bash scripts/build-release.sh && shopware-cli --no-interaction extension validate dist/MGDAIImageLabels-0.1.1.zip',
            $plan,
        );
        self::assertStringNotContainsString('shopware-cli extension validate ' . '.', $plan);
    }

    public function testRequiredDocumentationIsCompleteAndSafeToPublish(): void
    {
        foreach (self::REQUIRED_DOCUMENTS as $relativePath) {
            $absolutePath = self::ROOT . '/' . $relativePath;
            self::assertFileExists($absolutePath, sprintf('%s fehlt.', $relativePath));

            $content = file_get_contents($absolutePath);
            self::assertIsString($content);
            self::assertStringNotContainsString('TODO', $content, sprintf('%s enthält einen Platzhalter.', $relativePath));
            self::assertDoesNotMatchRegularExpression(
                '~(?:localhost|127\.0\.0\.1|192\.168\.|10\.\d{1,3}\.|SynoToken|launchApp=|BEGIN (?:RSA |OPENSSH )?PRIVATE KEY|(?:api[_-]?key|password|secret|token)\s*[:=]\s*[^\s`]+)~i',
                $content,
                sprintf('%s enthält eine private Adresse oder ein mögliches Geheimnis.', $relativePath),
            );
        }

        $germanReadme = file_get_contents(self::ROOT . '/README.md');
        $englishReadme = file_get_contents(self::ROOT . '/README.en.md');
        self::assertIsString($germanReadme);
        self::assertIsString($englishReadme);
        self::assertStringContainsString('## Installation', $germanReadme);
        self::assertStringContainsString('## Bedienung', $germanReadme);
        self::assertStringContainsString('## Installation', $englishReadme);
        self::assertStringContainsString('## Usage', $englishReadme);
        self::assertStringContainsString('Systemkonfiguration', $germanReadme);
        self::assertStringContainsString('technisch ungenutzte Werte', $germanReadme);

        $deployment = (string) file_get_contents(self::ROOT . '/Dokumentation/Deployment-und-Rueckfall.md');
        self::assertStringContainsString('plugin:update MGDAIImageLabels', $deployment);
        self::assertStringContainsString('vorherige Release-ZIP', $deployment);

        $contributing = (string) file_get_contents(self::ROOT . '/CONTRIBUTING.md');
        foreach (['Bash', 'PHP', 'Python 3', '`install`', '`find`', '`grep`', '`sort`', '`awk`', '`shasum`'] as $tool) {
            self::assertStringContainsString($tool, $contributing);
        }
    }

    public function testLicenseMatchesComposerContract(): void
    {
        $composer = json_decode((string) file_get_contents(self::ROOT . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($composer)) {
            self::fail('composer.json muss ein JSON-Objekt enthalten.');
        }
        self::assertSame('GPL-2.0-or-later', $composer['license'] ?? null);

        $license = (string) file_get_contents(self::ROOT . '/LICENSE');
        self::assertStringContainsString('GNU GENERAL PUBLIC LICENSE', $license);
        self::assertStringContainsString('Version 2, June 1991', $license);
        self::assertStringContainsString('any later version', $license);
    }

    public function testReleaseBuildIsSafeCompleteAndReproducible(): void
    {
        $script = self::ROOT . '/scripts/build-release.sh';
        self::assertFileExists($script);
        self::assertTrue(is_executable($script), 'Das Release-Skript muss ausführbar sein.');

        $firstOutput = $this->runReleaseBuild($script);
        self::assertStringContainsString('MGDAIImageLabels-0.1.2.zip', $firstOutput);

        $archivePath = self::ROOT . '/dist/MGDAIImageLabels-0.1.2.zip';
        self::assertFileExists($archivePath);
        $firstChecksum = hash_file('sha256', $archivePath);
        self::assertIsString($firstChecksum);

        $secondOutput = $this->runReleaseBuild($script);
        self::assertStringContainsString('MGDAIImageLabels-0.1.2.zip', $secondOutput);
        self::assertSame($firstChecksum, hash_file('sha256', $archivePath), 'Zwei Builds müssen bytegleich sein.');

        $archive = new \ZipArchive();
        self::assertTrue($archive->open($archivePath));

        $packagedComposer = $archive->getFromName('MGDAIImageLabels/composer.json');
        self::assertIsString($packagedComposer);
        $packagedMetadata = json_decode($packagedComposer, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($packagedMetadata);
        self::assertSame('0.1.2', $packagedMetadata['version'] ?? null);
        self::assertSame('0.1.2', $packagedMetadata['extra']['mgd-release-version'] ?? null);

        $entries = [];
        for ($index = 0; $index < $archive->numFiles; ++$index) {
            $name = $archive->getNameIndex($index);
            self::assertIsString($name);
            $entries[] = $name;
            self::assertMatchesRegularExpression('~^MGDAIImageLabels(?:/|$)~', $name);
            self::assertStringNotContainsString('\\', $name);
            self::assertDoesNotMatchRegularExpression('~(?:^|/)\.\.(?:/|$)~', $name);
            self::assertDoesNotMatchRegularExpression(
                '~(?:^|/)(?:\.git|\.env(?:\.[^/]*)?|tests|vendor|Backups|\.superpowers|node_modules|dist)(?:/|$)~i',
                $name,
            );

            $operationsSystem = null;
            $attributes = null;
            self::assertTrue($archive->getExternalAttributesIndex($index, $operationsSystem, $attributes));
            if (!is_int($attributes)) {
                self::fail(sprintf('ZIP-Rechte von %s konnten nicht als Ganzzahl gelesen werden.', $name));
            }
            self::assertNotSame(0120000, ($attributes >> 16) & 0170000, sprintf('%s ist ein symbolischer Link.', $name));
        }
        $archive->close();

        sort($entries);
        self::assertContains('MGDAIImageLabels/composer.json', $entries);
        self::assertContains('MGDAIImageLabels/LICENSE', $entries);
        self::assertContains('MGDAIImageLabels/README.md', $entries);
        self::assertContains('MGDAIImageLabels/README.en.md', $entries);
        self::assertContains(
            'MGDAIImageLabels/src/Resources/public/administration/.vite/entrypoints.json',
            $entries,
            'Das Installationspaket muss die gebaute Administration ohne Zielserver-Build enthalten.',
        );
        self::assertNotEmpty(
            array_filter(
                $entries,
                static fn (string $entry): bool => preg_match(
                    '~^MGDAIImageLabels/src/Resources/public/administration/assets/.+\.js$~',
                    $entry,
                ) === 1,
            ),
            'Das gebaute Administration-JavaScript fehlt im Installationspaket.',
        );
        self::assertNotEmpty(
            array_filter(
                $entries,
                static fn (string $entry): bool => preg_match(
                    '~^MGDAIImageLabels/src/Resources/public/administration/assets/.+\.css$~',
                    $entry,
                ) === 1,
            ),
            'Das gebaute Administration-CSS fehlt im Installationspaket.',
        );

        foreach ($this->runtimeSourceFiles() as $runtimeFile) {
            self::assertContains('MGDAIImageLabels/' . $runtimeFile, $entries, sprintf('%s fehlt im Release.', $runtimeFile));
        }
    }

    public function testReleaseRefusesDistSymlinkWithoutTouchingExternalTarget(): void
    {
        $fixture = $this->createReleaseFixture();
        $externalTarget = $this->createTemporaryDirectory('mgd-release-external-');
        $marker = $externalTarget . '/unbeteiligt.txt';
        file_put_contents($marker, 'unverändert');
        self::assertTrue(symlink($externalTarget, $fixture . '/dist'));

        try {
            [$exitCode, $output] = $this->runFixtureBuild($fixture);
            self::assertNotSame(0, $exitCode, $output);
            self::assertTrue(is_link($fixture . '/dist'), 'Der Test-Symlink darf nicht ersetzt werden.');
            self::assertSame('unverändert', file_get_contents($marker));
            self::assertSame(['.', '..', 'unbeteiligt.txt'], scandir($externalTarget));
        } finally {
            $this->removeTemporaryTree($fixture);
            $this->removeTemporaryTree($externalTarget);
        }
    }

    public function testReleaseRefusesSpecialFileAtDistPath(): void
    {
        $fixture = $this->createReleaseFixture();
        file_put_contents($fixture . '/dist', 'kein Verzeichnis');

        try {
            [$exitCode, $output] = $this->runFixtureBuild($fixture);
            self::assertNotSame(0, $exitCode, $output);
            self::assertSame('kein Verzeichnis', file_get_contents($fixture . '/dist'));
            self::assertFileDoesNotExist($fixture . '/MGDAIImageLabels-0.1.1.zip');
        } finally {
            $this->removeTemporaryTree($fixture);
        }
    }

    public function testReleaseUsesAtomicTemporaryArchiveInsideValidatedDist(): void
    {
        $script = (string) file_get_contents(self::ROOT . '/scripts/build-release.sh');

        self::assertMatchesRegularExpression(
            '~mktemp\s+"\$\{(?:validated_)?dist_directory\}/\.MGDAIImageLabels-~',
            $script,
            'Die temporäre ZIP-Datei muss im validierten dist-Verzeichnis liegen.',
        );
        self::assertStringContainsString('os.replace(sys.argv[1], sys.argv[2])', $script);
        self::assertStringContainsString('trap cleanup EXIT', $script);
        self::assertStringNotContainsString('trap cleanup EXIT INT TERM HUP', $script);
    }

    public function testTerminationStopsBuildWithDedicatedExitCodeAndCleansWorkspace(): void
    {
        if (!function_exists('proc_open') || !function_exists('proc_terminate')) {
            self::markTestSkipped('Für den Signalvertrag werden PHP-Prozessfunktionen benötigt.');
        }

        $fixture = $this->createReleaseFixture();
        $controlledTemp = $this->createTemporaryDirectory('mgd-release-signal-');
        for ($index = 0; $index < 3000; ++$index) {
            file_put_contents($fixture . '/src/datei-' . $index . '.txt', str_repeat('x', 1024));
        }

        $pipes = [];
        $process = proc_open(
            ['bash', $fixture . '/scripts/build-release.sh'],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $fixture,
            ['TMPDIR' => $controlledTemp, 'PATH' => (string) getenv('PATH')],
        );
        self::assertIsResource($process);

        try {
            $deadline = microtime(true) + 5.0;
            do {
                // Der Paket-Unterordner entsteht erst nach Registrierung aller
                // Signal-Traps. Das vermeidet ein Rennen direkt nach mktemp.
                $workspaces = glob($controlledTemp . '/mgd-ai-labels-release.*/MGDAIImageLabels', GLOB_ONLYDIR) ?: [];
                if ($workspaces !== []) {
                    break;
                }
                usleep(1000);
            } while (microtime(true) < $deadline);
            self::assertNotSame([], $workspaces, 'Der Prozess erreichte den abgesicherten Arbeitsbereich nicht.');

            self::assertTrue(proc_terminate($process, 15));
            foreach ($pipes as $pipe) {
                // Das Lesen wartet deterministisch auf das Ende des Kindes,
                // ohne dessen Exitcode vor proc_close() einmalig zu verbrauchen.
                stream_get_contents($pipe);
                fclose($pipe);
            }
            $exitCode = proc_close($process);
            $process = null;

            self::assertSame(143, $exitCode, 'SIGTERM muss eindeutig mit Exitcode 143 enden.');
            self::assertSame([], glob($controlledTemp . '/mgd-ai-labels-release.*') ?: [], 'Der EXIT-Trap muss den Arbeitsbereich entfernen.');
            self::assertFileDoesNotExist($fixture . '/dist/MGDAIImageLabels-0.1.1.zip');
        } finally {
            if (is_resource($process)) {
                proc_terminate($process, 9);
                foreach ($pipes as $pipe) {
                    if (is_resource($pipe)) {
                        fclose($pipe);
                    }
                }
                proc_close($process);
            }
            $this->removeTemporaryTree($fixture);
            $this->removeTemporaryTree($controlledTemp);
        }
    }

    /** Schützt Vollständigkeit, Navigation und Vertraulichkeit des öffentlichen Wikis. */
    public function testPublicWikiIsCompleteNavigableAndFreeFromOperationalSecrets(): void
    {
        $wikiDirectory = self::ROOT . '/docs/wiki';
        self::assertDirectoryExists($wikiDirectory);
        $combined = '';

        foreach (self::REQUIRED_WIKI_DOCUMENTS as $document) {
            $path = $wikiDirectory . '/' . $document;
            self::assertFileExists($path);
            $content = file_get_contents($path);
            self::assertIsString($content);
            self::assertStringNotContainsString('TODO', $content);
            self::assertStringNotContainsString('TBD', $content);
            $combined .= "\n" . $content;
        }

        $sidebar = file_get_contents($wikiDirectory . '/_Sidebar.md');
        self::assertIsString($sidebar);
        foreach (array_diff(self::REQUIRED_WIKI_DOCUMENTS, ['_Sidebar.md', '_Footer.md']) as $document) {
            self::assertStringContainsString(sprintf('](%s)', pathinfo($document, PATHINFO_FILENAME)), $sidebar);
        }

        foreach (['/Users/', 'Zauberwort', 'SynoToken', 'DATABASE_URL=', 'password=', 'token='] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $combined);
        }
    }

    /** Stellt sicher, dass jeder lokale Link in README und Wiki auf ein echtes Ziel verweist. */
    public function testReadmeAndWikiLocalLinksResolveToExistingTargets(): void
    {
        $documents = [self::ROOT . '/README.md'];
        foreach (self::REQUIRED_WIKI_DOCUMENTS as $document) {
            $documents[] = self::ROOT . '/docs/wiki/' . $document;
        }

        foreach ($documents as $document) {
            $content = file_get_contents($document);
            self::assertIsString($content);
            preg_match_all('/\[[^]]+]\(([^)]+)\)/', $content, $matches);
            foreach ($matches[1] as $target) {
                if (preg_match('~^(?:https?://|mailto:|#)~', $target) === 1) {
                    continue;
                }

                $path = rawurldecode(explode('#', $target, 2)[0]);
                if (str_starts_with($document, self::ROOT . '/docs/wiki/') && !str_contains(basename($path), '.')) {
                    $path .= '.md';
                }

                self::assertFileExists(
                    dirname($document) . '/' . $path,
                    sprintf('Defekter Link %s in %s', $target, $document),
                );
            }
        }
    }

    private function runReleaseBuild(string $script): string
    {
        $command = sprintf('bash %s 2>&1', escapeshellarg($script));
        exec($command, $lines, $exitCode);
        self::assertSame(0, $exitCode, implode("\n", $lines));

        return implode("\n", $lines);
    }

    /** @return array{int, string} */
    private function runFixtureBuild(string $fixture): array
    {
        $command = sprintf('bash %s 2>&1', escapeshellarg($fixture . '/scripts/build-release.sh'));
        exec($command, $lines, $exitCode);

        return [$exitCode, implode("\n", $lines)];
    }

    private function createReleaseFixture(): string
    {
        $fixture = $this->createTemporaryDirectory('mgd-release-fixture-');
        mkdir($fixture . '/scripts', 0700);
        mkdir($fixture . '/src', 0700);
        mkdir($fixture . '/Dokumentation', 0700);
        copy(self::ROOT . '/scripts/build-release.sh', $fixture . '/scripts/build-release.sh');
        chmod($fixture . '/scripts/build-release.sh', 0700);
        file_put_contents($fixture . '/composer.json', '{"extra":{"mgd-release-version":"0.1.1"}}');
        foreach (['LICENSE', 'README.md', 'README.en.md', 'SECURITY.md', 'CONTRIBUTING.md', 'CHANGELOG.md'] as $file) {
            file_put_contents($fixture . '/' . $file, $file);
        }
        file_put_contents($fixture . '/src/Laufzeit.php', '<?php');
        file_put_contents($fixture . '/Dokumentation/Hinweis.md', '# Hinweis');

        return $fixture;
    }

    private function createTemporaryDirectory(string $prefix): string
    {
        $directory = sys_get_temp_dir() . '/' . $prefix . bin2hex(random_bytes(8));
        self::assertTrue(mkdir($directory, 0700));

        return $directory;
    }

    /** Entfernt ausschließlich den zuvor mit zufälligem Testpräfix erzeugten Baum. */
    private function removeTemporaryTree(string $path): void
    {
        $baseName = basename($path);
        if (!str_starts_with($baseName, 'mgd-release-') || dirname($path) !== rtrim(sys_get_temp_dir(), '/')) {
            self::fail('Unsicherer temporärer Testpfad wurde nicht entfernt: ' . $path);
        }

        if (is_link($path) || is_file($path)) {
            unlink($path);

            return;
        }
        if (!is_dir($path)) {
            return;
        }

        foreach (scandir($path) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $child = $path . '/' . $entry;
            if (is_link($child) || is_file($child)) {
                unlink($child);
            } else {
                $this->removeTemporaryNestedTree($child, $path);
            }
        }
        rmdir($path);
    }

    private function removeTemporaryNestedTree(string $path, string $root): void
    {
        if (!str_starts_with($path . '/', $root . '/') || is_link($path) || !is_dir($path)) {
            self::fail('Unsicherer verschachtelter Testpfad wurde nicht entfernt: ' . $path);
        }
        foreach (scandir($path) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $child = $path . '/' . $entry;
            if (is_link($child) || is_file($child)) {
                unlink($child);
            } else {
                $this->removeTemporaryNestedTree($child, $root);
            }
        }
        rmdir($path);
    }

    /**
     * @param array<array-key, mixed> $steps
     *
     * @return array<string, mixed>
     */
    private function workflowStep(array $steps, string $name): array
    {
        foreach ($steps as $step) {
            if (is_array($step) && ($step['name'] ?? null) === $name) {
                return $step;
            }
        }

        self::fail(sprintf('Der CI-Schritt "%s" fehlt.', $name));
    }

    /** @param array<array-key, mixed> $steps */
    private function workflowStepIndex(array $steps, string $name): int
    {
        foreach ($steps as $index => $step) {
            if (is_int($index) && is_array($step) && ($step['name'] ?? null) === $name) {
                return $index;
            }
        }

        self::fail(sprintf('Der CI-Schritt "%s" besitzt keine Position.', $name));
    }

    /** Prüft die von Shopware CLI verlangten öffentlichen Plugin-Angaben. */
    private function assertShopwareMetadataIsComplete(): void
    {
        $composer = json_decode((string) file_get_contents(self::ROOT . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($composer);
        self::assertSame('Michael Gahn DESIGN', $composer['authors'][0]['name'] ?? null);

        $extra = $composer['extra'] ?? null;
        self::assertIsArray($extra);
        foreach (['de-DE', 'en-GB'] as $locale) {
            self::assertIsString($extra['label'][$locale] ?? null);
            $description = $extra['description'][$locale] ?? null;
            self::assertIsString($description);
            self::assertGreaterThanOrEqual(150, mb_strlen($description));
            self::assertLessThanOrEqual(185, mb_strlen($description));
            self::assertSame('https://github.com/MichaelGahnDESIGN', $extra['manufacturerLink'][$locale] ?? null);
            self::assertSame(
                'https://github.com/MichaelGahnDESIGN/MGD-AI-Kennzeichnung-Shopware-6/blob/main/SECURITY.md',
                $extra['supportLink'][$locale] ?? null,
            );
        }

        $iconPath = self::ROOT . '/src/Resources/config/plugin.png';
        self::assertFileExists($iconPath);
        $imageSize = getimagesize($iconPath);
        self::assertIsArray($imageSize);
        self::assertSame([128, 128], [$imageSize[0], $imageSize[1]]);
        self::assertSame(IMAGETYPE_PNG, $imageSize[2]);
    }

    /** @return list<string> */
    private function runtimeSourceFiles(): array
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(self::ROOT . '/src', \FilesystemIterator::SKIP_DOTS),
        );
        $files = [];
        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo || !$file->isFile()) {
                continue;
            }

            $files[] = 'src/' . substr($file->getPathname(), strlen(self::ROOT . '/src/'));
        }
        sort($files);

        return $files;
    }
}
