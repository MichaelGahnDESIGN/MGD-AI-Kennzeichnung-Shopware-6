<?php declare(strict_types=1);

/**
 * Lädt die durch Composer installierten Abhängigkeiten für die Tests.
 *
 * Der klare Abbruchhinweis verhindert schwer verständliche Folgefehler, wenn
 * Tests ohne einen vorherigen Composer-Installationsschritt gestartet werden.
 */
$autoloadDatei = dirname(__DIR__) . '/vendor/autoload.php';

if (!is_file($autoloadDatei)) {
    throw new RuntimeException(
        'Die Composer-Abhängigkeiten fehlen. Bitte zuerst „composer install“ im Plugin-Verzeichnis ausführen.'
    );
}

require $autoloadDatei;
