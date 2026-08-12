# Fehlerbehebung

Diese Seite folgt einer festen Diagnose-Reihenfolge. Zuerst wird die Bildquelle geprüft, danach Shopware und erst zuletzt CSS. Dadurch werden Symptome nicht mit Ursachen verwechselt.

## Ein Bild wird nicht mehr angezeigt

### 1. Bild-URL direkt öffnen

Im Browser-Netzwerkbereich oder aus dem HTML die tatsächliche `src`-URL ermitteln und direkt öffnen.

| Ergebnis | Bedeutung |
| --- | --- |
| HTTP 200 und Bildinhalt | Datei wird ausgeliefert; Rendering und CSS weiter prüfen |
| HTTP 404 | Datei, Medieneintrag oder fest eingetragene URL fehlt beziehungsweise ist veraltet |
| HTTP 403 | Zugriffs-, Private-Media- oder Serverregel prüfen |
| HTML statt Bild | Serverroute, Rewrite oder Fehlerseite prüfen |

Ein HTTP 404 ist kein gewünschtes Verhalten der Kennzeichnung und kein Labelzustand.

### 2. Shopware-Medieneintrag prüfen

- Ist das Medium in **Inhalte → Medien** vorhanden?
- Zeigt Shopware eine Vorschau?
- Stimmen Dateiname, Erweiterung und URL?
- Existiert die Datei physisch im konfigurierten Dateisystem?
- Wurde das Medium gelöscht und nur eine alte URL im CMS behalten?

Datei und Datenbankeintrag gehören zusammen. Eine allein zurückkopierte Datei repariert nicht automatisch den Medieneintrag.

### 3. Ausgabepfad bestimmen

Prüfen, ob die Seite Shopwares `sw_thumbnails` verwendet oder ein rohes `<img src="…">` enthält. Rohe HTML-Bilder umgehen Shopwares Medienauflösung und das Plugin. Mehr dazu unter [Themes und individuelle Templates](Themes-und-individuelle-Templates).

## Bild sichtbar, aber Label fehlt

1. Medium erneut öffnen und gespeicherten KI-Status prüfen.
2. Sicherstellen, dass nicht **Keine KI-Kennzeichnung** gewählt ist.
3. Prüfen, ob es wirklich ein Bildmedium ist.
4. Verkaufskanal und Sprachkonfiguration prüfen.
5. HTML auf `.mgd-ai-labeled-media` untersuchen.
6. Prüfen, ob ein Theme `thumbnail_utility` vollständig ersetzt.
7. Cache leeren und Theme kompilieren.
8. Browserkonsole und fehlgeschlagene Assets prüfen.

## Label vorhanden, aber falsch positioniert

- Medienwert und globalen Standard vergleichen.
- Theme-CSS auf `position`, `overflow`, `transform`, `float`, Breite und Höhe prüfen.
- Listing, Galerie, Warenkorb und CMS getrennt testen.
- RTL-Verkaufskanal gesondert prüfen.
- Kein freies CSS in Custom Fields erwarten; das Plugin akzeptiert nur vier feste Ecken.

## Sprache stimmt nicht

- Plugin-Konfiguration für den konkreten Verkaufskanal öffnen.
- Bei **Automatisch** die Domain-/Verkaufskanalsprache prüfen.
- Deutsch wird für `de`-Locales verwendet, sonst Englisch.
- Cache leeren, falls eine alte Übersetzung ausgeliefert wird.
- Der Status selbst ist sprachneutral und muss nicht neu gespeichert werden.

## Administration zeigt keine Vorschau

- Plugin aktiv?
- Administration nach Installation oder Update gebaut?
- Plugin-Assets unter `public/bundles/mgdaiimagelabels/administration/` vorhanden?
- Browsercache geleert?
- ausgewähltes Medium tatsächlich vom Typ `IMAGE`?
- Konsole auf Import-, TwigJS- oder Store-Fehler prüfen.

## Erlebniswelten-Aktion schlägt fehl

- alle fünf benötigten Rechte prüfen,
- Netzwerkantwort und HTTP-Status ansehen,
- Shopware-Systemprotokoll ohne sensible Daten prüfen,
- keine fremde Seite mit der deterministischen Plugin-ID vorhanden?
- Admin-Sitzung noch gültig?

## Cache und Build

```bash
bin/console cache:clear
bin/console theme:compile
```

Nach Plugin-Updates kann zusätzlich ein Administration-Build durch den üblichen Shopware-Deploymentprozess erforderlich sein. Release-ZIPs enthalten bereits die gebauten Plugin-Administrationsassets.

## Supportanfrage vorbereiten

Hilfreich sind Plugin-, Shopware-, PHP- und Theme-Version, betroffener Kontext, künstliche Reproduktionsschritte, HTTP-Status und eine geschwärzte Fehlermeldung. Niemals Passwörter, Tokens, Cookies, Kundendaten, Datenbankdumps oder vollständige produktive Logs veröffentlichen.

