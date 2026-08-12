# Sprache und Gestaltung

Sprache und Darstellung verwenden ausschließlich feste, geprüfte Werte. Freie CSS-Eingaben werden nicht als Konfiguration übernommen.

## Sprache

| Einstellung | Verhalten |
| --- | --- |
| Automatisch | deutsche Verkaufskanalsprache ergibt Deutsch; andere Kontexte verwenden Englisch |
| Deutsch | sichtbare Kennzeichnung immer auf Deutsch |
| Englisch | sichtbare Kennzeichnung immer auf Englisch |

`Automatisch` ist der empfohlene Standard. Ein Sprachwechsel ändert nur die Ausgabe, nicht den fachlichen Status des Mediums.

Die Sprache kann pro Verkaufskanal überschrieben werden. Damit lassen sich mehrsprachige Shops ohne doppelte Medienwerte betreiben.

## Position

- oben links
- oben rechts
- unten links
- unten rechts

Ein Medienwert überschreibt die globale Standardposition. Fehlende oder manipulierte Werte fallen auf die geprüfte globale Konfiguration zurück.

## Theme

| Theme | Verhalten |
| --- | --- |
| Automatisch | folgt dem bevorzugten Farbschema des Endgeräts mit kontrastreichem Fallback |
| Hell | helle Fläche und dunkler Text |
| Dunkel | dunkle Fläche und heller Text |

Das automatische Theme bedeutet nicht, dass das Bild analysiert wird. Es folgt ausschließlich der Browser-/Systemdarstellung.

## Globale Designwerte

| Wert | Erlaubter Bereich |
| --- | --- |
| Schriftgröße | 6 bis 24 Pixel |
| Abstand zum Bildrand | 0 bis 96 Pixel |
| vertikaler Innenabstand | 2 bis 24 Pixel |
| horizontaler Innenabstand | 4 bis 40 Pixel |
| Eckenradius | 0 bis 999 Pixel |
| Hintergrundunschärfe | 0 bis 24 Pixel |

Die Grenzen verhindern freie Style-Injektionen und unbrauchbare Extremwerte. Der Storefront-Resolver normalisiert die Konfiguration erneut serverseitig.

## Individuell oder global?

- **KI-Status:** immer pro Medium.
- **Position und Theme:** globaler Standard, optional pro Medium überschreibbar.
- **Größe und Abstände:** global beziehungsweise verkaufskanalbezogen über Shopware-Konfiguration.
- **Sprache:** automatisch oder explizit, pro Verkaufskanal überschreibbar.

## Theme-Anpassungen

Eigene Themes können die festen Plugin-Klassen gezielt gestalten. Dabei müssen Kontrast, Fokusverhalten, Klickflächen und kleine Medien erhalten bleiben. Das Plugin gibt keine frei eingegebenen Klassennamen oder Styles aus. Technische Hinweise stehen unter [Themes und individuelle Templates](Themes-und-individuelle-Templates).

