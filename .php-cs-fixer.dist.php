<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

/**
 * Gemeinsamer, reproduzierbarer PHP-Stil für den produktiven Laufzeitcode.
 *
 * Die PHP-8.2-Migrationsregeln bilden die niedrigste unterstützte Laufzeit ab.
 * Abhängigkeiten und Release-Pakete werden bewusst nie durchsucht.
 */
$finder = Finder::create()
    ->files()
    ->name('*.php')
    ->in(__DIR__ . '/src')
    ->exclude(['vendor', 'dist']);

return (new Config())
    ->setRiskyAllowed(true)
    ->setUsingCache(false)
    ->setRules([
        '@PER-CS2.0' => true,
        '@PHP82Migration' => true,
        'declare_strict_types' => true,
        // Mehrzeilige Konstruktoren bleiben für Menschen schneller erfassbar.
        'braces_position' => false,
        'single_line_empty_body' => false,
    ])
    ->setFinder($finder);
