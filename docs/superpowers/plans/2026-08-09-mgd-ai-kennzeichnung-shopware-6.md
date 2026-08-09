# MGD AI Kennzeichnung Shopware 6 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ein öffentliches, zweisprachiges Shopware-6-Plugin erstellen, das KI-Bildkennzeichnungen sicher an Medien speichert, in Administration und Erlebniswelten pflegbar macht und barrierefrei im Storefront ausgibt.

**Architecture:** Drei native Custom Fields am Shopware-Medium speichern Status, Position und Glas-Variante. Kleine PHP-Dienste normalisieren Medien- und Systemkonfigurationswerte; Administration, Twig-Ausgabe und zwei CMS-Elemente verwenden ausschließlich diese geprüften Werte. Alle Assets bleiben lokal, und die Veröffentlichung erfolgt als reproduzierbares GitHub-Release-ZIP.

**Tech Stack:** PHP 8.2+, Shopware 6.6.10/6.7, Symfony DI und Controller, Shopware DAL/SystemConfig, Twig, Vue-Administration/Meteor-Komponenten, JavaScript ES-Module, SCSS, PHPUnit, Node-Test-Runner, GitHub Actions, Shopware CLI.

---

## Geplante Dateistruktur

```text
MGD-AI-Kennzeichnung-Shopware-6/
├── .github/workflows/quality.yml
├── .gitignore
├── CHANGELOG.md
├── CONTRIBUTING.md
├── LICENSE
├── README.md
├── README.en.md
├── SECURITY.md
├── composer.json
├── phpstan.neon.dist
├── .php-cs-fixer.dist.php
├── phpunit.xml.dist
├── package.json
├── scripts/build-release.sh
├── src/
│   ├── MGDAIImageLabels.php
│   ├── Administration/Controller/PreparePhilosophyPageController.php
│   ├── Cms/BackgroundImage/BackgroundImageCmsElementResolver.php
│   ├── Cms/Philosophy/PhilosophyPageCreator.php
│   ├── Configuration/DisplayConfiguration.php
│   ├── Configuration/DisplayConfigurationNormalizer.php
│   ├── Configuration/DisplayConfigurationProvider.php
│   ├── Media/MediaLabelMetadata.php
│   ├── Media/MediaLabelMetadataNormalizer.php
│   ├── Setup/CustomFieldSetDefinitionFactory.php
│   ├── Setup/CustomFieldSetInstaller.php
│   ├── Storefront/Label/LabelLanguageResolver.php
│   ├── Storefront/Label/LabelView.php
│   ├── Storefront/Label/LabelViewResolver.php
│   ├── Storefront/Twig/LabelTwigExtension.php
│   └── Resources/
│       ├── app/administration/src/
│       │   ├── main.js
│       │   ├── component/mgd-ai-media-preview/{index.js,mgd-ai-media-preview.html.twig,mgd-ai-media-preview.scss}
│       │   ├── extension/sw-media-quickinfo/{index.js,sw-media-quickinfo.html.twig}
│       │   ├── module/mgd-ai-settings/{index.js,page/mgd-ai-settings-index/{index.js,mgd-ai-settings-index.html.twig}}
│       │   ├── module/sw-cms/elements/mgd-ai-background-image/{index.js,component.html.twig,config.html.twig,preview.html.twig}
│       │   ├── module/sw-cms/elements/mgd-ai-philosophy/{index.js,component.html.twig,config.html.twig,preview.html.twig}
│       │   ├── service/preview-state.js
│       │   └── snippet/{de-DE.json,en-GB.json}
│       ├── app/storefront/src/{main.js,scss/base.scss,scss/component/_ai-image-label.scss}
│       ├── config/{config.xml,routes.xml,services.xml}
│       ├── snippet/{de-DE/storefront.de-DE.json,en-GB/storefront.en-GB.json}
│       └── views/storefront/
│           ├── component/mgd-ai-image-label/{badge.html.twig,labeled-media.html.twig}
│           ├── element/{cms-element-mgd-ai-background-image.html.twig,cms-element-mgd-ai-philosophy.html.twig}
│           └── utilities/thumbnail.html.twig
└── tests/
    ├── Administration/preview-state.test.mjs
    ├── Integration/Setup/CustomFieldSetInstallerTest.php
    ├── Storefront/{LabelLanguageResolverTest.php,LabelTemplateTest.php,LabelViewResolverTest.php}
    ├── Unit/Configuration/{DisplayConfigurationNormalizerTest.php,DisplayConfigurationProviderTest.php}
    ├── Unit/Media/MediaLabelMetadataNormalizerTest.php
    ├── Unit/Setup/CustomFieldSetDefinitionFactoryTest.php
    └── bootstrap.php
```

Jede Datei besitzt genau eine Hauptverantwortung. Komplexe Views, Dienste und Einstellungen werden nicht in Sammeldateien zusammengezogen.

### Task 1: Plugin-Grundgerüst und lokale Qualitätsbefehle

**Files:**
- Create: `.gitignore`
- Create: `composer.json`
- Create: `package.json`
- Create: `phpunit.xml.dist`
- Create: `tests/bootstrap.php`
- Create: `src/MGDAIImageLabels.php`
- Test: `composer.json`

- [ ] **Step 1: Fehlende Composer-Struktur nachweisen**

Run: `composer validate --strict`

Expected: FAIL mit `./composer.json not found`.

- [ ] **Step 2: Minimale Plugin-Metadaten erstellen**

`composer.json` erhält mindestens:

```json
{
  "name": "michaelgahn-design/mgd-ai-kennzeichnung-shopware-6",
  "description": "Transparente und barrierefreie KI-Bildkennzeichnungen für Shopware 6.",
  "type": "shopware-platform-plugin",
  "version": "0.1.0",
  "license": "GPL-2.0-or-later",
  "require": {
    "php": "^8.2",
    "shopware/core": "~6.6.10 || ~6.7.0",
    "shopware/storefront": "~6.6.10 || ~6.7.0"
  },
  "require-dev": {
    "friendsofphp/php-cs-fixer": "^3.68",
    "phpstan/phpstan": "^1.12 || ^2.0",
    "phpunit/phpunit": "^10.5 || ^11.0"
  },
  "autoload": {"psr-4": {"MGDAIImageLabels\\": "src/"}},
  "autoload-dev": {"psr-4": {"MGDAIImageLabels\\Tests\\": "tests/"}},
  "extra": {
    "shopware-plugin-class": "MGDAIImageLabels\\MGDAIImageLabels",
    "label": {"de-DE": "MGD KI-Bildkennzeichnung", "en-GB": "MGD AI Image Labels"}
  },
  "scripts": {
    "test:unit": "phpunit --testsuite unit",
    "check:php": ["composer validate --strict", "php -l src/MGDAIImageLabels.php", "@test:unit"]
  }
}
```

`package.json` verwendet zunächst nur `"private": true` und `"type": "module"`.
Das Administrations-Testskript folgt zusammen mit seinem ersten echten Test in
Task 5. `phpunit.xml.dist` lädt `tests/bootstrap.php` und definiert die Suite
`unit` für `tests/Unit` und `tests/Storefront`.

- [ ] **Step 3: Plugin-Basisklasse erstellen**

```php
<?php declare(strict_types=1);

namespace MGDAIImageLabels;

use Shopware\Core\Framework\Plugin;

/**
 * Einstiegspunkt der Shopware-Erweiterung.
 *
 * Fachlogik bleibt in kleinen Diensten; diese Klasse koordiniert später nur
 * den sicheren Installations- und Deinstallationsablauf.
 */
final class MGDAIImageLabels extends Plugin
{
}
```

- [ ] **Step 4: Grundstruktur prüfen**

Run: `composer validate --strict && composer install --prefer-dist --no-interaction && php -l src/MGDAIImageLabels.php && npm run test:administration --if-present`

Expected: Composer und PHP melden Erfolg; Node meldet null Tests ohne Fehler.

- [ ] **Step 5: Grundgerüst committen**

```bash
git add .gitignore composer.json package.json phpunit.xml.dist tests/bootstrap.php src/MGDAIImageLabels.php
git commit -m "chore: initialisiere Shopware-Plugin"
```

### Task 2: Streng typisierte Medienwerte

**Files:**
- Create: `src/Media/MediaLabelMetadata.php`
- Create: `src/Media/MediaLabelMetadataNormalizer.php`
- Test: `tests/Unit/Media/MediaLabelMetadataNormalizerTest.php`

- [ ] **Step 1: Fehlende Normalisierung durch Tests festhalten**

```php
<?php declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Media;

use MGDAIImageLabels\Media\MediaLabelMetadataNormalizer;
use PHPUnit\Framework\TestCase;

final class MediaLabelMetadataNormalizerTest extends TestCase
{
    public function testValidValuesRemainUnchanged(): void
    {
        $result = (new MediaLabelMetadataNormalizer())->normalize([
            'mgd_ai_status' => 'deepfake',
            'mgd_ai_position' => 'top-left',
            'mgd_ai_theme' => 'light',
        ]);

        self::assertSame('deepfake', $result->status);
        self::assertSame('top-left', $result->position);
        self::assertSame('light', $result->theme);
    }

    public function testManipulatedValuesFallBackSafely(): void
    {
        $result = (new MediaLabelMetadataNormalizer())->normalize([
            'mgd_ai_status' => '<script>',
            'mgd_ai_position' => 'outside',
            'mgd_ai_theme' => 'url(https://example.invalid)',
        ]);

        self::assertSame('none', $result->status);
        self::assertNull($result->position);
        self::assertNull($result->theme);
        self::assertFalse($result->isVisible());
    }
}
```

- [ ] **Step 2: Test ausführen und korrektes Scheitern prüfen**

Run: `vendor/bin/phpunit tests/Unit/Media/MediaLabelMetadataNormalizerTest.php`

Expected: FAIL, weil die beiden Klassen noch fehlen.

- [ ] **Step 3: Unveränderliches Ergebnisobjekt und Positivlisten implementieren**

`MediaLabelMetadata` ist ein `final readonly class` mit `status`, `?position`, `?theme` und `isVisible(): bool`. `MediaLabelMetadataNormalizer` definiert exakt die fünf Statuswerte, vier Positionen und drei Designs aus der Spezifikation. Position und Design bleiben bei fehlenden oder ungültigen Medienwerten `null`, damit später globale Standards greifen; ein ungültiger Status wird `none`.

```php
public function normalize(array $customFields): MediaLabelMetadata
{
    $status = $this->choice($customFields['mgd_ai_status'] ?? null, self::STATUSES, 'none');
    $position = $this->optionalChoice($customFields['mgd_ai_position'] ?? null, self::POSITIONS);
    $theme = $this->optionalChoice($customFields['mgd_ai_theme'] ?? null, self::THEMES);

    return new MediaLabelMetadata($status, $position, $theme);
}
```

- [ ] **Step 4: Tests grün ausführen**

Run: `vendor/bin/phpunit tests/Unit/Media/MediaLabelMetadataNormalizerTest.php`

Expected: 2 Tests, 8 Assertions, PASS.

- [ ] **Step 5: Medienmodell committen**

```bash
git add src/Media tests/Unit/Media
git commit -m "feat: validiere KI-Kennzeichnungen"
```

### Task 3: Sichere globale und verkaufskanalspezifische Konfiguration

**Files:**
- Create: `src/Configuration/DisplayConfiguration.php`
- Create: `src/Configuration/DisplayConfigurationNormalizer.php`
- Create: `src/Configuration/DisplayConfigurationProvider.php`
- Create: `src/Resources/config/config.xml`
- Test: `tests/Unit/Configuration/DisplayConfigurationNormalizerTest.php`
- Test: `tests/Unit/Configuration/DisplayConfigurationProviderTest.php`
- Test: `tests/Unit/Configuration/DisplayConfigurationConfigXmlTest.php`

- [ ] **Step 1: Grenzwerte und Standards zuerst testen**

```php
public function testUnsafeNumbersAndChoicesUseDefaults(): void
{
    $configuration = (new DisplayConfigurationNormalizer())->normalize([
        'fontSize' => '6px;background:red',
        'offset' => 97,
        'paddingY' => -1,
        'paddingX' => 8,
        'radius' => 12,
        'blur' => 10,
        'position' => 'center',
        'theme' => 'dark',
        'language' => 'fr-FR',
    ]);

    self::assertSame(6, $configuration->fontSize);
    self::assertSame(12, $configuration->offset);
    self::assertSame(5, $configuration->paddingY);
    self::assertSame(8, $configuration->paddingX);
    self::assertSame(12, $configuration->radius);
    self::assertSame('bottom-right', $configuration->position);
    self::assertSame('dark', $configuration->theme);
    self::assertSame('auto', $configuration->language);
}
```

- [ ] **Step 2: Tests scheitern lassen**

Run: `vendor/bin/phpunit tests/Unit/Configuration`

Expected: FAIL wegen fehlender Konfigurationsklassen.

- [ ] **Step 3: Normalisierung und Anbieter implementieren**

`DisplayConfiguration` enthält nur `readonly`-Werte. Der Normalizer akzeptiert ausschließlich echte Ganzzahlen in den freigegebenen Bereichen. `DisplayConfigurationProvider` ruft pro Lesen genau neunmal die öffentliche, cache-integrierte API `SystemConfigService::get()` auf: Die acht Darstellungswerte werden immer global mit `null` als Verkaufskanal-ID geladen, ausschließlich `language` mit der angefragten Verkaufskanal-ID und Shopwares globalem Fallback. Der Provider besitzt keinen eigenen Cache; Memoisierung und Invalidierung verbleiben bei Shopware. Unit-Tests prüfen die neun exakten Aufrufe für zwei gültige Verkaufskanal-IDs und `null`, die feldweise Normalisierung manipulierter Werte sowie den XML-Vertrag gegen die PHP-Domäne.

- [ ] **Step 4: Native Konfigurationsfelder definieren**

`config.xml` enthält getrennte Karten für Sprache und Darstellung. Auswahlfelder verwenden nur `auto`, `de`, `en`, die vier Positionen und die drei Glas-Varianten. Zahlenfelder tragen passende `min`, `max` und Standardwerte; der PHP-Normalizer bleibt die verbindliche Sicherheitsgrenze.

- [ ] **Step 5: Konfiguration prüfen und committen**

Run: `vendor/bin/phpunit tests/Unit/Configuration && xmllint --noout src/Resources/config/config.xml && xmllint --noout --schema vendor/shopware/core/System/SystemConfig/Schema/config.xsd src/Resources/config/config.xml`

Expected: alle Tests PASS; XML ist wohlgeformt und entspricht dem Shopware-Systemkonfigurationsschema.

```bash
git add src/Configuration src/Resources/config/config.xml tests/Unit/Configuration
git commit -m "feat: fuege sichere Anzeigeeinstellungen hinzu"
```

### Task 4: Custom-Field-Set und Plugin-Lebenszyklus

**Files:**
- Create: `src/Setup/CustomFieldSetDefinitionFactory.php`
- Create: `src/Setup/CustomFieldSetInstaller.php`
- Create: `src/Resources/config/services.xml`
- Modify: `src/MGDAIImageLabels.php`
- Test: `tests/Unit/Setup/CustomFieldSetDefinitionFactoryTest.php`
- Test: `tests/Integration/Setup/CustomFieldSetInstallerTest.php`

- [ ] **Step 1: Exakte Definition testen**

Der Unit-Test fordert den Set-Namen `mgd_ai_image_labels`, die Relation `media`, drei SELECT-Felder und übersetzte Optionen. Er prüft zusätzlich, dass keine freie Texteingabe und kein Feld für andere Entitäten entsteht.

```php
$set = CustomFieldSetDefinitionFactory::createSet();
$relation = CustomFieldSetDefinitionFactory::createRelation('018f0000000000000000000000000000');
self::assertSame('mgd_ai_image_labels', $set['name']);
self::assertSame('media', $relation['entityName']);
self::assertSame('018f0000000000000000000000000000', $relation['customFieldSetId']);
self::assertSame(
    ['mgd_ai_status', 'mgd_ai_position', 'mgd_ai_theme'],
    array_column($set['customFields'], 'name')
);
```

- [ ] **Step 2: Test scheitern lassen**

Run: `vendor/bin/phpunit tests/Unit/Setup/CustomFieldSetDefinitionFactoryTest.php`

Expected: FAIL wegen fehlender Factory.

- [ ] **Step 3: Factory und idempotenten Installer implementieren**

Die Factory verwendet `CustomFieldTypes::SELECT` und liefert deutsche,
englische sowie Systemsprach-Labels. `CustomFieldSetInstaller::install()` legt
das Set per `custom_field_set.repository->upsert()` an, ermittelt dessen ID über
einen `EqualsFilter` auf den festen Namen und legt die Relation anschließend per
`custom_field_set_relation.repository->upsert()` an. `remove()` sucht
ausschließlich nach dem festen Set-Namen und löscht nur dessen IDs.
Repository-Abhängigkeiten werden in `services.xml` explizit injiziert.

- [ ] **Step 4: Lebenszyklus anbinden**

```php
public function install(InstallContext $context): void
{
    $this->installer()->install($context->getContext());
}

public function update(UpdateContext $context): void
{
    $this->installer()->install($context->getContext());
}

public function uninstall(UninstallContext $context): void
{
    parent::uninstall($context);
    if (!$context->keepUserData()) {
        $this->installer()->remove($context->getContext());
    }
}
```

Der private Zugriff holt `CustomFieldSetInstaller` aus dem Container. Alt-Texte, Medien und CMS-Seiten werden niemals gelöscht.

- [ ] **Step 5: Unit- und Shopware-Integrationstest ausführen**

Run in einer Shopware-Testinstallation: `php bin/phpunit custom/plugins/MGDAIImageLabels/tests/Integration/Setup/CustomFieldSetInstallerTest.php`

Expected: Set wird zweimal idempotent angelegt, Relation zeigt nur auf `media`, `keepUserData`-Pfad bewahrt Werte, vollständiger Remove-Pfad entfernt nur das eigene Set.

- [ ] **Step 6: Setup committen**

```bash
git add src/MGDAIImageLabels.php src/Setup src/Resources/config/services.xml tests/Unit/Setup tests/Integration/Setup
git commit -m "feat: registriere Medien-Custom-Fields"
```

### Task 5: Medienvorschau in der Shopware-Administration

**Files:**
- Modify: `package.json`
- Create: `src/Resources/app/administration/src/main.js`
- Create: `src/Resources/app/administration/src/service/preview-state.js`
- Create: `src/Resources/app/administration/src/component/mgd-ai-media-preview/index.js`
- Create: `src/Resources/app/administration/src/component/mgd-ai-media-preview/mgd-ai-media-preview.html.twig`
- Create: `src/Resources/app/administration/src/component/mgd-ai-media-preview/mgd-ai-media-preview.scss`
- Create: `src/Resources/app/administration/src/extension/sw-media-quickinfo/index.js`
- Create: `src/Resources/app/administration/src/extension/sw-media-quickinfo/sw-media-quickinfo.html.twig`
- Create: `src/Resources/app/administration/src/snippet/de-DE.json`
- Create: `src/Resources/app/administration/src/snippet/en-GB.json`
- Test: `tests/Administration/preview-state.test.mjs`

- [ ] **Step 1: Vorschauzustände als reine Funktion testen**

`package.json` erhält jetzt das Skript
`"test:administration": "node --test tests/Administration/*.test.mjs"`.

```js
import test from 'node:test';
import assert from 'node:assert/strict';
import { normalizePreviewState } from '../../src/Resources/app/administration/src/service/preview-state.js';

test('manipulierte Werte ergeben eine unsichtbare Vorschau', () => {
    assert.deepEqual(normalizePreviewState({ status: '<script>', position: 'center', theme: 'url()' }), {
        status: 'none', position: 'bottom-right', theme: 'auto', visible: false,
    });
});
```

- [ ] **Step 2: Test scheitern lassen**

Run: `npm run test:administration`

Expected: FAIL, weil `preview-state.js` fehlt.

- [ ] **Step 3: Reine Vorschau-Normalisierung implementieren**

Die ES-Modul-Datei enthält dieselben Positivlisten wie PHP und gibt nur feste CSS-Klassen zurück. Sie erzeugt weder HTML noch freie Styles.

- [ ] **Step 4: Native Medien-Seitenleiste erweitern**

`Shopware.Component.override('sw-media-quickinfo', ...)` erweitert den vorhandenen Block `sw_media_quickinfo_custom_field_sets`. Nach `{{ parent() }}` erscheint `mgd-ai-media-preview` nur, wenn `item.mediaType.name === 'IMAGE'`. Die normalen Shopware-Custom-Fields und deren native Speicheraktion bleiben unverändert. Das Vorschau-Element liest ausschließlich `item.customFields.mgd_ai_*`, zeigt das Bild lokal aus `item.url` und besitzt `aria-live="polite"` für Rückmeldungen.

- [ ] **Step 5: Snippets und Administration prüfen**

Run: `npm run test:administration && jq empty src/Resources/app/administration/src/snippet/de-DE.json src/Resources/app/administration/src/snippet/en-GB.json`

Expected: Node-Tests PASS; beide JSON-Dateien sind gültig.

Run in Shopware 6.6 und 6.7: `bin/build-administration.sh`

Expected: Build erfolgreich; Medienseitenleiste zeigt Felder und Vorschau ohne Konsolenfehler.

- [ ] **Step 6: Administration committen**

```bash
git add src/Resources/app/administration tests/Administration
git commit -m "feat: zeige Medienkennzeichnung mit Vorschau"
```

### Task 6: Sprachauflösung und Storefront-Viewmodell

**Files:**
- Create: `src/Storefront/Label/LabelLanguageResolver.php`
- Create: `src/Storefront/Label/LabelView.php`
- Create: `src/Storefront/Label/LabelViewResolver.php`
- Test: `tests/Storefront/LabelLanguageResolverTest.php`
- Test: `tests/Storefront/LabelViewResolverTest.php`

- [ ] **Step 1: Sprach- und Fallbackfälle testen**

```php
#[DataProvider('languageCases')]
public function testLanguageResolution(string $setting, string $locale, string $expected): void
{
    self::assertSame($expected, (new LabelLanguageResolver())->resolve($setting, $locale));
}

public static function languageCases(): iterable
{
    yield ['auto', 'de-DE', 'de'];
    yield ['auto', 'de-CH', 'de'];
    yield ['auto', 'en-GB', 'en'];
    yield ['auto', 'fr-FR', 'en'];
    yield ['de', 'en-GB', 'de'];
    yield ['en', 'de-DE', 'en'];
}
```

Der Viewresolver-Test verlangt für `deepfake` Textschlüssel,
Screenreader-Textschlüssel, die explizite Ausgabe-Locale `de-DE` oder `en-GB`,
Position, Theme und ausschließlich numerische CSS-Variablen. `none` muss
`LabelView::hidden()` liefern.

- [ ] **Step 2: Tests scheitern lassen**

Run: `vendor/bin/phpunit tests/Storefront/LabelLanguageResolverTest.php tests/Storefront/LabelViewResolverTest.php`

Expected: FAIL wegen fehlender Klassen.

- [ ] **Step 3: Resolver implementieren**

`LabelViewResolver` kombiniert `MediaLabelMetadataNormalizer`,
`DisplayConfigurationProvider` und `LabelLanguageResolver`. Individuelle
Position und Theme haben Vorrang; fehlende Werte nutzen globale Standards. Der
DTO enthält keine rohen Eingaben, sondern feste Snippet-Schlüssel, die
explizite Ausgabe-Locale, feste Klassensuffixe und validierte Integerwerte.

- [ ] **Step 4: Tests grün ausführen und committen**

Run: `vendor/bin/phpunit tests/Storefront`

Expected: alle Sprach-, Fallback- und Deepfake-Fälle PASS.

```bash
git add src/Storefront/Label tests/Storefront/LabelLanguageResolverTest.php tests/Storefront/LabelViewResolverTest.php
git commit -m "feat: erzeuge sichere Storefront-Labeldaten"
```

### Task 7: Twig-Komponente und barrierefreie Gestaltung

**Files:**
- Create: `src/Storefront/Twig/LabelTwigExtension.php`
- Modify: `src/Resources/config/services.xml`
- Create: `src/Resources/views/storefront/component/mgd-ai-image-label/badge.html.twig`
- Create: `src/Resources/views/storefront/component/mgd-ai-image-label/labeled-media.html.twig`
- Create: `src/Resources/snippet/de-DE/storefront.de-DE.json`
- Create: `src/Resources/snippet/en-GB/storefront.en-GB.json`
- Create: `src/Resources/app/storefront/src/main.js`
- Create: `src/Resources/app/storefront/src/scss/base.scss`
- Create: `src/Resources/app/storefront/src/scss/component/_ai-image-label.scss`
- Test: `tests/Storefront/LabelTemplateTest.php`

- [ ] **Step 1: Semantik und lokale Assets testen**

Der Test liest Template und SCSS und fordert `role="note"`, einen nur bei Deepfake ausgegebenen Screenreader-Text, `pointer-events: none`, einen kontrastreichen Fallback sowie `prefers-reduced-motion`. Er sucht zugleich nach `http://`, `https://`, `@import url` und externen Fonts und erwartet keine Treffer.

- [ ] **Step 2: Test scheitern lassen**

Run: `vendor/bin/phpunit tests/Storefront/LabelTemplateTest.php`

Expected: FAIL, weil Templates und Styles fehlen.

- [ ] **Step 3: Twig-Erweiterung registrieren**

```php
public function getFunctions(): array
{
    return [new TwigFunction('mgd_ai_image_label', [$this, 'resolve'])];
}

public function resolve(?MediaEntity $media): LabelView
{
    return $this->resolver->resolve($media, $this->requestStack->getCurrentRequest());
}
```

Der Service erhält das Tag `twig.extension`. Der Resolver liest Verkaufskanal-ID und Locale nur aus geprüften Request-Attributen und fällt ohne Storefront-Request sicher auf globale Einstellungen und Englisch zurück.

- [ ] **Step 4: Badge und Styles implementieren**

`badge.html.twig` rendert nur bei `label.visible`. Alle Klassen stammen aus
Positivlisten; Zahlen werden als Integer in lokal begrenzte CSS-Variablen
geschrieben. Das Badge verwendet Shopware-Snippets über
`|trans({}, null, label.locale)|sw_sanitize`. Dadurch wirkt eine erzwungene
deutsche oder englische Ausgabe auch dann, wenn der Verkaufskanal die jeweils
andere Sprache verwendet. `labeled-media.html.twig` kapselt genau einen vom
Aufrufer übergebenen `mediaContent`-Block und das Badge.

- [ ] **Step 5: Tests und Storefront-Build ausführen**

Run: `vendor/bin/phpunit tests/Storefront/LabelTemplateTest.php`

Expected: PASS.

Run in Shopware: `bin/build-storefront.sh`

Expected: Build erfolgreich, keine Sass- oder Twig-Fehler.

- [ ] **Step 6: Storefront-Grundlage committen**

```bash
git add src/Storefront/Twig src/Resources/config/services.xml src/Resources/views/storefront/component src/Resources/snippet src/Resources/app/storefront tests/Storefront/LabelTemplateTest.php
git commit -m "feat: rendere barrierefreie KI-Bildlabels"
```

### Task 8: Standardbilder, Listings und Produktgalerien integrieren

**Files:**
- Create: `src/Resources/views/storefront/utilities/thumbnail.html.twig`
- Modify: `src/Resources/app/storefront/src/scss/component/_ai-image-label.scss`
- Test: `tests/Storefront/ThumbnailIntegrationTemplateTest.php`

- [ ] **Step 1: Template-Vertrag testen**

Der Test fordert `{% sw_extends '@Storefront/storefront/utilities/thumbnail.html.twig' %}`, genau einen Aufruf von `mgd_ai_image_label(media)`, `{{ parent() }}` und ein Badge nur bei sichtbarem Viewmodell. Er prüft außerdem eine Ausschlussklasse für kleine Galerie-Navigationsthumbnails, damit dort kein Label erscheint.

- [ ] **Step 2: Test scheitern lassen**

Run: `vendor/bin/phpunit tests/Storefront/ThumbnailIntegrationTemplateTest.php`

Expected: FAIL wegen fehlendem Override.

- [ ] **Step 3: Zentralen Thumbnail-Pfad erweitern**

Der Block `thumbnail_utility` ruft zunächst den Resolver auf. Bei unsichtbarem Label wird unverändert `{{ parent() }}` ausgegeben. Bei sichtbarem Label umschließt `labeled-media.html.twig` ausschließlich den bestehenden Parent-Inhalt; für Shopware-Galerie-Navigationen setzt der Aufrufer beziehungsweise eine feste Klassenprüfung `label.visible` auf false. Der Aufrufer verwendet den sicheren `fill`-Standard. Nur wenn Shopwares ursprüngliches Medium nachweislich von intrinsischer Inline-Breite abhängt, übergibt er die boolesche Option `intrinsicLayout: true`; freie Layoutklassen sind unzulässig. Unter der dokumentierten Mindestbreite von `8rem` oder Mindesthöhe von `5rem` wechselt das Label in eine visuell verborgene 1-Pixel-Darstellung; `role="note"`, der vollständige Text und der zusätzliche Deepfake-Hinweis bleiben für assistive Technik erhalten. Der Wrapper verändert keine `src`, `srcset`, `sizes`, `alt`, `title`, Lazy-Loading-, Zoom-, Transform- oder Hover-Darstellung; nur die absolute Badge-Messschicht begrenzt Überlauf.

- [ ] **Step 4: Template- und Browser-Smoke-Test ausführen**

Run: `vendor/bin/phpunit tests/Storefront/ThumbnailIntegrationTemplateTest.php`

Expected: PASS.

Run in Testshop: `bin/console cache:clear && bin/console theme:compile`

Expected: Produktlisting, Produktdetail-Galerie, CMS-Bild und Warenkorb laden; gekennzeichnete Hauptbilder zeigen je ein Badge, kleine Galerie-Navigationen keines.

- [ ] **Step 5: Standardintegration committen**

```bash
git add src/Resources/views/storefront/utilities/thumbnail.html.twig src/Resources/app/storefront/src/scss/component/_ai-image-label.scss tests/Storefront/ThumbnailIntegrationTemplateTest.php
git commit -m "feat: integriere Labels in Shopware-Bilder"
```

### Task 9: Erlebniswelten-Hintergrundbild

**Files:**
- Create: `src/Cms/BackgroundImage/BackgroundImageCmsElementResolver.php`
- Modify: `src/Resources/config/services.xml`
- Create: `src/Resources/app/administration/src/module/sw-cms/elements/mgd-ai-background-image/index.js`
- Create: `src/Resources/app/administration/src/module/sw-cms/elements/mgd-ai-background-image/component.html.twig`
- Create: `src/Resources/app/administration/src/module/sw-cms/elements/mgd-ai-background-image/config.html.twig`
- Create: `src/Resources/app/administration/src/module/sw-cms/elements/mgd-ai-background-image/preview.html.twig`
- Create: `src/Resources/views/storefront/element/cms-element-mgd-ai-background-image.html.twig`
- Test: `tests/Integration/Cms/BackgroundImageCmsElementResolverTest.php`

- [ ] **Step 1: Resolver-Vertrag testen**

Der Integrationstest erwartet den Typ `mgd-ai-background-image`, sammelt nur die konfigurierte Media-ID, lädt das Medium über `MediaDefinition` und setzt es als `CmsElementMediaStruct`. Eine leere oder ungültige ID erzeugt keinen DAL-Zugriff und keine Ausgabe.

- [ ] **Step 2: Test scheitern lassen**

Run in Shopware: `php bin/phpunit custom/plugins/MGDAIImageLabels/tests/Integration/Cms/BackgroundImageCmsElementResolverTest.php`

Expected: FAIL wegen fehlendem Resolver.

- [ ] **Step 3: Resolver und Service implementieren**

Der Resolver erweitert `AbstractCmsElementResolver`, verwendet einen stabilen Suchschlüssel aus Element-ID und Media-ID und wird mit `shopware.cms.data_resolver` getaggt.

- [ ] **Step 4: CMS-Element mit getrennten Views registrieren**

Das Administrations-Element bietet Medium, Mindesthöhe, horizontale und vertikale Bildposition, dekorativ ja/nein sowie sicheren Fallback-Hintergrund. Es akzeptiert keine freie CSS-Eingabe. Das Storefront-Template verwendet die kodierte lokale Medien-URL, rendert das Badge serverseitig und setzt `aria-hidden="true"` nur für ausdrücklich dekorative Bilder.

- [ ] **Step 5: Build, Integrationstest und Commit**

Run: `npm run test:administration && php bin/phpunit custom/plugins/MGDAIImageLabels/tests/Integration/Cms/BackgroundImageCmsElementResolverTest.php && bin/build-administration.sh && bin/build-storefront.sh`

Expected: alle Befehle erfolgreich; Element erscheint in der Erlebniswelten-Auswahl und zeigt das Badge.

```bash
git add src/Cms/BackgroundImage src/Resources/config/services.xml src/Resources/app/administration/src/module/sw-cms/elements/mgd-ai-background-image src/Resources/views/storefront/element/cms-element-mgd-ai-background-image.html.twig tests/Integration/Cms
git commit -m "feat: fuege gekennzeichnetes CMS-Hintergrundbild hinzu"
```

### Task 10: Zweisprachige AI-Philosophie und sichere Seitenerstellung

**Files:**
- Create: `src/Cms/Philosophy/PhilosophyPageCreator.php`
- Create: `src/Administration/Controller/PreparePhilosophyPageController.php`
- Modify: `src/Resources/config/routes.xml`
- Modify: `src/Resources/config/services.xml`
- Create: `src/Resources/app/administration/src/module/sw-cms/elements/mgd-ai-philosophy/index.js`
- Create: `src/Resources/app/administration/src/module/sw-cms/elements/mgd-ai-philosophy/component.html.twig`
- Create: `src/Resources/app/administration/src/module/sw-cms/elements/mgd-ai-philosophy/config.html.twig`
- Create: `src/Resources/app/administration/src/module/sw-cms/elements/mgd-ai-philosophy/preview.html.twig`
- Create: `src/Resources/views/storefront/element/cms-element-mgd-ai-philosophy.html.twig`
- Create: `src/Resources/app/administration/src/module/mgd-ai-settings/index.js`
- Create: `src/Resources/app/administration/src/module/mgd-ai-settings/page/mgd-ai-settings-index/index.js`
- Create: `src/Resources/app/administration/src/module/mgd-ai-settings/page/mgd-ai-settings-index/mgd-ai-settings-index.html.twig`
- Test: `tests/Integration/Cms/PhilosophyPageCreatorTest.php`
- Test: `tests/Integration/Administration/PreparePhilosophyPageControllerTest.php`

- [ ] **Step 1: Sichere Idempotenz testen**

Der Test ruft `prepare()` zweimal auf und erwartet dieselbe CMS-Seiten-ID. Die Seite bleibt inaktiv beziehungsweise keiner Kategorie und keinem Verkaufskanal zugewiesen. Der Test legt vorher eine fremde Seite mit gleichem Namen an und bestätigt, dass sie unverändert bleibt. Der Controller-Test erwartet Admin-API-Scope, `POST`, erforderliches Systemkonfigurationsrecht und keine sensiblen Antwortdaten.

- [ ] **Step 2: Tests scheitern lassen**

Run in Shopware: `php bin/phpunit custom/plugins/MGDAIImageLabels/tests/Integration/Cms/PhilosophyPageCreatorTest.php custom/plugins/MGDAIImageLabels/tests/Integration/Administration/PreparePhilosophyPageControllerTest.php`

Expected: FAIL wegen fehlender Dienste.

- [ ] **Step 3: CMS-Element implementieren**

Das Element registriert ein translatables Rich-Text-Feld `content` mit einem deutschen und englischen Standardtext. Die Storefront-Ausgabe nutzt `|sw_sanitize`; Script-, Style-, Iframe-, Object- und Event-Attribute sind unzulässig.

- [ ] **Step 4: Seitenerstellung und Admin-Aktion implementieren**

`PhilosophyPageCreator` sucht ausschließlich nach einer plugin-eigenen Kennung in `customFields`, erstellt ein `landingpage`-Layout mit genau einem `mgd-ai-philosophy`-Element und weist es keiner Kategorie zu. Der Controller antwortet mit `{ "created": true|false, "cmsPageId": "<uuid>" }`; die Administrationsseite zeigt danach einen Link zur normalen Erlebniswelten-Bearbeitung. Sie veröffentlicht oder verknüpft nichts automatisch.

- [ ] **Step 5: Tests, Builds und Commit**

Run: `php bin/phpunit custom/plugins/MGDAIImageLabels/tests/Integration/Cms/PhilosophyPageCreatorTest.php custom/plugins/MGDAIImageLabels/tests/Integration/Administration/PreparePhilosophyPageControllerTest.php && bin/build-administration.sh && bin/build-storefront.sh`

Expected: alle Tests und Builds PASS; wiederholtes Klicken erzeugt keine zweite Seite.

```bash
git add src/Cms/Philosophy src/Administration src/Resources/config src/Resources/app/administration/src/module/mgd-ai-settings src/Resources/app/administration/src/module/sw-cms/elements/mgd-ai-philosophy src/Resources/views/storefront/element/cms-element-mgd-ai-philosophy.html.twig tests/Integration
git commit -m "feat: ergaenze zweisprachige AI-Philosophie"
```

### Task 11: Dokumentation, Lizenz und reproduzierbares Release

**Files:**
- Create: `README.md`
- Create: `README.en.md`
- Create: `SECURITY.md`
- Create: `CONTRIBUTING.md`
- Create: `CHANGELOG.md`
- Create: `LICENSE`
- Create: `Dokumentation/Architektur.md`
- Create: `Dokumentation/Datenschutz-und-Sicherheit.md`
- Create: `Dokumentation/Integration-eigener-Themes.md`
- Create: `Dokumentation/Deployment-und-Rueckfall.md`
- Create: `scripts/build-release.sh`
- Test: `tests/Structure/DocumentationAndReleaseTest.php`

- [ ] **Step 1: Dokumentations- und Paketvertrag testen**

Der Test fordert alle genannten Dateien, deutsche Hauptdokumentation, englische Kurzanleitung, GPL-2.0-or-later, keine Geheimnisdateien und einen Release-Ordner `MGDAIImageLabels`. Er prüft, dass `.env`, `.git`, `tests`, `Backups`, `.superpowers` und `vendor` nicht im ZIP liegen.

- [ ] **Step 2: Test scheitern lassen**

Run: `vendor/bin/phpunit tests/Structure/DocumentationAndReleaseTest.php`

Expected: FAIL, weil Dokumentation und Release-Skript fehlen.

- [ ] **Step 3: Menschenlesbare Dokumentation schreiben**

Die Dokumente erklären Zweck, Installation, Bedienung, Rechte, Datenspeicherung, Sprachwahl, Barrierefreiheit, Dritt-Theme-Integration, Grenzen, Backup und Rückfall auch für Nicht-Programmierer. Keine Zugangsdaten, privaten URLs oder produktiven IDs werden aufgenommen.

- [ ] **Step 4: Release-Skript implementieren**

Das Bash-Skript verwendet `set -euo pipefail`, liest die Version aus `composer.json`, erstellt ein temporäres Verzeichnis mit `mktemp -d`, kopiert ausschließlich eine feste Positivliste und erzeugt `dist/MGDAIImageLabels-<version>.zip`. Ein Trap entfernt nur das validierte temporäre Verzeichnis.

- [ ] **Step 5: Paket bauen, prüfen und committen**

Run: `bash scripts/build-release.sh && unzip -l dist/MGDAIImageLabels-0.1.0.zip && vendor/bin/phpunit tests/Structure/DocumentationAndReleaseTest.php`

Expected: ZIP besitzt genau einen obersten Ordner `MGDAIImageLabels`; Tests PASS; keine Entwicklungs- oder Geheimnisdateien.

```bash
git add README.md README.en.md SECURITY.md CONTRIBUTING.md CHANGELOG.md LICENSE Dokumentation scripts tests/Structure
git commit -m "docs: dokumentiere Installation und Sicherheit"
```

### Task 12: Automatische Qualitäts- und Kompatibilitätsmatrix

**Files:**
- Create: `.github/workflows/quality.yml`
- Create: `.php-cs-fixer.dist.php`
- Create: `phpstan.neon.dist`
- Modify: `composer.json`
- Test: `.github/workflows/quality.yml`

- [ ] **Step 1: Workflow-Anforderungen als Strukturtest ergänzen**

Der bestehende Strukturtest fordert Matrixeinträge `6.6.10` und `6.7`, PHP
`8.2` und `8.4`, Composer-Validierung, PHPUnit, Node-Tests, PHPStan,
PHP-CS-Fixer im Prüfmodus, Shopware-CLI-Validierung, Geheimnisscan, ZIP-Build
und Artefakt-Upload.

- [ ] **Step 2: Test scheitern lassen**

Run: `vendor/bin/phpunit tests/Structure/DocumentationAndReleaseTest.php`

Expected: FAIL wegen fehlendem Workflow.

- [ ] **Step 3: GitHub-Actions-Workflow implementieren**

Der Workflow läuft bei Push und Pull Request. Die Shopware-Matrix setzt per
`composer require --no-update` die jeweilige Core-/Storefront-Linie, installiert
mit `--prefer-dist --no-interaction`, führt PHP-, PHPStan-,
PHP-CS-Fixer- und Node-Prüfungen aus und baut das Release nur im neuesten
Matrixlauf. `phpstan.neon.dist` analysiert `src` auf Level 8; die
PHP-CS-Fixer-Konfiguration nutzt `@PER-CS2.0` und `declare_strict_types`.
Berechtigungen bleiben `contents: read`; Releases werden nicht automatisch aus
ungeprüften Pull Requests veröffentlicht.

- [ ] **Step 4: Vollständige lokale Prüfung**

Run: `composer validate --strict && vendor/bin/phpunit && vendor/bin/phpstan analyse -c phpstan.neon.dist && vendor/bin/php-cs-fixer fix --dry-run --diff && npm run test:administration && shopware-cli extension validate . && git diff --check && bash scripts/build-release.sh`

Expected: alle Befehle Exit-Code 0.

- [ ] **Step 5: CI committen**

```bash
git add .github/workflows/quality.yml .php-cs-fixer.dist.php phpstan.neon.dist composer.json tests/Structure/DocumentationAndReleaseTest.php
git commit -m "ci: pruefe Shopware-Kompatibilitaet"
```

### Task 13: Kontrollierte Shopware-6.6- und 6.7-Abnahme

**Files:**
- Create: `Dokumentation/Testprotokoll-0.1.0.md`
- Modify: `CHANGELOG.md`

- [ ] **Step 1: Zwei frische Testshops bereitstellen**

Je eine isolierte Shopware-Installation für die jüngste 6.6.10.x- und 6.7.x-Version erstellen. Keine produktiven TableGuard-Daten verwenden. Plugin als `custom/plugins/MGDAIImageLabels` einbinden.

- [ ] **Step 2: Installation und Builds je Version ausführen**

Run in jedem Testshop:

```bash
MGD_SHOPWARE_INTEGRATION_TESTS=1 MGD_SHOPWARE_TEST_DATABASE_URL='mysql://.../mgd_shopware_test' php bin/phpunit custom/plugins/MGDAIImageLabels/tests/Integration/Setup/CustomFieldSetInstallerTest.php --fail-on-skipped
bin/console plugin:refresh
bin/console plugin:install --activate MGDAIImageLabels
bin/build-administration.sh
bin/build-storefront.sh
bin/console cache:clear
```

`MGD_SHOPWARE_TEST_DATABASE_URL` muss dabei auf eine isolierte Testdatenbank
zeigen; das Beispiel enthält absichtlich keine echten Zugangsdaten. Expected:
alle Befehle Exit-Code 0; Plugin ist aktiv.

- [ ] **Step 3: Fachliche Smoke-Tests dokumentieren**

Pro Version alle fünf Statuswerte, vier Positionen, drei Designs, Auto/Deutsch/Englisch, Produktlisting, Produktdetail-Galerie, CMS-Bild, Hintergrundbild, Philosophieelement, Warenkorb, responsive Ansicht, Tastaturbedienung und Deepfake-Screenreader-Text prüfen. Browserkonsole und Shopware-Log müssen frei von pluginbedingten Fehlern sein.

- [ ] **Step 4: Deinstallation mit beiden Datenoptionen testen**

Zuerst mit Benutzerdaten behalten deinstallieren und erneute Installation prüfen. Danach in einer separaten Testinstanz ohne Datenerhalt deinstallieren und bestätigen, dass nur das eigene Custom-Field-Set und eigene Konfiguration verschwinden.

- [ ] **Step 5: Testprotokoll und Changelog committen**

```bash
git add Dokumentation/Testprotokoll-0.1.0.md CHANGELOG.md
git commit -m "test: dokumentiere Shopware-Abnahme"
```

### Task 14: Öffentliche GitHub-Repositories und Release 0.1.0

**Files:**
- Modify: remote repository names and release metadata

- [ ] **Step 1: Sauberen Veröffentlichungsstand verifizieren**

Run: `git status --short --branch && git log --oneline --decorate -12 && git diff --check && composer validate --strict && vendor/bin/phpunit && npm run test:administration && bash scripts/build-release.sh`

Expected: sauberer `main`, alle Prüfungen erfolgreich, Release-ZIP vorhanden.

- [ ] **Step 2: Bestehendes WordPress-Repository umbenennen**

```bash
gh repo rename MGD-AI-Kennzeichnung-WordPress --repo MichaelGahnDESIGN/MGD-AI-Image-Labels --yes
gh repo view MichaelGahnDESIGN/MGD-AI-Kennzeichnung-WordPress --json name,url,visibility
```

Expected: Name `MGD-AI-Kennzeichnung-WordPress`, Sichtbarkeit `PUBLIC`; alter GitHub-Link leitet weiter.

- [ ] **Step 3: Neues öffentliches Shopware-Repository erstellen und pushen**

```bash
gh repo create MichaelGahnDESIGN/MGD-AI-Kennzeichnung-Shopware-6 --public --source=. --remote=origin --push
gh repo view MichaelGahnDESIGN/MGD-AI-Kennzeichnung-Shopware-6 --json name,url,visibility,defaultBranchRef
```

Expected: öffentliches Repository mit Default-Branch `main`.

- [ ] **Step 4: Version taggen und Release veröffentlichen**

```bash
git tag -a v0.1.0 -m "MGD AI Kennzeichnung Shopware 6 v0.1.0"
git push origin v0.1.0
gh release create v0.1.0 dist/MGDAIImageLabels-0.1.0.zip --title "MGD AI Kennzeichnung Shopware 6 v0.1.0" --notes-file CHANGELOG.md
```

Expected: öffentliches Release enthält genau das geprüfte ZIP.

- [ ] **Step 5: Links und Release-Download prüfen**

Run: `gh release view v0.1.0 --repo MichaelGahnDESIGN/MGD-AI-Kennzeichnung-Shopware-6 --json url,assets,isDraft,isPrerelease`

Expected: kein Draft, kein Prerelease, ein ZIP-Asset.

### Task 15: Abgesicherte Installation im TableGuard-Liveshop

**Files:**
- Create: `Dokumentation/TableGuard-Live-Test-2026-08.md`
- Create: `/Users/michaelgahn/AKTUELLE PROJEKTE/TableGuard/Dokumentation/MGD_AI_Kennzeichnung_Live_Test_2026-08.md`
- Create: `/Volumes/AI-Workspace/AI_Knowledge/02 Projekte/MGD AI Kennzeichnung Shopware 6.md`

- [ ] **Step 1: Live-Versionen und Ausgangszustand nur lesend erfassen**

In der Shopware-Administration Systeminformationen prüfen und Shopware-/PHP-Version dokumentieren. Startseite, Listing, Produktdetail, Warenkorb und Checkout vor der Änderung auf Erreichbarkeit prüfen. Keine Tokens, E-Mail-Adressen, internen Pfade oder Kundendaten dokumentieren.

- [ ] **Step 2: Wiederherstellbares Backup erstellen und prüfen**

Mit dem vorhandenen TableGuard-Backupverfahren ein vollständiges Datei- und Datenbankbackup anlegen. Dateigröße, Abschlussstatus und vorhandene Wiederherstellungsanleitung prüfen. Ohne erfolgreiches Backup wird die Live-Installation nicht begonnen.

- [ ] **Step 3: Geprüftes Release-ZIP installieren**

In **Erweiterungen → Meine Erweiterungen → Erweiterung hochladen** ausschließlich `MGDAIImageLabels-0.1.0.zip` wählen. Plugin installieren, anschließend Administration und Storefront über das vorhandene TableGuard-Deploymentverfahren bauen, Plugin aktivieren und Cache leeren.

- [ ] **Step 4: Minimalen Live-Datensatz testen**

Ein vorhandenes, unkritisches TableGuard-Testbild kennzeichnen. Automatische Sprache, Deutsch und Englisch kurz prüfen; danach die gewünschte endgültige Einstellung wiederherstellen. Startseite, Listing, Produktdetail, Erlebniswelt, Warenkorb und Checkout prüfen. Das Plugin darf keine zusätzlichen externen Netzwerkrequests erzeugen.

- [ ] **Step 5: Fehlerpfad bei Bedarf ausführen**

Bei pluginbedingtem Fehler sofort deaktivieren, Cache und Theme neu bauen und erneut prüfen. Falls der vorherige Zustand nicht vollständig zurückkehrt, Backup gemäß dokumentierter Wiederherstellungsanleitung einspielen. Keine weiteren Live-Änderungen vornehmen, bis die Ursache lokal behoben und erneut getestet wurde.

- [ ] **Step 6: Nicht sensible Ergebnisse dokumentieren**

Testzeitpunkt, Versionen, geprüfte Seiten, Ergebnis und gegebenenfalls ausgeführten Rückfallweg dokumentieren. Die TableGuard-Versionsdokumentation und die Obsidian-Projektnotiz erhalten nur eine verständliche technische Zusammenfassung ohne Zugangsdaten.

- [ ] **Step 7: Abschlussdokumentation committen und pushen**

```bash
git add Dokumentation/TableGuard-Live-Test-2026-08.md
git commit -m "docs: dokumentiere TableGuard-Live-Test"
git push origin main
git -C "/Users/michaelgahn/AKTUELLE PROJEKTE/TableGuard" add Dokumentation/MGD_AI_Kennzeichnung_Live_Test_2026-08.md
git -C "/Users/michaelgahn/AKTUELLE PROJEKTE/TableGuard" commit -m "docs: dokumentiere Test der KI-Bildkennzeichnung"
```

Expected: Repository sauber; öffentliche Dokumentation enthält keine sensiblen Daten.

## Abschlussprüfung

- [ ] Jede Anforderung der freigegebenen Designspezifikation ist durch mindestens einen Task abgedeckt.
- [ ] Der Plan enthält keine unvollständigen oder nur allgemein formulierten Arbeitsanweisungen.
- [ ] Klassen-, Feld- und Methodennamen stimmen über Setup, Administration, Twig, Tests und Dokumentation überein.
- [ ] `git diff --check` meldet keine Formatierungsfehler.
- [ ] Das TableGuard-Repository enthält vor dem Live-Test weiterhin keine von diesem Projekt verursachten Änderungen.
