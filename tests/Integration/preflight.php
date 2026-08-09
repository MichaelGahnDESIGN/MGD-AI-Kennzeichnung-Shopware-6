<?php

declare(strict_types=1);

/*
 * Dieser vorgeschaltete Prozess verhindert ein irreführendes grünes Ergebnis:
 * PHPUnit 11 meldet einen Skip aus setUpBeforeClass(), setzt dafür trotz
 * --fail-on-skipped jedoch keinen Fehlercode. Ohne ausdrückliche Freigabe wird
 * PHPUnit deshalb gar nicht erst gestartet. Zugangsdaten werden weder gelesen
 * noch ausgegeben; die eigentliche URL-Sicherheitsprüfung bleibt im Testcode.
 */
$permission = $_SERVER['MGD_SHOPWARE_INTEGRATION_TESTS']
    ?? $_ENV['MGD_SHOPWARE_INTEGRATION_TESTS']
    ?? getenv('MGD_SHOPWARE_INTEGRATION_TESTS');

if ($permission === '1') {
    exit(0);
}

$task13Command = "MGD_SHOPWARE_INTEGRATION_TESTS=1 MGD_SHOPWARE_TEST_DATABASE_URL='mysql://.../mgd_shopware_test' composer test:integration";
fwrite(
    STDERR,
    "Integrationstests wurden nicht ausgeführt. Task 13 ausdrücklich freigeben mit:\n"
    . $task13Command
    . "\nMGD_SHOPWARE_TEST_DATABASE_URL muss auf eine isolierte MySQL-Testdatenbank zeigen.\n",
);

exit(1);
