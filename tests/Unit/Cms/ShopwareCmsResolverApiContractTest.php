<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Cms;

use Composer\InstalledVersions;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\CmsElementResolverInterface;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\TestBootstrapper;

/**
 * Schützt den recherchierten Resolver-Vertrag der beiden Zielversionen.
 *
 * Die Prüfsummen stammen aus den unveränderten offiziellen Shopware-Tags
 * v6.6.10.22 und v6.7.13.0. Pro Lauf wird ausdrücklich nur die tatsächlich
 * installierte Linie geprüft. Erst Task 13 führt diesen Test in zwei getrennten
 * Shopware-Kerneln aus; dieser Test behauptet keinen lokalen Doppellauf.
 */
final class ShopwareCmsResolverApiContractTest extends TestCase
{
    /** @var array<string, array{interface: string, criteria: string, mediaDefinition: string, testBootstrapper: string}> */
    private const EXACT_OFFICIAL_CONTRACTS = [
        '6.6.10.22' => [
            'interface' => '024f0d30f34aeb6038c71bf1815c5e1b9bc4dc6b6b55e79a65dd20a78bfe4152',
            'criteria' => '941db3c05ec139f0ceb18eee622dfc8fa0bc64ea751410f4925d4790d1bdd0ea',
            'mediaDefinition' => 'fcb0a5cdfb373ec8ea9a0620ef6738520de66e8ecf7a1cda8dad3ab04ababd4a',
            'testBootstrapper' => '39f26f8f84cc6dbed932dad70ebacb8df4876dc097ae7a04be69b988af77d3f9',
        ],
        '6.7.13.0' => [
            'interface' => '024f0d30f34aeb6038c71bf1815c5e1b9bc4dc6b6b55e79a65dd20a78bfe4152',
            'criteria' => '941db3c05ec139f0ceb18eee622dfc8fa0bc64ea751410f4925d4790d1bdd0ea',
            'mediaDefinition' => '6c2c470012d2974cb9c6614695658956658d84ebf1daf7a06ad95f81b4fd4f61',
            'testBootstrapper' => '55b22f45ec2e459d1443b5e2b810c4aa65f209f5c5290efd91a52176a939dc74',
        ],
    ];

    public function testInstalledOfficialSourceMatchesItsResearchedContract(): void
    {
        $prettyVersion = InstalledVersions::getPrettyVersion('shopware/core');
        self::assertIsString($prettyVersion);
        $version = ltrim($prettyVersion, 'v');
        self::assertArrayHasKey(
            $version,
            self::EXACT_OFFICIAL_CONTRACTS,
            'Task 13 ist bewusst auf die zwei geprüften offiziellen Shopware-Tags begrenzt.',
        );
        $contract = self::EXACT_OFFICIAL_CONTRACTS[$version];

        self::assertSame($contract['interface'], $this->sourceHash(CmsElementResolverInterface::class));
        self::assertSame($contract['criteria'], $this->sourceHash(CriteriaCollection::class));
        self::assertSame($contract['mediaDefinition'], $this->sourceHash(MediaDefinition::class));
        self::assertSame($contract['testBootstrapper'], $this->sourceHash(TestBootstrapper::class));

        $addCallingPlugin = new \ReflectionMethod(TestBootstrapper::class, 'addCallingPlugin');
        self::assertTrue($addCallingPlugin->isPublic());
        self::assertCount(1, $addCallingPlugin->getParameters());
        self::assertTrue($addCallingPlugin->getParameters()[0]->allowsNull());
        self::assertTrue((new \ReflectionMethod(TestBootstrapper::class, 'setForceInstallPlugins'))->isPublic());
    }

    /** @param class-string $className */
    private function sourceHash(string $className): string
    {
        $fileName = (new \ReflectionClass($className))->getFileName();
        self::assertIsString($fileName);

        $hash = hash_file('sha256', $fileName);
        if ($hash === false) {
            throw new \RuntimeException('Die installierte Shopware-Quelldatei konnte nicht geprüft werden.');
        }

        return $hash;
    }
}
