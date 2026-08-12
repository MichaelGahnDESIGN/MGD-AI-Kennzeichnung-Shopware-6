# Design: Öffentliche README und GitHub-Wiki

## Ausgangslage

`MGD AI Kennzeichnung Shopware 6` ist ein öffentliches, allgemein nutzbares Shopware-6-Plugin. Das Repository enthält bereits eine funktionsorientierte README und mehrere technische Dokumente. Für eine verständliche Open-Source-Veröffentlichung fehlt noch eine klar gegliederte Dokumentation für zwei gleichwertige Zielgruppen:

1. Shopbetreiber, Administratoren und Redakteure, die das Plugin installieren und Bilder kennzeichnen möchten.
2. Entwickler und Agenturen, die Integration, Architektur, Tests und Theme-Kompatibilität verstehen müssen.

Die neue Dokumentation orientiert sich in Ton und Navigation an Michaels öffentlichen Projekten, besonders an `MGD AI Kennzeichnung WordPress`. Shopware-spezifische Konzepte werden eigenständig und ohne WordPress-Begriffe erklärt.

## Ziele

- Neue Nutzer verstehen innerhalb weniger Minuten Zweck, Grenzen und Installation.
- Redakteure können Bilder sicher kennzeichnen und Sprache sowie Darstellung nachvollziehen.
- Betreiber erhalten klare Hinweise zu Backup, Update, Rechten, Datenschutz und Rückfall.
- Entwickler finden Architektur, Integrationspunkte, Testbefehle und Releaseprozess ohne Quellcode-Suche.
- Das GitHub-Wiki ist direkt navigierbar und wird zusätzlich im Hauptrepository versioniert.
- Dokumentation enthält keine TableGuard-Zugangsdaten, internen Serverpfade, Tokens oder personenbezogenen Daten.
- Die dokumentierte Kompatibilität entspricht ausschließlich den tatsächlich geprüften Shopware-Versionen.

## Nicht-Ziele

- Keine Rechtsberatung oder Garantie zur Kennzeichnungspflicht.
- Keine automatische KI-Erkennung oder Anbindung eines externen Bilderkennungsdienstes.
- Keine allgemeine Zusage für rohe HTML-Bilder oder Theme-Templates, die Shopwares Medien- und Thumbnail-System vollständig umgehen.
- Keine Änderung des Plugin-Verhaltens im Rahmen dieser Dokumentationsaufgabe.
- Keine Aufnahme des TableGuard-Shops als technische Voraussetzung oder fest verdrahtetes Zielsystem.

## README-Konzept

Die README bleibt der schnelle Einstieg und enthält folgende Reihenfolge:

1. Projekttitel, kurze Nutzenbeschreibung und Status-Badges.
2. Deutlicher Hinweis: keine automatische Erkennung, keine Rechtsberatung.
3. Dokumentationsnavigation mit den wichtigsten Wiki-Einstiegen.
4. Problem und Lösung in verständlicher Sprache.
5. Funktionsübersicht.
6. Voraussetzungen und geprüfte Kompatibilität.
7. Schnellinstallation per ZIP und CLI.
8. Erste Kennzeichnung in der Medienverwaltung.
9. Status-, Sprach-, Positions- und Theme-Tabelle.
10. Hinweise zu Erlebniswelten, Standard-Shopware und eigenen Themes.
11. Datenschutz, Sicherheit und Barrierefreiheit.
12. Update, Deinstallation und Rückfall.
13. Grenzen und Fehlerbehebung.
14. Entwicklerbereich mit Architektur, Tests und Releasebau.
15. Beiträge, Sicherheitsmeldungen, Lizenz, verwandte Projekte und Impressum.

Technische Details werden nicht vollständig dupliziert, sondern auf passende Wiki-Seiten und bestehende Repository-Dokumente verlinkt.

## Wiki-Konzept

Die versionierte Quelle liegt unter `docs/wiki/`. Jede Seite behandelt genau ein Thema. Das echte GitHub-Wiki wird aus denselben Markdown-Dateien aufgebaut.

### Seiten

| Datei | Zweck |
| --- | --- |
| `Home.md` | Überblick, Zielgruppen und zentrale Navigation |
| `Installation-und-Updates.md` | ZIP, CLI, Backup, Update und Cache/Theme-Bau |
| `Bilder-kennzeichnen.md` | Status, Position, Theme, Vorschau und Speichern |
| `Sprache-und-Gestaltung.md` | Automatisch, Deutsch, Englisch und sichere Darstellungsgrenzen |
| `Erlebniswelten-und-Hintergrundbilder.md` | CMS-Elemente, KI-Philosophie und Alternativtexte |
| `Themes-und-individuelle-Templates.md` | Shopware-Thumbnail-Vertrag, unterstützte und umgehende Ausgabepfade |
| `Rechte-und-Rollen.md` | Medienrechte, Custom Fields und CMS-Berechtigungen |
| `Datenschutz-und-Sicherheit.md` | Datenfluss, Positivlisten, keine Drittanbieter und Sicherheitsmeldungen |
| `Barrierefreiheit.md` | Semantik, Screenreader, kleine Medien und redaktionelle Verantwortung |
| `Deinstallation-und-Wiederherstellung.md` | Benutzerdaten behalten, Snapshot, Rückfall und Restwerte |
| `Fehlerbehebung.md` | Cache, Theme, fehlende Labels, verschwundene Bilder und Diagnose |
| `Entwicklerarchitektur.md` | Komponenten, Services, Twig, DAL und Konfiguration |
| `Tests-und-Releaseprozess.md` | PHPUnit, Node, PHPStan, CI, reproduzierbares ZIP und Shopware CLI |
| `FAQ.md` | Kurze Antworten auf wiederkehrende Fragen |
| `_Sidebar.md` | Beständige Wiki-Navigation |
| `_Footer.md` | Projekt-, Support-, Lizenz- und Sicherheitslinks |

## Dokumentation der Integrationsgrenze

Das Plugin integriert Kennzeichnungen über Shopwares Medienobjekte und das zentrale `sw_thumbnails`-Template. Diese Grenze wird in README, Theme-Seite und Fehlerbehebung eindeutig erklärt:

- Standardmäßige Shopware-Thumbnails und die plugin-eigenen CMS-Elemente werden unterstützt.
- Ein Theme darf Attribute und responsive Quellen weiterhin durch Shopware erzeugen lassen.
- Direkt in redaktionelles HTML eingetragene `<img src="…">`-Elemente enthalten kein Shopware-Medienobjekt. Das Plugin kann dort keinen Status auflösen und kein Label ergänzen.
- Vollständig ersetzte Thumbnail-Templates können die Erweiterung ebenfalls umgehen.
- Ein fehlendes Bild mit HTTP 404 ist ein Datei-, Medien- oder URL-Problem und kein gewünschtes Labelverhalten.
- Fehlerbehebung beginnt mit HTTP-Status, Medieneintrag, Datei und Cache, bevor CSS untersucht wird.

Diese Erklärung bleibt allgemein. TableGuard dient nur als anonymisierter produktiver Referenztest und wird nicht zur Voraussetzung erklärt.

## GitHub-Veröffentlichung

- Das Repository bleibt öffentlich und das Wiki bleibt aktiviert.
- Die README verlinkt auf das echte Wiki und auf `docs/wiki/` als versionierte Quelle.
- Repository-Beschreibung, Homepage-Link und Themen werden passend ergänzt.
- Vorgesehene Themen: `shopware`, `shopware-6`, `plugin`, `ai-labeling`, `image-labeling`, `accessibility`, `privacy`, `gdpr`, `php`, `twig`.
- Wiki-Seiten werden in ein separates GitHub-Wiki-Repository übertragen und dort committed und gepusht.
- Änderungen am Hauptrepository erfolgen über Git, Tests und die vorhandene CI.

## Qualitäts- und Sicherheitsprüfung

Vor Veröffentlichung werden geprüft:

- alle internen und externen Markdown-Links,
- Übereinstimmung von README- und Wiki-Navigation,
- keine Platzhalter wie `TODO`, `TBD` oder erfundene Funktionen,
- keine Zugangsdaten, Tokens, internen Serverpfade oder lokalen Backup-Inhalte,
- korrekte Versions- und Kompatibilitätsangaben,
- verständliche Sprache für Nicht-Programmierer,
- klare Trennung zwischen Betreiber- und Entwicklerhinweisen,
- bestehende Unit-, Administration-, Storefront-, Struktur- und statische Tests,
- GitHub-CI nach dem Push.

## Erfolgskriterien

Die Aufgabe ist abgeschlossen, wenn:

1. die umfangreiche README im öffentlichen Hauptrepository verfügbar ist,
2. alle geplanten Wiki-Seiten öffentlich erreichbar sind,
3. `docs/wiki/` dieselben Inhalte versioniert enthält,
4. Repository-Metadaten und Themen gesetzt sind,
5. Links und Sicherheitsprüfung erfolgreich sind,
6. lokale Tests und GitHub-CI grün sind,
7. keine shopspezifischen Geheimnisse oder unbelegten Zusagen veröffentlicht wurden.

