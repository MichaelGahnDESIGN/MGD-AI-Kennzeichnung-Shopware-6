# Produktgalerie und „AI DEEPFAKE“ Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Shopwares Produktgalerie zeigt gekennzeichnete Hauptbilder wieder in voller Größe und das deutsche sowie englische Frontend verwendet einheitlich `AI DEEPFAKE`.

**Architecture:** Der vorhandene Fill-Rahmen erhält eine ausschließlich auf direkte Kinder von `.gallery-slider-item` begrenzte absolute Positionierung. Die sichtbare Deepfake-Bezeichnung wird in allen vier regionalen und neutralen Storefront-Snippetdateien vereinheitlicht; Administration und interne Statuswerte bleiben unverändert.

**Tech Stack:** Shopware 6.6/6.7, Twig, SCSS, PHP 8.2+, PHPUnit, Node Test Runner, Chrome DevTools Protocol, reproduzierbares ZIP, Git, kontrollierter TableGuard-Live-Playtest

---

## Dateiverantwortung

- `tests/Storefront/LabelTemplateTest.php`: schützt die vier Storefronttexte und den festen Galerie-CSS-Vertrag.
- `tests/Storefront/storefront-layout.test.mjs`: reproduziert die kollabierende Galeriehöhe mit einem auto-hohen, durch Mindesthöhe begrenzten Shopware-Slot.
- `src/Resources/app/storefront/src/scss/component/_ai-image-label.scss`: enthält ausschließlich die kontextspezifische Größenreparatur.
- `src/Resources/snippet/de-DE/storefront.de-DE.json`: regionaler deutscher Frontendtext.
- `src/Resources/snippet/de-DE/storefront.de.json`: neutraler deutscher Frontendtext.
- `src/Resources/snippet/en-GB/storefront.en-GB.json`: regionaler englischer Frontendtext.
- `src/Resources/snippet/en-GB/storefront.en.json`: neutraler englischer Frontendtext.
- `README.md`, `docs/wiki/Bilder-kennzeichnen.md`, `CHANGELOG.md`: öffentlicher Bedien- und Versionshinweis.
- `composer.json`: Releaseversion `0.1.2`.

### Task 1: Regressionsverträge rot machen

**Files:**
- Modify: `tests/Storefront/LabelTemplateTest.php`
- Modify: `tests/Storefront/storefront-layout.test.mjs`
- Test: `tests/Storefront/LabelTemplateTest.php`
- Test: `tests/Storefront/storefront-layout.test.mjs`

- [ ] **Step 1: Vier Frontendtexte ausdrücklich festlegen**

Ergänze in `testGermanAndEnglishStorefrontSnippetsAreCompleteAndStructurallyEqual()` nach den bestehenden Generated-Assertions:

```php
self::assertSame('AI DEEPFAKE', $this->snippetValue($german, 'mgd-ai-image-labels.status.deepfake'));
self::assertSame('AI DEEPFAKE', $this->snippetValue($neutralGerman, 'mgd-ai-image-labels.status.deepfake'));
self::assertSame('AI DEEPFAKE', $this->snippetValue($english, 'mgd-ai-image-labels.status.deepfake'));
self::assertSame('AI DEEPFAKE', $this->snippetValue($neutralEnglish, 'mgd-ai-image-labels.status.deepfake'));
```

- [ ] **Step 2: Festen Galerie-CSS-Vertrag verlangen**

Ergänze in `testComponentAssetsUseOnlyFixedSafeClassesAndLocalStyles()`:

```php
self::assertStringContainsString(
    '.gallery-slider-item > .mgd-ai-labeled-media--fill',
    $component,
);
self::assertMatchesRegularExpression(
    '/\.gallery-slider-item\s*>\s*\.mgd-ai-labeled-media--fill\s*\{[^}]*position:\s*absolute;[^}]*inset:\s*0;/s',
    $component,
);
```

- [ ] **Step 3: Echte kollabierende Geometrie nachstellen**

Ändere nur beim vorhandenen `gallery-cover`-Paar den Slot auf Shopwares entscheidenden Vertrag: Der Slot besitzt `position: relative`, `width: 300px` und `min-height: 180px`, aber keine feste `height`. Das direkte Originalbild bleibt absolut mit `inset: 0; width: 100%; height: 100%`. Der umschlossene Pfad muss nach der Reparatur dieselben 300 × 180 Pixel besitzen.

Verwende im Testdokument:

```css
.geometry-gallery-cover .geometry-slot {
    position: relative;
    width: 300px;
    min-height: 180px;
}
```

und ergänze den festen Shopware-Klassennamen an beide Galerie-Slots:

```javascript
function presentationPair(id, layout, mediaClass, slotClass = 'geometry-slot') {
    const original = `<div id="${id}-original-slot" class="${slotClass}"><div id="${id}-original" class="${mediaClass}"></div></div>`;
    const wrapped = `<div id="${id}-wrapped-slot" class="${slotClass}"><div id="${id}-wrapper" class="mgd-ai-labeled-media mgd-ai-labeled-media--${layout}"><div id="${id}-wrapped" class="${mediaClass}"></div><div class="mgd-ai-labeled-media__overlay"></div></div></div>`;
    return `<div class="geometry-pair geometry-${id}">${original}${wrapped}</div>`;
}
```

Der Aufruf lautet:

```javascript
${presentationPair('gallery-cover', 'fill', 'gallery-slider-image', 'geometry-slot gallery-slider-item')}
```

- [ ] **Step 4: RED nachweislich ausführen**

Run:

```bash
vendor/bin/phpunit tests/Storefront/LabelTemplateTest.php
npm run test:storefront
```

Expected: PHP scheitert an `AI DEEPFAKE`; der Browsertest scheitert beim `gallery-cover`-Fill-Rahmen mit Höhe `0` statt `180`.

- [ ] **Step 5: Testcommit erstellen**

```bash
git add tests/Storefront/LabelTemplateTest.php tests/Storefront/storefront-layout.test.mjs
git diff --cached --check
git commit -m "test: reproduziere kollabierende Produktgalerie"
```

### Task 2: Minimalen Galerie- und Textfix implementieren

**Files:**
- Modify: `src/Resources/app/storefront/src/scss/component/_ai-image-label.scss`
- Modify: `src/Resources/snippet/de-DE/storefront.de-DE.json`
- Modify: `src/Resources/snippet/de-DE/storefront.de.json`
- Modify: `src/Resources/snippet/en-GB/storefront.en-GB.json`
- Modify: `src/Resources/snippet/en-GB/storefront.en.json`

- [ ] **Step 1: Shopware-Galerieslot wieder zum direkten Größenbezug machen**

Ergänze unmittelbar nach `.mgd-ai-labeled-media--fill`:

```scss
// Shopwares Produktgalerie positioniert das Hauptbild absolut und leitet die
// Höhe aus dem Galerieslot ab. Der Rahmen übernimmt deshalb ausschließlich als
// direktes Slot-Kind dieselbe Fläche, ohne andere Fill-Kontexte zu verändern.
.gallery-slider-item > .mgd-ai-labeled-media--fill {
    position: absolute;
    inset: 0;
}
```

- [ ] **Step 2: Storefronttexte vereinheitlichen**

Setze in allen vier Storefront-Snippetdateien:

```json
"deepfake": "AI DEEPFAKE"
```

Ändere keine Administration-Snippets und keinen Screenreader-Zusatztext.

- [ ] **Step 3: GREEN nachweislich ausführen**

Run:

```bash
vendor/bin/phpunit tests/Storefront/LabelTemplateTest.php
npm run test:storefront
```

Expected: beide Befehle Exit `0`; der Headless-Test bestätigt für `gallery-cover` Medium und Rahmen jeweils 300 × 180 Pixel.

- [ ] **Step 4: Produktionscommit erstellen**

```bash
git add src/Resources/app/storefront/src/scss/component/_ai-image-label.scss src/Resources/snippet/de-DE/storefront.de-DE.json src/Resources/snippet/de-DE/storefront.de.json src/Resources/snippet/en-GB/storefront.en-GB.json src/Resources/snippet/en-GB/storefront.en.json
git diff --cached --check
git commit -m "fix: erhalte gekennzeichnete Produktbilder"
```

### Task 3: Release 0.1.2 dokumentieren und bauen

**Files:**
- Modify: `composer.json`
- Modify: `README.md`
- Modify: `docs/wiki/Bilder-kennzeichnen.md`
- Modify: `CHANGELOG.md`
- Modify: `Dokumentation/TableGuard-Live-Test-2026-08.md`

- [ ] **Step 1: Version und öffentliche Bezeichnung aktualisieren**

Setze `extra.mgd-release-version` in `composer.json` auf `0.1.2`. Ersetze in der README-Tabelle und im Wiki ausschließlich die sichtbaren Deepfake-Beispiele durch `AI DEEPFAKE` für Deutsch und Englisch.

- [ ] **Step 2: Changelog ergänzen**

Ergänze oben einen Abschnitt `0.1.2` mit Datum 14. August 2026 und genau diesen Punkten:

```markdown
## 0.1.2 – 14. August 2026

### Behoben

- Gekennzeichnete Hauptbilder behalten in Shopwares Produktgalerie ihre vollständige Größe sowie Zoom- und Sliderfunktion.

### Geändert

- Deutsches und englisches Frontend verwenden für den Deepfake-Status einheitlich `AI DEEPFAKE`.
```

- [ ] **Step 3: TableGuard-Betriebsdokumentation ergänzen**

Dokumentiere Ursache, begrenzte CSS-Regel, Testmatrix und Rückfallweg in `Dokumentation/TableGuard-Live-Test-2026-08.md`. Keine Zugangsdaten, Tokens oder Serverpfade aufnehmen.

- [ ] **Step 4: Vollständige lokale Qualitätskette ausführen**

Run:

```bash
composer validate --strict
composer audit
composer test:unit
composer analyse:phpstan
composer check:style
npm run test:administration
npm run test:storefront
bash scripts/build-release.sh
shopware-cli --no-interaction extension validate dist/MGDAIImageLabels-0.1.2.zip
```

Expected: alle Befehle Exit `0`; Shopware CLI meldet keine Fehler; bekannte reine Hinweise werden dokumentiert.

- [ ] **Step 5: Release- und Dokumentationscommit erstellen**

```bash
git add composer.json README.md docs/wiki/Bilder-kennzeichnen.md CHANGELOG.md Dokumentation/TableGuard-Live-Test-2026-08.md
git diff --cached --check
git commit -m "release: bereite Version 0.1.2 vor"
```

### Task 4: TableGuard sicher live aktualisieren

**Files:**
- Local-only: `PLAYTEST/Playtest-live_14-08-2026-2/`
- Local-only: `BACKUPS/2026-08-14_pre_product_gallery_fix/`

- [ ] **Step 1: Lokale Schutzgrenzen prüfen**

Bestätige mit `git check-ignore`, dass `PLAYTEST/`, `BACKUPS/`, ZIP-Backups und Zugangsdaten ignoriert bleiben. Erstelle den Playtest-Ordner mit Aufgabe, Protokoll, Auswertung, Bugfix, Todo, Verbesserungsvorschlägen und `Artefakte/`.

- [ ] **Step 2: Live-Backup erstellen und prüfen**

Sichere vor dem Upload die vorhandene Storefront-SCSS-Datei und alle vier Storefront-Snippetdateien. Erzeuge lokale SHA-256-Prüfsummen. Das Backup wird weder committed noch gepusht.

- [ ] **Step 3: Nur die fünf geprüften Laufzeitdateien übertragen**

Übertrage ausschließlich:

```text
src/Resources/app/storefront/src/scss/component/_ai-image-label.scss
src/Resources/snippet/de-DE/storefront.de-DE.json
src/Resources/snippet/de-DE/storefront.de.json
src/Resources/snippet/en-GB/storefront.en-GB.json
src/Resources/snippet/en-GB/storefront.en.json
```

Vergleiche lokale und entfernte SHA-256-Werte, ohne Zugangsdaten auszugeben.

- [ ] **Step 4: Shopware kontrolliert neu bauen**

Führe ausschließlich Plugin-Aktualisierung, Cache-Leerung, Theme-Kompilierung und Cache-Aufwärmung aus. Ein temporärer, zufällig geschützter Wartungsweg muss danach gelöscht und per HTTP 404 kontrolliert werden.

- [ ] **Step 5: Produktgalerie als Gast prüfen**

Prüfe `https://tableguard.de/TableGuard-Base/TG-SF-0001` auf Desktop und Mobil:

- Hauptbild `naturalWidth > 0` und sichtbare Geometrie größer als 100 × 100 Pixel,
- Rahmen entspricht dem sichtbaren Galerieslot,
- sichtbares Label bleibt im Rahmen,
- Sliderwechsel lädt das nächste Bild,
- Zoom öffnet und zeigt ein geladenes Bild,
- Vorschaubilder besitzen keine sichtbaren Labels,
- `AI DEEPFAKE` erscheint bei einem Deepfake-Medium,
- keine pluginbedingten Konsolen- oder Netzwerkfehler.

- [ ] **Step 6: Startseitenregression prüfen**

Prüfe auf 1440, 768 und 375 Pixel weiterhin sechs geladene Startseitenbilder, sechs sichtbare Labels, sechs Accessibility-Notizen und keine Browserfehler.

- [ ] **Step 7: Rückfallbereitschaft und Playtest abschließen**

Dokumentiere Prüfergebnisse und Backup-Pfad lokal. Bei einem Fehler die fünf Dateien aus dem Backup wiederherstellen, Theme erneut kompilieren und den Rückfall im Browser bestätigen.

### Task 5: GitHub und Wissen synchronisieren

**Files:**
- Modify: `/Volumes/AI-Workspace/AI_Knowledge/02 Projekte/MGD AI Kennzeichnung Shopware 6.md`

- [ ] **Step 1: Obsidian-Wissen aktualisieren**

Ergänze Version `0.1.2`, Ursache und Lösung der Galeriehöhe sowie die einheitliche Frontendbezeichnung. Keine lokalen Zugangsdaten, Backup-Pfade oder Playtest-Artefakte eintragen.

- [ ] **Step 2: Finalen Git-Stand prüfen**

Run:

```bash
git status --short
git diff --check
git log -5 --oneline
```

Expected: keine unbeabsichtigten oder sensiblen Dateien; ausschließlich freigegebene Projektdateien committed.

- [ ] **Step 3: Nur `main` pushen**

```bash
git push origin main
```

Expected: GitHub `main` zeigt denselben finalen Commit wie lokal. Playtest, Backups und Secrets bleiben lokal.
