# Themes und individuelle Templates

Das Plugin ist allgemein für Shopware 6 entwickelt. Seine automatische Storefront-Integration besitzt dennoch eine klare technische Grenze: Das Bild muss als Shopware-Medium über Shopwares Thumbnail-System ausgegeben werden.

## Unterstützter Standardweg

Das Plugin erweitert den äußeren Block `thumbnail_utility` aus:

```text
@Storefront/storefront/utilities/thumbnail.html.twig
```

Die fertige Shopware-Ausgabe bleibt die Quelle für:

- `src`,
- `srcset`,
- `sizes`,
- Alt-Text und Titel,
- Lazy Loading,
- Zoom- und `data-*`-Attribute,
- responsive Bildgrößen.

Das Plugin löst das Label aus dem übergebenen Medienobjekt auf und kapselt Shopwares fertiges Bild nur bei einem sichtbaren Status.

## Kontextabhängige Darstellung

Shopware verwendet das Thumbnail-Template für sehr unterschiedliche Ausgaben. Ein sicherer Resolver unterscheidet deshalb unter anderem:

- Produktlisting,
- Produktgalerie und Zoom,
- Warenkorbpositionen,
- CMS-Bilder mit Cover-, Contain- oder Standardmodus,
- Zahlungs- und Versandlogos,
- Hersteller- und Footerlogos,
- Suchvorschläge,
- Konfiguratorbilder,
- Navigationsteaser.

Unbekannte oder manipulierte Aufrufer fallen auf eine konservative intrinsische Darstellung zurück. Kleine Galerie-Navigationsthumbnails werden nicht doppelt gekennzeichnet.

## Eigene Themes

Ein eigenes Theme ist in der Regel kompatibel, wenn es:

1. Shopwares `sw_thumbnails` verwendet,
2. den äußeren `thumbnail_utility`-Block nicht vollständig ohne `parent()` ersetzt,
3. das Medienobjekt im normalen Template-Kontext belässt,
4. Bildattribute weiterhin durch Shopware erzeugen lässt,
5. Plugin-Klassen nicht global mit widersprüchlichen Layoutregeln überschreibt.

Nach jeder Theme-Änderung sind Listing, Produktgalerie, Warenkorb, CMS-Bilder, Suche und Logos zu prüfen.

## Rohe HTML-Bilder

Ein direkt eingetragenes Element wie:

```html
<img src="/media/beispiel/bild.jpg" alt="Beispiel">
```

enthält nur eine URL. Es enthält keine Shopware-Medien-ID und kein geladenes Medienobjekt. Das Plugin kann deshalb weder Custom Fields lesen noch den fachlichen Status zuverlässig zuordnen.

Das gilt besonders für:

- HTML-Blöcke in Erlebniswelten,
- fest in Theme-Templates eingetragene URLs,
- externe CDN-Bilder ohne Shopware-Medienbezug,
- Inhalte aus Drittanbieter-Plugins, die kein Shopware-Thumbnail rendern.

Verwenden Sie für solche Inhalte ein echtes Shopware-Bild- oder das plugin-eigene Hintergrundbild-Element. Eine URL-basierte Rückwärtssuche wäre mehrdeutig, teuer und datenschutztechnisch schlechter und ist daher nicht Bestandteil des Plugins.

## Vollständig ersetzte Thumbnail-Templates

Wenn ein Theme den zentralen Block vollständig ersetzt und den Elterninhalt nicht übernimmt, kann kein anderes Plugin diesen ausgelassenen Code nachträglich ausführen. In diesem Fall muss das Theme den Plugin-Vertrag bewusst integrieren oder wieder auf den Shopware-Standardweg zurückkehren.

## CSS-Anpassung

Die relevanten Klassen beginnen mit:

```text
.mgd-ai-labeled-media
.mgd-ai-labeled-media__overlay
.mgd-ai-image-label
```

Anpassungen sollten ausschließlich Darstellung verändern. Folgende Eigenschaften sind funktional wichtig:

- Wrapper bleibt Bezugspunkt für die absolute Position.
- Overlay blockiert keine Interaktion.
- kleine Medien behalten den zugänglichen Text.
- Zahlungs- und Versandlogos behalten Shopwares physisches `float: right`, auch in RTL.
- responsive Bildgeometrie wird nicht durch feste Breiten oder Höhen ersetzt.

## Kompatibilitätsprüfung

- Desktop, Tablet und Mobilgerät
- LTR und RTL, falls der Shop RTL-Sprachen verwendet
- Listing und Suchvorschläge
- Produktgalerie, Zoom und Thumbnail-Navigation
- Warenkorb und Checkoutlogos
- Erlebniswelten mit Standard-, Cover- und Contain-Modus
- Hell-/Dunkelmodus und 200 Prozent Zoom

