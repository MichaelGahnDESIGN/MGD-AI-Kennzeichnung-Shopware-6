# Mitwirken

Danke für das Interesse an MGD AI Kennzeichnung Shopware 6. Beiträge sollen verständlich, sicher und für Shopware 6.6.10 sowie 6.7 wartbar bleiben.

## Vor einer Änderung

1. Beschreiben Sie Problem und gewünschtes Verhalten in einem Issue, ohne vertrauliche Shopdaten zu veröffentlichen.
2. Verwenden Sie einen eigenen Branch und kleine, thematisch klare Commits.
3. Schreiben oder ändern Sie zuerst einen Test, der ohne die Korrektur fehlschlägt.
4. Halten Sie Funktionen, Ansichten und Einstellungen in sinnvoll benannten Einzeldateien.

## Quellcode und Datenschutz

Kommentare und ausführliche technische Dokumentation werden auf Deutsch in UTF-8 geschrieben. Namen im öffentlichen Code dürfen technisch englisch sein. Verarbeiten und speichern Sie nur Daten, die für die Kennzeichnung notwendig sind. Neue externe Übertragungen, Tracking, Telemetrie oder automatische Bildanalyse gehören nicht zum stillschweigenden Umfang und benötigen vorab eine eigene Datenschutz- und Sicherheitsentscheidung.

Keine Zugangsdaten, privaten Adressen, Kundendaten, Datenbankkopien, Medien aus produktiven Shops oder Protokolle committen. Tests verwenden ausschließlich künstliche Daten. Abhängigkeiten müssen aus einer nachvollziehbaren, gepflegten Quelle stammen.

## Lokale Prüfung

Für den vollständigen Entwicklungs- und Releasevertrag werden Bash, PHP, Composer, Node.js mit npm und Python 3 benötigt. Das Release-Skript verwendet außerdem die üblichen Unix-Werkzeuge `mktemp`, `install`, `find`, `grep`, `sort`, `dirname`, `rm`, `awk` und `shasum`. Unter Linux stammen viele davon aus Coreutils; auf macOS sind kompatible Systemvarianten vorhanden. Python erzeugt das ZIP über sein Standardmodul `zipfile`, daher ruft das Skript selbst weder `zip` noch `unzip` auf. `unzip` beziehungsweise `zipinfo` sind nur für die manuelle Paketkontrolle hilfreich.

Installieren Sie Entwicklungsabhängigkeiten mit Composer und führen Sie mindestens aus:

```bash
composer validate --strict
vendor/bin/phpunit --testsuite unit
vendor/bin/phpstan analyse src tests --level=max
npm run test:administration
npm run test:storefront
bash scripts/build-release.sh
vendor/bin/phpunit tests/Structure/DocumentationAndReleaseTest.php
```

Integrationstests dürfen ausschließlich gegen eine ausdrücklich benannte, isolierte Testdatenbank laufen. Niemals eine produktive Datenbank-URL verwenden. Die reale Versionsmatrix folgt dem dafür vorgesehenen, getrennten Qualitätsschritt.

## Pull Request

Der Pull Request erklärt Nutzen, Risiken, Testnachweise und mögliche Rückfallmaßnahmen. Änderungen an Datenhaltung, Berechtigungen, Templates, Lebenszyklus oder Release-Paket benötigen eine besonders klare Begründung. Aktualisieren Sie bei sichtbaren oder betrieblichen Änderungen README, Fachdokumentation und CHANGELOG.

Mit einem Beitrag bestätigen Sie, dass Sie ihn unter `GPL-2.0-or-later` bereitstellen dürfen.
