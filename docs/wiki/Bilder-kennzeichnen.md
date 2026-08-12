# Bilder kennzeichnen

Die Kennzeichnung ist eine redaktionelle Entscheidung. Das Plugin erkennt oder bewertet keine Bildinhalte automatisch.

## Arbeitsablauf

1. In Shopware **Inhalte → Medien** öffnen.
2. Ein Bild auswählen.
3. Den Custom-Field-Bereich **KI-Bildkennzeichnung** öffnen.
4. Einen KI-Status wählen.
5. Position und Theme festlegen.
6. Die lokale Vorschau kontrollieren.
7. Mit Shopwares vorhandener Speicheraktion speichern.
8. Das Bild in allen wichtigen Storefront-Kontexten kontrollieren.

## Die drei Medienfelder

| Feld | Bedeutung |
| --- | --- |
| KI-Status | fachliche Aussage über Erstellung oder Bearbeitung |
| Position | eine der vier Bildecken |
| Theme | automatisch, hell oder dunkel |

Die Felder werden als Shopware-Custom-Fields am Medium gespeichert. Die Bilddatei wird nicht überschrieben und das Label wird nicht eingebrannt.

## Statuswerte

| Status | Bedeutung | Sichtbare Ausgabe |
| --- | --- | --- |
| Keine KI-Kennzeichnung | keine sichtbare Kennzeichnung erforderlich | kein Label |
| Vollständig KI-generiert | Bild vollständig durch KI erzeugt | `KI-GENERIERT` oder `AI GENERATED` |
| Teilweise KI-generiert | Bild enthält KI-generierte Bestandteile | `TEILWEISE KI-GENERIERT` oder `AI PARTIALLY GENERATED` |
| Mit KI verändert | vorhandenes Bild wesentlich mit KI verändert | `MIT KI BEARBEITET` oder `AI MODIFIED` |
| Deepfake | authentisch wirkende oder vergleichbare Manipulation | `KI-DEEPFAKE` oder `AI DEEPFAKE` |

Die Tabelle ist eine technische Beschreibung, keine rechtliche Einordnung.

## Vorschau und Speichern

Die Vorschau reagiert lokal auf die aktuelle Auswahl. Sie öffnet keinen eigenen Speicherweg und sendet keine Bilder an einen Dienst. Erst Shopwares native Custom-Field-Speicheraktion übernimmt die Werte.

Nach dem Speichern sollte das Medium erneut geöffnet werden. So wird geprüft, ob die gespeicherten Werte aus Shopware zurückgelesen werden.

## Wo erscheint ein Label?

Das Plugin ergänzt Bilder, die über Shopwares zentrales Thumbnail-System ausgegeben werden, sowie sein eigenes Erlebniswelten-Hintergrundbild. Ein direkt in HTML eingetragenes `<img src="…">` enthält kein Shopware-Medienobjekt und kann daher nicht automatisch zugeordnet werden. Weitere Details stehen unter [Themes und individuelle Templates](Themes-und-individuelle-Templates).

## Redaktionelle Checkliste

- Status fachlich geprüft?
- Verkaufskanalsprache kontrolliert?
- Label auf hellem und dunklem Motiv lesbar?
- Alternativtext des Bildes sinnvoll?
- mobile, Tablet- und Desktopgröße geprüft?
- Produktgalerie, Listing und Warenkorb kontrolliert, falls betroffen?
- keine fälschliche Testkennzeichnung auf einem echten Medium zurückgelassen?

