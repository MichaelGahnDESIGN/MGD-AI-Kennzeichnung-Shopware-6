# Tests und Releaseprozess

Der Qualitätsvertrag prüft Fachlogik, Administration, Storefront-Geometrie, Barrierefreiheit, Dokumentation, Sicherheit und das ausgelieferte ZIP.

## Lokale Einrichtung

```bash
composer install
npm ci
```

## PHP-Tests

```bash
composer validate --strict
composer test:unit
composer analyse:phpstan
composer check:style
```

Die Unit-Suite benötigt keine Datenbank. PHPStan läuft mit dem projektspezifischen Maximalvertrag und erhöhtem Speicherlimit. PHP-CS-Fixer prüft produktiven Laufzeitcode ohne Änderungen vorzunehmen.

## Administration

```bash
npm run test:administration
```

Geprüft werden unter anderem:

- vollständige relative ESM-Importpfade,
- Shopware-TwigJS-Parent-Syntax,
- deutsche/englische Snippet-Parität,
- sichere Vorschauwerte,
- Bildtypprüfung,
- Shopware-6.6-/6.7-Kontextadapter,
- authentifizierte Admin-Aktion,
- getrennte CMS-Komponenten.

## Storefront und Barrierefreiheit

```bash
npm run test:storefront
```

Ein Headless-Chrome-Test prüft reale Geometrie, kleine Medien und den Accessibility Tree. Dazu gehören Fill- und Intrinsic-Kontexte, Logos, LTR/RTL, natürliche Bildgrößen und vollständig innerhalb liegende sichtbare Labels.

## Struktur- und Releasevertrag

```bash
vendor/bin/phpunit --fail-on-skipped tests/Structure/DocumentationAndReleaseTest.php
bash scripts/build-release.sh
```

Der Strukturtest prüft:

- CI-Matrix und fest gepinnte Actions,
- Pflichtdokumentation und Wiki,
- Lizenz- und Composer-Metadaten,
- Administrationsassets im Paket,
- Ausschluss von Entwicklungs- und Geheimnisdateien,
- sicheren `dist`-Pfad,
- atomischen Build und Signal-Cleanup,
- reproduzierbare ZIP-Inhalte.

## Integrationstests

Integrationstests benötigen eine ausdrücklich freigegebene, isolierte Datenbank. Der Datenbankname muss klar `test` oder `testing` enthalten. Ohne Freigabeflag bricht der Composer-Befehl vor PHPUnit ab.

Setzen Sie das Freigabeflag `MGD_SHOPWARE_INTEGRATION_TESTS` auf `1` und
übergeben Sie die isolierte Datenbankverbindung über die geschützte Variable
`MGD_SHOPWARE_TEST_DATABASE_URL`. Führen Sie anschließend aus:

```bash
composer test:integration
```

Die Verbindungszeichenfolge wird bewusst nicht als kopierbares Beispiel gezeigt. Produktive Zugangsdaten dürfen nie in Shell-Historie, Dokumentation oder CI-Logs gelangen.

Geprüft werden echte DAL-, Kernel-, CMS-, Controller-, Lifecycle- und Konfigurationsretentionspfade. Tests laufen transaktional beziehungsweise gegen getrennte, kurzlebige Datenbanken.

## GitHub-CI

Die Workflowmatrix prüft:

| Shopware | PHP |
| --- | --- |
| 6.6.10 | 8.2 und 8.4 |
| 6.7 | 8.2 und 8.4 |

Zusätzlich läuft ein Geheimnisscan über Historie und Arbeitsbaum. Nur der Shopware-6.7-/PHP-8.4-Job baut, validiert und veröffentlicht das CI-Artefakt.

## Reproduzierbares Release

```bash
bash scripts/build-release.sh
```

Das Skript:

1. prüft Quell- und Zielpfade,
2. verweigert Symlinks und Spezialdateien am Ausgabeort,
3. arbeitet in einem temporären Verzeichnis auf demselben Dateisystem,
4. übernimmt nur freigegebene Laufzeitdateien,
5. injiziert die Shopware-Paketversion nur in das ZIP,
6. setzt reproduzierbare Zeitstempel und Reihenfolge,
7. ersetzt das Zielarchiv atomisch,
8. bereinigt bei Signalen mit eindeutigen Exitcodes.

Das Ergebnis wird mit Shopware CLI validiert. Release-Datei und SHA-256 werden auf GitHub veröffentlicht.

## Manuelle Matrix

Vor einem Release gehören zusätzlich echte Installation, Aktivierung, Administration-/Storefront-Build, Medienvorschau, Sprache, Erlebniswelten, Browserpfade und Deinstallation mit und ohne Datenerhalt in frischen Shopware-6.6- und 6.7-Umgebungen dazu.
