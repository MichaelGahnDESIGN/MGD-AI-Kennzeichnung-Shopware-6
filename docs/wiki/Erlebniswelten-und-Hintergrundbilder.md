# Erlebniswelten und Hintergrundbilder

Das Plugin enthält zwei getrennte CMS-Funktionen: ein gekennzeichnetes Hintergrundbild und eine optional vorbereitbare KI-Philosophie.

## Gekennzeichnetes Hintergrundbild

Das eigene Erlebniswelten-Element verwendet ein echtes Shopware-Bildmedium. Dadurch stehen Medienmetadaten und KI-Status serverseitig zur Verfügung.

Konfigurierbar sind:

- Bildmedium,
- sichere Mindesthöhe,
- horizontale und vertikale Bildposition,
- feste Hintergrundfarbe für den Fallback,
- dekorativ oder inhaltlich,
- redaktioneller Alternativtext.

Dokumente, PDFs und Videos werden weder im Admin noch serverseitig als Bild akzeptiert.

## Dekorative und inhaltliche Bilder

### Dekorativ

Ein rein dekoratives Bild erhält einen leeren Alternativtext. Sein Inhalt darf für das Verständnis der Seite nicht erforderlich sein.

### Inhaltlich

Ein inhaltliches Bild benötigt eine kurze Beschreibung seines Inhalts oder Zwecks. Das Plugin verwendet in dieser Reihenfolge:

1. redaktionellen Alternativtext des CMS-Elements,
2. gepflegten übersetzten Alt-Text des Mediums,
3. gepflegten Titel des Mediums.

Dateiname oder ein generischer Text werden nicht als Ersatz erfunden. Fehlt eine sinnvolle Beschreibung, zeigt die Administration eine Warnung.

## KI-Philosophie vorbereiten

Unter **Einstellungen → Erweiterungen → MGD KI-Bildkennzeichnung** kann eine zweisprachige Erlebniswelt vorbereitet werden. Dafür sind zusätzliche CMS-Rechte nötig.

Die Aktion:

- verwendet eine deterministische, plugin-eigene Seiten-ID,
- überschreibt keine fremde Seite,
- legt höchstens eine eigene Seite an,
- veröffentlicht nichts,
- weist die Seite keinem Verkaufskanal, keiner Kategorie und keiner Landingpage zu,
- liefert anschließend einen Link zum Shopware-Editor.

Deutsch und Englisch besitzen sichere Ausgangstexte. Redaktionsinhalte werden über Shopwares HTML-Sanitizer ausgegeben.

## Bewusster Veröffentlichungsablauf

1. Seite vorbereiten.
2. Im Erlebniswelten-Editor öffnen.
3. Inhalt rechtlich und redaktionell prüfen.
4. Übersetzungen prüfen.
5. Vorschau in allen Bildschirmgrößen kontrollieren.
6. Seite bewusst zuordnen und veröffentlichen.

Das Plugin übernimmt die letzten beiden Schritte absichtlich nicht automatisch.

