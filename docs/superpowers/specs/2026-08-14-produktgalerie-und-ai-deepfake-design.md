# Produktgalerie und einheitlicher Frontendtext „AI DEEPFAKE“

**Datum:** 14. August 2026

**Status:** Von Michael Gahn fachlich freigegeben

## Ziel

Gekennzeichnete Artikelbilder müssen in Shopwares Produktgalerie weiterhin vollständig sichtbar sein und ihre Kennzeichnung innerhalb des Bildbereichs anzeigen. Der Deepfake-Status soll im deutschen und englischen Frontend einheitlich den sichtbaren Text `AI DEEPFAKE` verwenden.

## Bestätigte Ursache

Shopwares Hauptbild der Produktgalerie ist absolut positioniert und bezieht seine Größe unmittelbar vom Galerieslot. Der bisherige relative Kennzeichnungsrahmen liegt dazwischen und erhält in diesem Kontext eine berechnete Höhe von null Pixeln. Die Bilddatei wird erfolgreich geladen, das Bild berechnet innerhalb des kollabierten Rahmens aber ebenfalls null Pixel Breite und Höhe.

## Gewählte Lösung

Der vorhandene Fill-Rahmen bleibt für alle bereits geprüften Shopware-Kontexte unverändert. Ausschließlich wenn er ein direktes Kind von Shopwares `.gallery-slider-item` ist, übernimmt er den vorhandenen Galerieslot absolut mit `inset: 0`. Das absolut positionierte Shopware-Bild erhält dadurch wieder seinen ursprünglichen Größenbezug. Das Label bleibt als nicht-interaktive Overlayschicht innerhalb desselben Rahmens.

Die deutschen und englischen Storefront-Snippets erhalten beide exakt `AI DEEPFAKE`. Administrations-Snippets, gespeicherte Statuswerte und Screenreader-Zusatztexte werden nicht verändert.

## Sicherheits- und Kompatibilitätsgrenzen

- Keine freien Datenbankwerte erzeugen CSS-Klassen oder Styles.
- Nur Shopwares feste Galerieklasse und der vorhandene Fill-Modus aktivieren die Regel.
- Vorschaubilder der Galerien bleiben weiterhin von sichtbaren Labels ausgeschlossen.
- Bild-URL, `srcset`, Alternativtext, Lazy Loading, Zoom-Attribute und Slidersteuerung bleiben bei Shopware.
- Es werden keine Bilder oder Metadaten an Dritte übertragen.
- Vor dem Live-Update werden Plugin-Dateien und Release-Paket gesichert; der Rückfall stellt die vorherigen Dateien wieder her und kompiliert das Theme erneut.

## Testvertrag

Vor der Implementierung müssen Tests für folgende fehlende Eigenschaften rot werden:

- Produktgalerie-Fill-Rahmen besitzt den festen absoluten Größenvertrag.
- Deutsche und englische regionale sowie neutrale Storefront-Snippets liefern `AI DEEPFAKE`.
- Administration bleibt außerhalb des Frontend-Textauftrags unverändert.

Nach der Implementierung müssen PHP-Unit-, Struktur-, Administrations- und Storefront-Browsertests grün sein. Der Live-Playtest prüft mindestens:

- Hauptbild sichtbar und geladen,
- Label sichtbar und vollständig innerhalb des Hauptbildrahmens,
- Sliderwechsel und Zoom weiterhin bedienbar,
- Galerievorschaubilder ohne zusätzliche sichtbare Labels,
- Startseitenkennzeichnungen unverändert,
- Desktop und Mobil,
- keine pluginbedingten Konsolen- oder Netzwerkfehler,
- sichtbarer Text `AI DEEPFAKE` im deutschen Frontend.

## Nicht Bestandteil

- keine Änderung an Custom Fields oder Medienzuordnungen,
- keine Umbenennung des internen Statuswerts `deepfake`,
- keine Änderung der Administrationsbezeichnung,
- keine allgemeine Neugestaltung der Produktgalerie.
