# Entwicklerarchitektur

Das Plugin ist in kleine, fachlich benannte Komponenten getrennt. Die wichtigsten Grenzen verhindern, dass freie Daten direkt zu HTML, CSS oder Datenbankmutationen werden.

## Überblick

```text
Shopware-Medium und SystemConfig
        ↓
Normalizer und Resolver
        ↓
unveränderliche Viewmodelle
        ↓
Twig-Templates mit festen Klassen und Snippet-Schlüsseln
        ↓
lokales Storefront-CSS
```

## Domain

`src/Domain/` enthält geschlossene Enums:

- `LabelStatus`
- `LabelPosition`
- `LabelTheme`

Unbekannte Strings werden nicht als neue Darstellung akzeptiert.

## Medienmetadaten

`MediaLabelMetadataNormalizer` liest ausschließlich die drei bekannten Custom Fields und erzeugt geprüfte Metadaten. Statusfehler führen zu keiner sichtbaren Kennzeichnung. Ungültige Position oder ungültiges Theme werden nicht direkt ausgegeben.

## Konfiguration

`DisplayConfiguration` und seine Factory lesen globale sowie verkaufskanalspezifische SystemConfig-Werte. Zahlen werden als echte Ganzzahlen behandelt und begrenzt. Die Storefront erhält keine freien CSS-Ausdrücke.

## Storefront-Viewmodell

Der Label-Resolver verbindet Medienmetadaten, Konfiguration und Verkaufskanalsprache. Das Ergebnis ist ein unveränderliches `LabelView` mit:

- Sichtbarkeit,
- festem Snippet-Schlüssel,
- festem Screenreader-Schlüssel,
- geprüfter Position,
- geprüftem Theme,
- normalisierten Zahlenwerten.

## Thumbnail-Präsentation

`ThumbnailPresentationResolver` ordnet bekannte Shopware-Aufrufer einer geschlossenen Präsentation zu:

- Fill,
- intrinsisch,
- intrinsisch und physisch rechts gefloatet,
- Label nicht erlaubt.

Klassenwerte werden gemischt typisiert angenommen, aber nur sichere Stringtokens aus begrenzt tiefen Strukturen ausgewertet. Unbekannte Aufrufer fallen intrinsisch zurück.

## Twig

Die Storefront-Erweiterung kapselt das Ergebnis von Shopwares `parent()` höchstens einmal. Bildattribute werden nicht nachgebaut. Das Label-Template erhält ausschließlich geprüfte Viewmodelle und feste CSS-Klassen.

Das CMS-Hintergrundbild verwendet ein bereits serverseitig aufgelöstes Label, damit kein zweiter Resolveraufruf und kein doppeltes Badge entsteht.

## Administration

Getrennte Dateien registrieren:

- Medienvorschau,
- Quickinfo-Override,
- Hintergrundbild-Komponente, Konfiguration und Vorschau,
- Philosophie-Komponente, Konfiguration und Vorschau,
- Einstellungsseite,
- deutsche und englische Snippets.

Die Medienvorschau liest ausschließlich das lokale `item`-Objekt. Speichern bleibt Shopwares Verantwortung. Die CMS-Sprachauflösung unterstützt Shopware 6.6 über Vuex und 6.7 über den registrierten Pinia-Kontext.

## Setup und Lebenszyklus

`CustomFieldSetDefinitionFactory` erzeugt deterministische IDs und drei Auswahlfelder. Der Installer prüft Eigentum und Kollisionen, bevor er Daten verändert.

Plugin-Lifecycle-Code funktioniert auch während Installation und Deinstallation, wenn plugin-eigene Dienste noch nicht im Container geladen sind. Dafür werden ausschließlich öffentliche Shopware-Core-Dienste als begrenzter Fallback verwendet.

## Konfigurationsretention

Ein eigener, eigentumsgeprüfter Snapshot schützt die bekannten SystemConfig-Werte bei **Benutzerdaten behalten**. Speicherung, Wiederherstellung, Verifikation und Löschung laufen transaktional. Fremde Schlüssel, unbekannte Typen und zusätzliche Datensätze brechen sicher ab.

## CMS-Resolver und Philosophie

Das Hintergrundbild lädt ausschließlich echte Bildmedien über DAL. Die Philosophie-Seite verwendet feste IDs und einen unsichtbaren Eigentumsmarker. `create()` statt `upsert()` verhindert das Überschreiben fremder Daten bei Rennen.

## Abhängigkeiten

Das Plugin benötigt keine externe Laufzeitbibliothek außerhalb der von Shopware bereitgestellten Plattform. Entwicklungsabhängigkeiten dienen Tests, statischer Analyse, Stilprüfung und Releasebau.

