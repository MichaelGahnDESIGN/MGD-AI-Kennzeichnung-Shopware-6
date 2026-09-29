<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Update;

/** Ermittelt ausschließlich die direkt unter custom/plugins installierte ZIP-Erweiterung. */
final class PluginDirectory
{
    public static function resolve(string $entryClassFile, string $projectDirectory): string
    {
        // Der Shopware-Einstiegspunkt liegt in <Plugin>/src/MGDAIImageLabels.php.
        // Ein einzelnes dirname() würde src/ statt des Plugin-Ordners liefern.
        $plugin = dirname($entryClassFile, 2);
        $expected = $projectDirectory . '/custom/plugins/MGDAIImageLabels';
        $actualPath = realpath($plugin);
        $expectedPath = realpath($expected);

        if ($actualPath === false || $expectedPath === false || is_link($expected) || $actualPath !== $expectedPath) {
            throw new \RuntimeException('GitHub-Updates erfordern die ZIP-Installation in custom/plugins/MGDAIImageLabels.');
        }

        return $expectedPath;
    }
}
