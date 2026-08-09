<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Storefront;

use Composer\InstalledVersions;
use PHPUnit\Framework\TestCase;

/**
 * Prüft die tatsächlich per Composer installierte offizielle Shopware-Quelle.
 *
 * Die Hashes stammen aus den unveränderten Tags v6.6.10.22 und v6.7.13.0.
 * Damit werden keine großen Shopware-Templates kopiert. Die spätere CI-Matrix
 * führt denselben Test mit der jeweils installierten Shopware-Linie aus.
 */
final class ShopwareThumbnailCallerContractTest extends TestCase
{
    /** @var array<string, array{thumbnailSha256: string, calls: int, names: list<string>}> */
    private const EXACT_CONTRACTS = [
        '6.6.10.22' => [
            'thumbnailSha256' => '8080a1849dd029fbe479cbfaf747f7d26c46c6f535e17ef0f0cea4201093a0ab',
            'calls' => 27,
            'names' => [
                'cms-block-background',
                'cms-element-vimeo-video__placeholder',
                'cms-element-youtube-video__placeholder',
                'cms-image-slider-thumbnails',
                'cms-image-thumbnails',
                'configurator-option-img-thumbnails',
                'footer-payment-image-thumbnails',
                'footer-shipping-image-thumbnails',
                'gallery-slider-image-thumbnails',
                'gallery-slider-thumbnails-image-thumbnails',
                'line-item-img-thumbnails',
                'minimal-image-thumbnails',
                'navigation-flyout-teaser-image-thumbnails',
                'payment-method-image-thumbnails',
                'product-detail-manufacturer-image-thumbnails',
                'product-image-thumbnails',
                'quickview-minimal-product-manufacturer-logo',
                'search-suggest-product-image-thumbnails',
                'shipping-method-image-thumbnails',
            ],
        ],
        '6.7.13.0' => [
            'thumbnailSha256' => '2ab633c06b67020f3ec0f1fb7a6529cb96ba91879447d19cdec9df039d85f923',
            'calls' => 24,
            'names' => [
                'cms-block-background',
                'cms-element-vimeo-video__placeholder',
                'cms-element-youtube-video__placeholder',
                'cms-image-slider-thumbnails',
                'cms-image-thumbnails',
                'configurator-option-img-thumbnails',
                'footer-payment-image-thumbnails',
                'footer-shipping-image-thumbnails',
                'gallery-slider-image-thumbnails',
                'gallery-slider-thumbnails-image-thumbnails',
                'line-item-img-thumbnails',
                'minimal-image-thumbnails',
                'navigation-flyout-teaser-image-thumbnails',
                'payment-method-image-thumbnails',
                'product-image-thumbnails',
                'quickview-minimal-product-manufacturer-logo',
                'search-suggest-product-image-thumbnails',
                'shipping-method-image-thumbnails',
            ],
        ],
    ];

    public function testInstalledExactOfficialTagMatchesTheResearchedCallerInventory(): void
    {
        $prettyVersion = InstalledVersions::getPrettyVersion('shopware/storefront');
        self::assertIsString($prettyVersion);
        $version = ltrim($prettyVersion, 'v');
        self::assertArrayHasKey(
            $version,
            self::EXACT_CONTRACTS,
            'Dieser lokale Forschungscheck ist bewusst auf die beiden geprüften offiziellen Tags begrenzt.',
        );
        $contract = self::EXACT_CONTRACTS[$version];

        $viewRoot = __DIR__ . '/../../vendor/shopware/storefront/Resources/views/storefront';
        $thumbnail = file_get_contents($viewRoot . '/utilities/thumbnail.html.twig');
        self::assertIsString($thumbnail);
        self::assertSame($contract['thumbnailSha256'], hash('sha256', $thumbnail));

        [$calls, $names] = $this->thumbnailCalls($viewRoot);
        self::assertSame($contract['calls'], $calls);
        self::assertSame($contract['names'], $names);
    }

    /** @return array{int, list<string>} */
    private function thumbnailCalls(string $viewRoot): array
    {
        $calls = 0;
        $names = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($viewRoot));

        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo || !$file->isFile() || $file->getExtension() !== 'twig') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            self::assertIsString($contents);
            $matches = [];
            $matchCount = preg_match_all("/{%\\s*sw_thumbnails\\s+'([^']+)'/", $contents, $matches);
            self::assertIsInt($matchCount);
            $calls += $matchCount;

            foreach ($matches[1] as $name) {
                $names[$name] = true;
            }
        }

        $uniqueNames = array_keys($names);
        sort($uniqueNames);

        return [$calls, $uniqueNames];
    }
}
