<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Update;

use MGDAIImageLabels\Update\ArchiveValidator;
use MGDAIImageLabels\Update\DownloadPolicy;
use MGDAIImageLabels\Update\ReleaseMetadata;
use MGDAIImageLabels\Update\RequirementPolicy;
use PHPUnit\Framework\TestCase;

/** Sichert die Vertrauensgrenze zwischen fremdem GitHub-Release und Shopdateien ab. */
final class GitHubUpdateContractTest extends TestCase
{
    public function testReleaseRequiresExactRepositoryAssetAndDigest(): void
    {
        $release = $this->release();
        $metadata = ReleaseMetadata::fromGitHub($release, '0.1.2');

        self::assertSame('0.1.3', $metadata->version);
        self::assertSame($release['assets'][0]['browser_download_url'], $metadata->url);

        $release['assets'][0]['digest'] = null;
        $this->expectException(\RuntimeException::class);
        ReleaseMetadata::fromGitHub($release, '0.1.2');
    }

    public function testOlderReleaseIsRejected(): void
    {
        $this->expectException(\RuntimeException::class);
        ReleaseMetadata::fromGitHub($this->release(), '0.1.3');
    }

    public function testArchivePathAllowlistBlocksTraversalAndExecutables(): void
    {
        self::assertTrue(ArchiveValidator::allowedPath('MGDAIImageLabels/src/Resources/public/administration/.vite/manifest.json'));
        self::assertTrue(ArchiveValidator::allowedPath('MGDAIImageLabels/src/Resources/app/storefront/src/scss/base.scss'));
        self::assertFalse(ArchiveValidator::allowedPath('MGDAIImageLabels/../evil.php'));
        self::assertFalse(ArchiveValidator::allowedPath('MGDAIImageLabels/src/payload.sh'));
        self::assertFalse(ArchiveValidator::allowedPath('MGDAIImageLabels/src/.env'));
        self::assertFalse(ArchiveValidator::allowedPath('OtherPlugin/src/MGDAIImageLabels.php'));
    }

    public function testDownloadRedirectsNeverReachArbitraryHosts(): void
    {
        $policy = new DownloadPolicy();
        self::assertTrue($policy->allowedRedirect('https://release-assets.githubusercontent.com/example'));
        self::assertFalse($policy->allowedRedirect('http://github.com/example'));
        self::assertFalse($policy->allowedRedirect('https://github.com.evil.example/'));
        self::assertFalse($policy->allowedRedirect('https://user:password@github.com/example'));
    }

    public function testChangedDependenciesRequireManualShopwareInstallation(): void
    {
        $this->expectException(\RuntimeException::class);
        RequirementPolicy::assertIdentical(
            ['php' => '^8.2', 'shopware/core' => '~6.7.0'],
            ['php' => '^8.2', 'shopware/core' => '~6.8.0'],
        );
    }

    /** @return array<string, mixed> */
    private function release(): array
    {
        return [
            'tag_name' => 'v0.1.3',
            'draft' => false,
            'prerelease' => false,
            'assets' => [[
                'name' => 'MGDAIImageLabels-0.1.3.zip',
                'browser_download_url' => 'https://github.com/MichaelGahnDESIGN/MGD-AI-Kennzeichnung-Shopware-6/releases/download/v0.1.3/MGDAIImageLabels-0.1.3.zip',
                'size' => 123456,
                'digest' => 'sha256:' . str_repeat('a', 64),
            ]],
        ];
    }
}
