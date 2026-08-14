# Änderungsprotokoll

Alle wesentlichen Änderungen dieses Projekts werden hier dokumentiert. Das Format orientiert sich an „Keep a Changelog“; Versionen folgen der semantischen Versionierung.

## [0.1.2] – 2026-08-14

### Behoben

- Gekennzeichnete Hauptbilder behalten in Shopwares Produktgalerie ihre vollständige Größe sowie Zoom- und Sliderfunktion.

### Geändert

- Deutsches und englisches Frontend verwenden für den Deepfake-Status einheitlich `AI DEEPFAKE`.

## [0.1.1] – 2026-08-11

### Korrigiert

- Die in Shopware 6.6.10.22 und 6.7.13.0 identisch erzeugten Administration-Assets sind nun im Installations-ZIP enthalten. Eine normale Plugin-Installation benötigt dadurch keinen eigenen Administration-Build auf dem Zielserver.
- Die SQLite-Plattformerkennung ist mit Doctrine DBAL 3 und 4 kompatibel.

## [0.1.0] – 2026-08-11

### Hinzugefügt

- Shopware-6-Plugin-Grundgerüst für Shopware 6.6.10 und 6.7 mit PHP 8.2 oder neuer
- geschlossene Medienfelder für KI-Status, Labelposition und Erscheinungsbild
- deutsche und englische Storefront-Texte mit automatischer Verkaufskanal-Sprache und manueller Überschreibung
- barrierearme Kennzeichnung in Shopware-Thumbnails und lokaler Vorschau in der Medienverwaltung
- Erlebniswelten-Element für gekennzeichnete Hintergrundbilder mit sicherer Konfiguration
- manuell vorbereitbares, unveröffentlichtes Erlebniswelten-Layout zur KI-Philosophie
- datensparsamer Installations-, Update- und Deinstallationsablauf
- deutsche Betriebs-, Sicherheits-, Architektur- und Theme-Dokumentation sowie englische Kurzanleitung
- reproduzierbarer, positiv gelisteter Release-Build
- sicherer Erhalt globaler und verkaufskanalspezifischer Plugin-Einstellungen bei Deinstallation mit Datenerhalt und anschließender Reinstallation

### Geprüft

- Reale isolierte Matrix mit Shopware 6.6.10.22 und 6.7.13.0: Installation, Aktivierung, Administration- und Storefront-Build, Datenbankintegration, Storefront und Administration sowie Deinstallation mit und ohne Datenerhalt.
