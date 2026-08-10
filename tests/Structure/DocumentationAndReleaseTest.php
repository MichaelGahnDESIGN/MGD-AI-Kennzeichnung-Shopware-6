<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Structure;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

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
        self::assertStringContainsString('MGDAIImageLabels-0.1.0.zip', $firstOutput);

        $archivePath = self::ROOT . '/dist/MGDAIImageLabels-0.1.0.zip';
        self::assertFileExists($archivePath);
        $firstChecksum = hash_file('sha256', $archivePath);
        self::assertIsString($firstChecksum);

        $secondOutput = $this->runReleaseBuild($script);
        self::assertStringContainsString('MGDAIImageLabels-0.1.0.zip', $secondOutput);
        self::assertSame($firstChecksum, hash_file('sha256', $archivePath), 'Zwei Builds müssen bytegleich sein.');

        $archive = new \ZipArchive();
        self::assertTrue($archive->open($archivePath));

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
            self::assertFileDoesNotExist($fixture . '/MGDAIImageLabels-0.1.0.zip');
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
            self::assertFileDoesNotExist($fixture . '/dist/MGDAIImageLabels-0.1.0.zip');
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
        file_put_contents($fixture . '/composer.json', '{"version":"0.1.0"}');
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
