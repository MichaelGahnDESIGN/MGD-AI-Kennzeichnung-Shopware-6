<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Update;

use MGDAIImageLabels\Update\PluginDirectory;
use PHPUnit\Framework\TestCase;

/** Der Einstiegspunkt liegt unter src/, die erlaubte ZIP-Installation eine Ebene darüber. */
final class PluginDirectoryTest extends TestCase
{
    private string $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->project = sys_get_temp_dir() . '/mgd-ai-plugin-path-' . bin2hex(random_bytes(8));
        self::assertTrue(mkdir($this->project . '/custom/plugins/MGDAIImageLabels/src', 0755, true));
        self::assertNotFalse(file_put_contents($this->entryFile(), '<?php'));
    }

    protected function tearDown(): void
    {
        unlink($this->entryFile());
        rmdir(dirname($this->entryFile()));
        rmdir(dirname($this->entryFile(), 2));
        rmdir($this->project . '/custom/plugins');
        rmdir($this->project . '/custom');
        rmdir($this->project);
        parent::tearDown();
    }

    public function testEntryClassResolvesToPluginRootNotSrc(): void
    {
        self::assertSame(
            realpath($this->project . '/custom/plugins/MGDAIImageLabels'),
            PluginDirectory::resolve($this->entryFile(), $this->project),
        );
    }

    public function testForeignProjectDirectoryIsRejected(): void
    {
        $this->expectException(\RuntimeException::class);
        PluginDirectory::resolve($this->entryFile(), $this->project . '/other-shop');
    }

    private function entryFile(): string
    {
        return $this->project . '/custom/plugins/MGDAIImageLabels/src/MGDAIImageLabels.php';
    }
}
