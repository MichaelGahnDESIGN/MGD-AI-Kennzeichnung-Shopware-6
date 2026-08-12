# Öffentliche README und GitHub-Wiki Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Das allgemeine Shopware-Plugin erhält eine ausführliche öffentliche README und ein versioniertes sowie auf GitHub veröffentlichtes Wiki für Betreiber, Redakteure und Entwickler.

**Architecture:** Die README dient als verständlicher Schnellstart und verweist für Details auf thematisch getrennte Markdown-Seiten unter `docs/wiki/`. Ein Strukturtest schützt Seiteninventar, Navigation, lokale Links und den Ausschluss vertraulicher Angaben. Dieselben Dateien werden in das separate GitHub-Wiki-Repository übertragen.

**Tech Stack:** GitHub-Flavored Markdown, PHP 8.2+, PHPUnit 11, Git, GitHub CLI, GitHub-Wiki-Git-Repository

---

## Dateistruktur

**Ändern:**

- `README.md`: öffentlicher Einstieg für beide Zielgruppen
- `tests/Structure/DocumentationAndReleaseTest.php`: Wiki-, Sicherheits- und Linkvertrag

**Neu anlegen:**

- `docs/wiki/Home.md`: Wiki-Startseite
- `docs/wiki/Installation-und-Updates.md`: Installation, Backup und Update
- `docs/wiki/Bilder-kennzeichnen.md`: redaktioneller Arbeitsablauf
- `docs/wiki/Sprache-und-Gestaltung.md`: Sprache und Designwerte
- `docs/wiki/Erlebniswelten-und-Hintergrundbilder.md`: CMS-Elemente
- `docs/wiki/Themes-und-individuelle-Templates.md`: Integrationsvertrag
- `docs/wiki/Rechte-und-Rollen.md`: Berechtigungen
- `docs/wiki/Datenschutz-und-Sicherheit.md`: Schutzmodell
- `docs/wiki/Barrierefreiheit.md`: zugängliche Ausgabe
- `docs/wiki/Deinstallation-und-Wiederherstellung.md`: Lebenszyklus
- `docs/wiki/Fehlerbehebung.md`: diagnoseorientierte Hilfe
- `docs/wiki/Entwicklerarchitektur.md`: technische Bausteine
- `docs/wiki/Tests-und-Releaseprozess.md`: Qualität und Release
- `docs/wiki/FAQ.md`: häufige Fragen
- `docs/wiki/_Sidebar.md`: Navigation
- `docs/wiki/_Footer.md`: Projektlinks

## Task 1: Wiki-Vertrag testgetrieben festlegen

**Files:**

- Modify: `tests/Structure/DocumentationAndReleaseTest.php`
- Test: `tests/Structure/DocumentationAndReleaseTest.php`

- [ ] **Step 1: Seiteninventar ergänzen**

```php
/** @var list<string> */
private const REQUIRED_WIKI_DOCUMENTS = [
    'Home.md',
    'Installation-und-Updates.md',
    'Bilder-kennzeichnen.md',
    'Sprache-und-Gestaltung.md',
    'Erlebniswelten-und-Hintergrundbilder.md',
    'Themes-und-individuelle-Templates.md',
    'Rechte-und-Rollen.md',
    'Datenschutz-und-Sicherheit.md',
    'Barrierefreiheit.md',
    'Deinstallation-und-Wiederherstellung.md',
    'Fehlerbehebung.md',
    'Entwicklerarchitektur.md',
    'Tests-und-Releaseprozess.md',
    'FAQ.md',
    '_Sidebar.md',
    '_Footer.md',
];
```

- [ ] **Step 2: Failing Test schreiben**

```php
public function testPublicWikiIsCompleteNavigableAndFreeFromOperationalSecrets(): void
{
    $wikiDirectory = self::ROOT . '/docs/wiki';
    self::assertDirectoryExists($wikiDirectory);
    $combined = '';

    foreach (self::REQUIRED_WIKI_DOCUMENTS as $document) {
        $path = $wikiDirectory . '/' . $document;
        self::assertFileExists($path);
        $content = file_get_contents($path);
        self::assertIsString($content);
        self::assertStringNotContainsString('TODO', $content);
        self::assertStringNotContainsString('TBD', $content);
        $combined .= "\n" . $content;
    }

    $sidebar = file_get_contents($wikiDirectory . '/_Sidebar.md');
    self::assertIsString($sidebar);
    foreach (array_diff(self::REQUIRED_WIKI_DOCUMENTS, ['_Sidebar.md', '_Footer.md']) as $document) {
        self::assertStringContainsString(sprintf('](%s)', pathinfo($document, PATHINFO_FILENAME)), $sidebar);
    }

    foreach (['/Users/', 'Zauberwort', 'SynoToken', 'DATABASE_URL=', 'password=', 'token='] as $forbidden) {
        self::assertStringNotContainsString($forbidden, $combined);
    }
}
```

- [ ] **Step 3: RED belegen**

Run: `vendor/bin/phpunit tests/Structure/DocumentationAndReleaseTest.php --filter PublicWiki`

Expected: `FAIL`, weil `docs/wiki/` noch fehlt.

- [ ] **Step 4: Test committen**

```bash
git add tests/Structure/DocumentationAndReleaseTest.php
git commit -m "test: definiere öffentlichen Wiki-Vertrag"
```

## Task 2: README und Wiki-Navigation erstellen

**Files:**

- Modify: `README.md`
- Create: `docs/wiki/Home.md`
- Create: `docs/wiki/_Sidebar.md`
- Create: `docs/wiki/_Footer.md`

- [ ] **Step 1: README neu gliedern**

Die README erhält in dieser Reihenfolge: Titel und Badges; Kurzbeschreibung; Warnhinweise zu automatischer Erkennung und Rechtsberatung; Wiki-Navigation; Problem; Lösung; Funktionen; Voraussetzungen und Kompatibilität; Schnellinstallation; erste Kennzeichnung; Status und Sprache; Shopware/Erlebniswelten/Themes; Datenschutz; Barrierefreiheit; Update und Rückfall; Grenzen und Fehlerbehebung; Entwicklung und Tests; Mitwirken; verwandte Projekte; Lizenz und Impressum.

- [ ] **Step 2: Wiki-Startseite schreiben**

`Home.md` bietet getrennte Tabellen für Shopbetreiber, Redakteure sowie Entwickler und verlinkt jede geplante Themenseite.

- [ ] **Step 3: Navigation erstellen**

`_Sidebar.md` enthält alle 14 Inhaltsseiten als Wiki-Links ohne `.md`. `_Footer.md` verlinkt Repository, Releases, Sicherheitsbereich und GPL-Lizenz.

- [ ] **Step 4: RED erneut prüfen**

Run: `vendor/bin/phpunit tests/Structure/DocumentationAndReleaseTest.php --filter PublicWiki`

Expected: `FAIL` nur wegen der noch fehlenden Themenseiten.

- [ ] **Step 5: Zwischenstand committen**

```bash
git add README.md docs/wiki/Home.md docs/wiki/_Sidebar.md docs/wiki/_Footer.md
git commit -m "docs: strukturiere öffentlichen Einstieg"
```

## Task 3: Betreiber- und Redaktionshandbuch schreiben

**Files:**

- Create: `docs/wiki/Installation-und-Updates.md`
- Create: `docs/wiki/Bilder-kennzeichnen.md`
- Create: `docs/wiki/Sprache-und-Gestaltung.md`
- Create: `docs/wiki/Erlebniswelten-und-Hintergrundbilder.md`
- Create: `docs/wiki/Rechte-und-Rollen.md`
- Create: `docs/wiki/Barrierefreiheit.md`

- [ ] **Step 1: Installation dokumentieren**

ZIP- und CLI-Installation, Backup, Theme-Kompilierung, Cache, Verkaufskanalprüfung, Updates und Rückfall verständlich beschreiben. Befehle enthalten keine Zugangsdaten.

- [ ] **Step 2: Kennzeichnung dokumentieren**

Fünf Statuswerte, drei Medienfelder, Vorschau, native Speicheraktion, unveränderte Bilddatei und redaktionelle Verantwortung erklären.

- [ ] **Step 3: Sprache und Gestaltung dokumentieren**

`Automatisch`, Deutsch, Englisch, Verkaufskanal-Fallback, individuelle Medienwerte, globale Zahlenwerte und Positivlisten ohne freies CSS erklären.

- [ ] **Step 4: Erlebniswelten dokumentieren**

Hintergrundbild-Element, Bildmediumspflicht, dekorativ versus inhaltlich, Alternativtext und bewusst unverknüpfte KI-Philosophie-Seite erklären.

- [ ] **Step 5: Rechte und Barrierefreiheit dokumentieren**

Medien-/Custom-Field-Rechte von den fünf CMS-Erstellrechten trennen. `role="note"`, Deepfake-Zusatztext, kleine Medien, Kontrast und reale Theme-Prüfung erklären.

- [ ] **Step 6: Betreiberhandbuch committen**

```bash
git add docs/wiki/Installation-und-Updates.md docs/wiki/Bilder-kennzeichnen.md docs/wiki/Sprache-und-Gestaltung.md docs/wiki/Erlebniswelten-und-Hintergrundbilder.md docs/wiki/Rechte-und-Rollen.md docs/wiki/Barrierefreiheit.md
git commit -m "docs: ergänze Betreiber- und Redaktionshandbuch"
```

## Task 4: Technik, Sicherheit und Fehlerbehebung schreiben

**Files:**

- Create: `docs/wiki/Themes-und-individuelle-Templates.md`
- Create: `docs/wiki/Datenschutz-und-Sicherheit.md`
- Create: `docs/wiki/Deinstallation-und-Wiederherstellung.md`
- Create: `docs/wiki/Fehlerbehebung.md`
- Create: `docs/wiki/Entwicklerarchitektur.md`
- Create: `docs/wiki/Tests-und-Releaseprozess.md`
- Create: `docs/wiki/FAQ.md`

- [ ] **Step 1: Theme-Vertrag erklären**

`sw_thumbnails`, Shopware-Medienobjekte, unveränderte responsive Attribute und die Grenzen roher `<img>`-Tags sowie vollständig ersetzter Thumbnail-Templates dokumentieren. HTTP 404 ausdrücklich als Datei-, Medien- oder URL-Problem erklären.

- [ ] **Step 2: Datenschutz und Lebenszyklus erklären**

Lokale Custom Fields, keine externe Bildübertragung, keine Telemetrie, Shopware-Rechte, beide Varianten von **Benutzerdaten behalten**, Konfigurationssnapshot und mögliche ungenutzte Medien-JSON-Werte dokumentieren.

- [ ] **Step 3: Diagnoseleitfaden schreiben**

Reihenfolge: Bild-URL/HTTP-Status; Medieneintrag und Datei; `sw_thumbnails` oder rohes HTML; Status und Sprache; Cache und Theme; Browserfehler; erst danach CSS und Overrides.

- [ ] **Step 4: Architektur und Tests erklären**

Domain-Enums, Normalizer, Resolver, Viewmodel, Twig, Installer, Konfiguration und Admin-Komponenten beschreiben. Exakte Befehle aus `composer.json`, `package.json` und `scripts/build-release.sh` verwenden.

- [ ] **Step 5: FAQ schreiben**

Automatische Erkennung, Rechtsberatung, Sprache, Bilddateiveränderung, fehlendes Label, verschwundenes Bild, rohe HTML-Bilder, kleine Bilder, Deinstallation, Shopware-Versionen und Themes beantworten.

- [ ] **Step 6: GREEN prüfen**

Run: `vendor/bin/phpunit tests/Structure/DocumentationAndReleaseTest.php --filter PublicWiki`

Expected: `OK` ohne Skips.

- [ ] **Step 7: Technikseiten committen**

```bash
git add docs/wiki
git commit -m "docs: ergänze Technik und Fehlerbehebung"
```

## Task 5: Linkvertrag und Gesamtqualität absichern

**Files:**

- Modify: `tests/Structure/DocumentationAndReleaseTest.php`
- Test: `tests/Structure/DocumentationAndReleaseTest.php`

- [ ] **Step 1: lokalen Linktest ergänzen**

```php
public function testReadmeAndWikiLocalLinksResolveToExistingTargets(): void
{
    $documents = [self::ROOT . '/README.md'];
    foreach (self::REQUIRED_WIKI_DOCUMENTS as $document) {
        $documents[] = self::ROOT . '/docs/wiki/' . $document;
    }

    foreach ($documents as $document) {
        $content = file_get_contents($document);
        self::assertIsString($content);
        preg_match_all('/\[[^]]+]\(([^)]+)\)/', $content, $matches);
        foreach ($matches[1] as $target) {
            if (preg_match('#^(?:https?://|mailto:|#)#', $target) === 1) {
                continue;
            }
            $path = rawurldecode(explode('#', $target, 2)[0]);
            if (str_starts_with($document, self::ROOT . '/docs/wiki/') && !str_contains(basename($path), '.')) {
                $path .= '.md';
            }
            self::assertFileExists(dirname($document) . '/' . $path, sprintf('Defekter Link %s in %s', $target, $document));
        }
    }
}
```

- [ ] **Step 2: RED und GREEN belegen**

Run: `vendor/bin/phpunit tests/Structure/DocumentationAndReleaseTest.php --filter LocalLinks`

Expected: zunächst `FAIL` bei einem echten Linkfehler; nach Korrektur `OK` ohne Abschwächung des Tests.

- [ ] **Step 3: vollständige lokale Prüfung ausführen**

```bash
composer validate --strict
composer test:unit
composer analyse:phpstan
composer check:style
npm run test:administration
npm run test:storefront
vendor/bin/phpunit --fail-on-skipped tests/Structure/DocumentationAndReleaseTest.php
git diff --check
```

Expected: alle Befehle Exit `0`.

- [ ] **Step 4: Geheimnis- und Platzhalterscan ausführen**

Run: `rg -n 'TODO|TBD|Zauberwort|SynoToken|DATABASE_URL=|password=|token=|/Users/' README.md docs/wiki`

Expected: keine Treffer.

- [ ] **Step 5: Linkvertrag committen**

```bash
git add tests/Structure/DocumentationAndReleaseTest.php README.md docs/wiki
git commit -m "test: schütze öffentliche Dokumentationslinks"
```

## Task 6: GitHub und Wiki veröffentlichen

**Files:**

- Source: `docs/wiki/*.md`
- Destination: `MGD-AI-Kennzeichnung-Shopware-6.wiki.git`

- [ ] **Step 1: Repository-Metadaten setzen**

```bash
gh repo edit MichaelGahnDESIGN/MGD-AI-Kennzeichnung-Shopware-6 --description "Shopware-6-Plugin zur transparenten, barrierearmen Kennzeichnung KI-generierter und KI-bearbeiteter Bilder" --homepage "https://github.com/MichaelGahnDESIGN/MGD-AI-Kennzeichnung-Shopware-6/wiki" --enable-wiki=true
gh repo edit MichaelGahnDESIGN/MGD-AI-Kennzeichnung-Shopware-6 --add-topic shopware --add-topic shopware-6 --add-topic plugin --add-topic ai-labeling --add-topic image-labeling --add-topic accessibility --add-topic privacy --add-topic gdpr --add-topic php --add-topic twig
```

- [ ] **Step 2: Hauptrepository pushen**

Run: `git push origin main`

Expected: Push erfolgreich.

- [ ] **Step 3: Wiki sicher synchronisieren**

```bash
wiki_dir="$(mktemp -d)"
git clone "https://github.com/MichaelGahnDESIGN/MGD-AI-Kennzeichnung-Shopware-6.wiki.git" "$wiki_dir"
find "$wiki_dir" -maxdepth 1 -type f -name '*.md' -delete
cp docs/wiki/*.md "$wiki_dir"/
git -C "$wiki_dir" add --all
git -C "$wiki_dir" commit -m "docs: veröffentliche vollständiges Shopware-Handbuch"
wiki_branch="$(git -C "$wiki_dir" branch --show-current)"
git -C "$wiki_dir" push origin "$wiki_branch"
```

- [ ] **Step 4: öffentliche Wiki-Seiten prüfen**

Mit `curl --fail --location` mindestens `Home`, `Installation-und-Updates`, `Bilder-kennzeichnen`, `Fehlerbehebung`, `Entwicklerarchitektur` und `Tests-und-Releaseprozess` auf HTTP 200 prüfen.

- [ ] **Step 5: GitHub-CI abwarten**

```bash
run_id="$(gh run list --repo MichaelGahnDESIGN/MGD-AI-Kennzeichnung-Shopware-6 --branch main --limit 1 --json databaseId --jq '.[0].databaseId')"
gh run watch "$run_id" --repo MichaelGahnDESIGN/MGD-AI-Kennzeichnung-Shopware-6 --exit-status
```

Expected: Qualitätsmatrix und Geheimnisscan erfolgreich.

- [ ] **Step 6: Abschluss verifizieren**

```bash
git status --short --branch
gh repo view MichaelGahnDESIGN/MGD-AI-Kennzeichnung-Shopware-6 --json url,visibility,homepageUrl,hasWikiEnabled,repositoryTopics
```

Expected: Hauptrepository sauber und synchron, `PUBLIC`, Wiki aktiv, Homepage und Themen gesetzt.

