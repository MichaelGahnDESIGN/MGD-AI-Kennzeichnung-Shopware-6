# Deinstallation und Wiederherstellung

Vor Deinstallation oder Wiederherstellung immer einen neuen Snapshot des aktuellen Zustands anlegen. Die Shopware-Option **Benutzerdaten behalten** bestimmt das gewünschte Verhalten.

## Mit Benutzerdaten behalten

Das Plugin erhält:

- sein Custom-Field-Set,
- die Relation zur Medienentität,
- vorhandene Medienwerte,
- globale und verkaufskanalspezifische Plugin-Konfiguration.

Shopware blendet Konfiguration inaktiver Plugins aus. Ein leeres Ergebnis unmittelbar nach der Deinstallation bedeutet daher nicht automatisch, dass die Datenbankzeile gelöscht wurde.

## Warum ein Konfigurationssnapshot nötig ist

Shopware 6.6 schreibt bei einer späteren Plugin-Reinstallation zunächst die Standardwerte aus `config.xml`, auch wenn zuvor erhaltene Konfigurationszeilen vorhanden sind. Das Plugin legt deshalb beim Keep-Zyklus einen eng begrenzten Snapshot seiner exakt bekannten Konfigurationsschlüssel und Verkaufskanalbereiche an.

Bei der Reinstallation:

1. prüft es Eigentum und Schema des Snapshots,
2. stellt nur erlaubte Schlüssel und geprüfte Typen wieder her,
3. verifiziert den Datenbankzustand innerhalb derselben Transaktion,
4. rollt bei Abweichung vollständig zurück,
5. entfernt den Snapshot erst nach erfolgreicher Prüfung beziehungsweise ersetzt ihn beim nächsten Keep-Zyklus.

Fremde Systemkonfiguration wird nicht übernommen.

## Ohne Benutzerdaten behalten

Das Plugin entfernt:

- sein eindeutig zugeordnetes Custom-Field-Set,
- globale und verkaufskanalspezifische Werte seiner bekannten Konfigurationsschlüssel,
- seine eigentumsgeprüfte Snapshot-Tabelle.

Fremde Custom Fields, Konfigurationen, Tabellen und Bilddateien werden nicht gelöscht.

## Mögliche Restwerte an Medien

Shopware speichert Custom-Field-Werte als JSON an der Medienentität. Das automatische Entfernen einzelner Schlüssel aus jedem Medium wäre eine umfangreiche Massenänderung mit höherem Risiko. Deshalb können nach einer Deinstallation ohne Datenerhalt technisch ungenutzte Schlüssel verbleiben.

Wer diese Werte vollständig entfernen muss, sollte:

1. Medienwerte inventarisieren,
2. Datenbank sichern,
3. ein getestetes, eng begrenztes Bereinigungsskript verwenden,
4. Ergebnis und Rückfall prüfen.

## KI-Philosophie-Seite

Eine vorbereitete Erlebniswelt enthält redaktionelle Inhalte und wird nicht automatisch entfernt. Unerwarteter Inhaltsverlust wäre riskanter als eine erhaltene, unverknüpfte Seite. Prüfen und löschen Sie sie bei Bedarf bewusst in Shopware.

## Sicherer Rückfall

1. aktuellen Stand sichern,
2. Plugin deaktivieren,
3. Cache leeren und Theme kompilieren,
4. Storefront prüfen,
5. falls nötig Plugin deinstallieren,
6. Datenbank und Plugin-Dateien aus dem geprüften Backup wiederherstellen,
7. Pluginstatus, Migrationen, Cache und Theme erneut prüfen,
8. Administration und wichtige Verkaufskanäle testen.

Eine Wiederherstellung sollte nie erstmals im Produktivsystem erprobt werden.

