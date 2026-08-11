# Integration eigener Themes

## Standardfall

Das Plugin erweitert Shopwares Storefront-Template `storefront/utilities/thumbnail.html.twig`. Themes, die dieses Template mit Shopwares Vererbungsmechanismus erweitern und den Block `thumbnail_utility` respektieren, erhalten die Kennzeichnung normalerweise automatisch. Das ursprüngliche Bild bleibt vollständig Shopware überlassen; das Plugin legt nur einen positionierten Rahmen darum.

Nach Installation oder Theme-Änderungen müssen Cache und Theme neu gebaut werden:

```bash
bin/console cache:clear
bin/console theme:compile
```

Testen Sie Produktdetail, Listing, Suche, Warenkorb, Checkout, Zahlungs- und Versandlogos sowie eigene Erlebniswelten. Kleine Galerie-Navigationselemente sind bewusst nicht sichtbar gekennzeichnet.

## Typischer Konflikt

Ein Theme kann die Integration umgehen, wenn es das Thumbnail-Template vollständig ersetzt, `parent()` nicht mehr aufruft oder Bilder als eigenes HTML ohne Shopwares Thumbnail-Helfer rendert. Das ist kein Datenverlust: Die Medienfelder bleiben gespeichert, aber das Badge erscheint an dieser Stelle nicht.

Prüfen Sie zuerst mit Shopwares Template-Profiler oder einer gezielten Quellcode-Suche, welches Template das Bild erzeugt. Ändern Sie nie direkt Dateien im Plugin oder im Basis-Storefront; legen Sie die Anpassung im eigenen Theme ab, damit Updates erhalten bleiben.

## Explizite Einbindung in einem eigenen Template

Wenn ein eigenes Template bereits ein Shopware-Medienobjekt `media` besitzt, kann es das geprüfte Viewmodell über die Twig-Funktion auflösen:

```twig
{% set label = mgd_ai_image_label(media) %}

{% sw_embed '@MGDAIImageLabels/storefront/component/mgd-ai-image-label/labeled-media.html.twig' with {
    label: label,
    intrinsicLayout: false,
    floatEndLayout: false
} only %}
    {% block mediaContent %}
        {# Hier das bereits sichere, eigene Bild-Markup einsetzen. #}
    {% endblock %}
{% end_sw_embed %}
```

`intrinsicLayout` darf nur `true` sein, wenn der Rahmen exakt der natürlichen Inline-Bildgröße folgen soll. `floatEndLayout` ist ausschließlich für bewusst rechts gefloatete, intrinsische Logos gedacht. Freie Werte aus CMS-Feldern dürfen diese Booleans nicht direkt steuern.

Alternativ lässt sich bei einem bereits sicher positionierten Rahmen nur das Badge einbinden:

```twig
{% sw_include '@MGDAIImageLabels/storefront/component/mgd-ai-image-label/badge.html.twig' with {
    label: mgd_ai_image_label(media)
} only %}
```

Der umgebende Container muss dann selbst `position: relative` besitzen und das Overlay korrekt begrenzen. Bevorzugt wird der vollständige `labeled-media`-Rahmen, weil er dieses Verhalten bereits geprüft mitbringt.

## CSS-Anpassungen

Die zentralen Klassen beginnen mit:

- `.mgd-ai-labeled-media` für den Bildrahmen
- `.mgd-ai-labeled-media__overlay` für die nicht interaktive Messschicht
- `.mgd-ai-image-label` für die Kennzeichnung
- `.mgd-ai-background-image` für das Erlebniswelten-Hintergrundelement

Position, Theme und Status erhalten ausschließlich feste Modifikator-Klassen. Größe und Abstände kommen aus begrenzten CSS-Variablen mit dem Präfix `--mgd-ai-`. Überschreiben Sie bevorzugt Farben und Typografie in einer eigenen, nach dem Plugin geladenen Theme-Datei. Entfernen Sie nicht `pointer-events: none`, die visuell versteckte Fallback-Darstellung oder die semantische Textausgabe.

Automatisch folgt das Label dem Farbschema des Geräts. Eigene Farben müssen in hellem und dunklem Kontext ausreichend Kontrast erreichen. Der sichtbare Zustand wird über Container Queries erst bei ausreichender Bildgröße aktiviert; Themes dürfen den zugänglichen Grundzustand nicht mit `display: none` entfernen.

## Barrierefrei prüfen

- Vergrößerung bis mindestens 200 Prozent ohne abgeschnittene wesentliche Inhalte
- Tastaturbedienung von Links und Bildergalerien, da das Overlay keine Interaktion blockieren darf
- Screenreader-Ausgabe der Kennzeichnung und des zusätzlichen Deepfake-Hinweises
- sinnvoller Alternativtext für nicht dekorative Hintergrundbilder
- Kontrast in Theme hell, dunkel und automatisch
- Darstellung bei sehr kleinen Bildern und auf mobilen Ansichten

## Updatefestigkeit

Shopware- oder Theme-Updates können Template-Blöcke und Layoutverträge ändern. Dokumentieren Sie eigene Overrides, halten Sie sie klein und prüfen Sie sie in Staging. Version 0.1.1 wurde mit Shopwares Standard-Storefront unter 6.6.10.22 und 6.7.13.0 geprüft; jedes eigene Zieltheme muss weiterhin individuell getestet werden.
