<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Update;

use MGDAIImageLabels\Update\ArchiveValidator;
use MGDAIImageLabels\Update\AtomicInstaller;
use PHPUnit\Framework\TestCase;

/** Der Dateitausch wird in einem isolierten Test-Shop inklusive Rückfall geprüft. */
final class AtomicInstallerTest extends TestCase
{
    private string $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->project = sys_get_temp_dir() . '/mgd-ai-updater-test-' . bin2hex(random_bytes(8));
        self::assertTrue(mkdir($this->project . '/custom/plugins/MGDAIImageLabels', 0755, true));
        self::assertNotFalse(file_put_contents(
            $this->project . '/custom/plugins/MGDAIImageLabels/composer.json',
            json_encode($this->manifest('0.1.2'), JSON_THROW_ON_ERROR),
        ));
    }

    protected function tearDown(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->project, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo) {
                continue;
            }
            $file->isDir() && !$file->isLink() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->project);
        parent::tearDown();
    }

    public function testVerifiedArchiveIsPreparedAndOriginalIsBackedUp(): void
    {
        $archive = $this->archive();
        $refreshCalls = 0;

        (new AtomicInstaller(new ArchiveValidator()))->install(
            $archive,
            '0.1.3',
            $this->pluginDirectory(),
            $this->privateDirectory(),
            static function () use (&$refreshCalls): void { ++$refreshCalls; },
        );

        self::assertSame(1, $refreshCalls);
        self::assertSame('0.1.3', $this->installedVersion());
        self::assertCount(1, glob($this->privateDirectory() . '/backup-*') ?: []);
    }

    public function testRefreshFailureRestoresOriginalPlugin(): void
    {
        $archive = $this->archive();

        try {
            (new AtomicInstaller(new ArchiveValidator()))->install(
                $archive,
                '0.1.3',
                $this->pluginDirectory(),
                $this->privateDirectory(),
                static function (): void { throw new \RuntimeException('Testfehler beim Shopware-Refresh'); },
            );
            self::fail('Der fehlgeschlagene Refresh muss abbrechen.');
        } catch (\RuntimeException $error) {
            self::assertSame('Testfehler beim Shopware-Refresh', $error->getMessage());
        }

        self::assertSame('0.1.2', $this->installedVersion());
    }

    private function archive(): string
    {
        $path = $this->project . '/candidate.zip';
        $zip = new \ZipArchive();
        self::assertTrue($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE));
        self::assertTrue($zip->addFromString(
            'MGDAIImageLabels/composer.json',
            json_encode($this->manifest('0.1.3'), JSON_THROW_ON_ERROR),
        ));
        self::assertTrue($zip->addFromString('MGDAIImageLabels/src/MGDAIImageLabels.php', '<?php'));
        self::assertTrue($zip->close());
        return $path;
    }

    /** @return array<string, mixed> */
    private function manifest(string $version): array
    {
        return [
            'name' => 'michaelgahn-design/mgd-ai-kennzeichnung-shopware-6',
            'type' => 'shopware-platform-plugin',
            'version' => $version,
            'extra' => ['shopware-plugin-class' => 'MGDAIImageLabels\\MGDAIImageLabels'],
            'autoload' => ['psr-4' => ['MGDAIImageLabels\\' => 'src/']],
            'require' => ['php' => '^8.2', 'shopware/core' => '~6.6.10 || ~6.7.0', 'shopware/storefront' => '~6.6.10 || ~6.7.0'],
        ];
    }

    private function pluginDirectory(): string
    {
        return $this->project . '/custom/plugins/MGDAIImageLabels';
    }

    private function privateDirectory(): string
    {
        return $this->project . '/var/mgd-ai-image-labels';
    }

    private function installedVersion(): string
    {
        $manifest = json_decode((string) file_get_contents($this->pluginDirectory() . '/composer.json'), true, 32, JSON_THROW_ON_ERROR);
        self::assertIsArray($manifest);
        self::assertIsString($manifest['version'] ?? null);
        return $manifest['version'];
    }
}
