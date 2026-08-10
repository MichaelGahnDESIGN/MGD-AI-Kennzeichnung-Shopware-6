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

    private function runReleaseBuild(string $script): string
    {
        $command = sprintf('bash %s 2>&1', escapeshellarg($script));
        exec($command, $lines, $exitCode);
        self::assertSame(0, $exitCode, implode("\n", $lines));

        return implode("\n", $lines);
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
