<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Configuration;

use MGDAIImageLabels\Configuration\ConfigurationKeys;
use PHPUnit\Framework\TestCase;

/** Beweist, dass Sicherung und Laufzeit exakt dieselbe geschlossene Schlüsselliste verwenden. */
final class ConfigurationKeysTest extends TestCase
{
    public function testContainsExactlyTheNineOwnedConfigurationKeys(): void
    {
        self::assertSame([
            'MGDAIImageLabels.config.fontSize',
            'MGDAIImageLabels.config.offset',
            'MGDAIImageLabels.config.paddingY',
            'MGDAIImageLabels.config.paddingX',
            'MGDAIImageLabels.config.radius',
            'MGDAIImageLabels.config.blur',
            'MGDAIImageLabels.config.position',
            'MGDAIImageLabels.config.theme',
            'MGDAIImageLabels.config.language',
        ], ConfigurationKeys::all());
    }

    public function testRejectsForeignKeysAndDefinesExpectedScalarTypes(): void
    {
        self::assertFalse(ConfigurationKeys::isOwned('OtherPlugin.config.secret'));
        self::assertSame('integer', ConfigurationKeys::expectedType(ConfigurationKeys::FONT_SIZE));
        self::assertSame('string', ConfigurationKeys::expectedType(ConfigurationKeys::LANGUAGE));
    }
}
