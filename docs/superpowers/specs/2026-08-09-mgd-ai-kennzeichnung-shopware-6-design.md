# MGD AI Kennzeichnung Shopware 6 – Designspezifikation

Stand: 9. August 2026  
Status: von Michael freigegeben  
Geplante Lizenz: GPL-2.0-or-later

## 1. Ziel

`MGD AI Kennzeichnung Shopware 6` wird ein öffentliches, allgemein nutzbares
Shopware-6-Plugin. Es überträgt den fachlichen Umfang des bestehenden
WordPress-Plugins sinngemäß auf native Shopware-Abläufe. Redakteurinnen und
Redakteure kennzeichnen einzelne Medien danach, ob und wie künstliche
Intelligenz an ihrer Erstellung oder Bearbeitung beteiligt war. Das Storefront
zeigt daraufhin ein dezentes, barrierefreies Badge direkt am Bild.

Das Plugin verändert weder Bilddateien noch Alt-Texte. Es analysiert keine
Bilder, lädt keine externen Ressourcen und überträgt keine Medien-, Besucher-
oder Nutzungsdaten an Dritte. Es ist ein technisches Transparenzwerkzeug und
keine Rechtsberatung.

## 2. Produkt- und Repository-Namen

- Bestehendes WordPress-Repository: `MGD-AI-Kennzeichnung-WordPress`
- Neues öffentliches Repository: `MGD-AI-Kennzeichnung-Shopware-6`
- Sichtbarer Pluginname: `MGD KI-Bildkennzeichnung`
- Technischer Pluginname und PHP-Namensraum: `MGDAIImageLabels`

Die Repository-Namen unterscheiden die Plattformen eindeutig. Der sichtbare
Pluginname bleibt für Redaktionen kurz und verständlich.

## 3. Zielplattformen

- Shopware 6.6.10.x
- Shopware 6.7.x
- PHP-Versionen entsprechend den jeweils offiziell unterstützten
  Systemanforderungen dieser Shopware-Linien
- Administration auf Deutsch und Englisch
- Storefront-Ausgabe auf Deutsch und Englisch

Das Plugin ist nicht speziell an TableGuard gebunden. Der TableGuard-Shop ist
die erste kontrollierte Live-Testinstallation.

## 4. Fachlicher Umfang

### 4.1 Kennzeichnung pro Medium

Jedes Bildmedium erhält drei Werte:

| Feld | Erlaubte Werte | Standard |
| --- | --- | --- |
| Status | `none`, `generated`, `partially-generated`, `modified`, `deepfake` | `none` |
| Position | `top-left`, `top-right`, `bottom-left`, `bottom-right` | globale Position |
| Glas-Variante | `auto`, `light`, `dark` | globale Glas-Variante |

`none` wird ausdrücklich gespeichert. Dadurch ist unterscheidbar, ob ein Bild
bewusst als nicht kennzeichnungspflichtig bewertet wurde. Nur Bildmedien dürfen
die Felder erhalten; Dokumente, Videos und andere Dateien werden nicht
gekennzeichnet.

### 4.2 Sichtbare Texte

| Status | Deutsch | Englisch |
| --- | --- | --- |
| `none` | keine Ausgabe | no output |
| `generated` | `KI-GENERIERT` | `AI GENERATED` |
| `partially-generated` | `TEILWEISE KI-GENERIERT` | `AI PARTIALLY GENERATED` |
| `modified` | `MIT KI BEARBEITET` | `AI MODIFIED` |
| `deepfake` | `KI-DEEPFAKE` | `AI DEEPFAKE` |

Bei `deepfake` ergänzt ein nur für assistive Technik erreichbarer Satz die
Bedeutung. Die Texte werden als lokale Shopware-Snippets gepflegt und nicht als
freie HTML-Eingaben gespeichert.

### 4.3 Sprachwahl

Die globale Einstellung `Automatisch` ist der Standard. Sie wählt Deutsch bei
einem deutschen Verkaufskanal und Englisch bei einem englischen
Verkaufskanal. Für jeden Verkaufskanal kann die Ausgabe optional fest auf
`Deutsch` oder `Englisch` gestellt werden. Ist eine Sprache nicht eindeutig
auflösbar, wird Englisch als neutraler Rückfallwert verwendet.

### 4.4 Globale Darstellung

Die Plugin-Konfiguration enthält:

| Einstellung | Standard | Erlaubter Bereich |
| --- | ---: | ---: |
| Schriftgröße | 6 px | 6–24 px |
| Außenabstand | 12 px | 0–96 px |
| Innenabstand vertikal | 5 px | 2–24 px |
| Innenabstand horizontal | 9 px | 4–40 px |
| Eckenradius | 999 px | 0–999 px |
| Glasunschärfe | 10 px | 0–24 px |
| Standardposition | unten rechts | vier feste Ecken |
| Standard-Glas-Variante | automatisch | automatisch, hell, dunkel |
| Sprache | automatisch | automatisch, Deutsch, Englisch |

Alle Zahlen werden serverseitig als Ganzzahlen geprüft. CSS-Einheiten,
Dezimalwerte, Vorzeichen und zusätzlicher Text sind nicht erlaubt. CSS wird nur
aus geprüften Zahlen und fest programmierten Selektoren erzeugt.

### 4.5 Administrationsoberfläche

Die Shopware-Mediendetailansicht erhält einen klar abgegrenzten Bereich mit:

- Statusauswahl;
- Position;
- Glas-Variante;
- lokaler, nicht speichernder Live-Vorschau;
- expliziter Speicheraktion und zugänglicher Erfolgs- oder Fehlermeldung.

Die zentrale Plugin-Konfiguration liegt unter den Shopware-Erweiterungen und
verwendet native Administrationskomponenten. Alle Texte stehen als deutsche und
englische Snippets bereit. Die Oberfläche unterstützt Tastaturbedienung,
sinnvolle Beschriftungen und sichtbare Fokuszustände.

### 4.6 Storefront-Ausgabe

Eine wiederverwendbare Twig-Komponente erzeugt Wrapper und Badge serverseitig.
Sie wird für die zentralen Standardpfade integriert:

- Produktdetail-Galerie;
- Produktbilder in Listings und Produktempfehlungen;
- normale Erlebniswelten-Bildelemente;
- Kategorie-, Hersteller- und weitere Standardmedien, sofern das
  Shopware-Template ein vollständiges Medienobjekt bereitstellt;
- ein eigener Erlebniswelten-Baustein beziehungsweise eine klar begrenzte
  Erweiterung für Hintergrundbilder.

Das Badge verwendet `role="note"`, blockiert keine Links oder Bedienelemente
und bleibt auch ohne `backdrop-filter` kontrastreich sichtbar. Der Wrapper darf
vorhandene responsive Bildgrößen, Lazy Loading, `picture`-Elemente und
Seitenverhältnisse nicht verändern.

Fremde Themes oder Plugins können vollständig eigene Bildtemplates verwenden.
Für diese Fälle dokumentiert das Projekt die Twig-Komponente und die benötigten
Parameter. Eine fehleranfällige globale JavaScript-Suche nach Bild-URLs ist
ausgeschlossen.

### 4.7 AI-Philosophie

Das Plugin stellt einen Erlebniswelten-Baustein für einen redaktionell
pflegbaren Transparenztext bereit. Deutsche und englische Inhalte werden
getrennt nach Shopware-Sprache gespeichert. Erlaubt ist nur der von Shopware
bereinigte Rich-Text-Umfang; Skripte, Inline-Events und eingebettete externe
Inhalte sind ausgeschlossen.

Optional kann das Plugin eine eigene, zunächst unveröffentlichte CMS-Seite
vorbereiten. Es veröffentlicht keine Seite selbstständig und verändert keine
Footer-Navigation automatisch. Veröffentlichung, Zuweisung zum Verkaufskanal
und Footer-Verlinkung bleiben bewusste Handlungen einer berechtigten Person.

## 5. Architektur

### 5.1 Speicherung

Die drei Medienwerte werden als native Shopware-Custom-Fields am
`media`-Datensatz gespeichert. Installation und Aktualisierung legen ein klar
benanntes Custom-Field-Set idempotent an. Das Plugin führt keine eigene
Metadaten-Datenbanktabelle ein.

Globale und verkaufskanalspezifische Einstellungen werden über
`SystemConfigService` verwaltet. Freie Storefront-Texte liegen nicht in der
Systemkonfiguration, sondern in Shopware-Snippets beziehungsweise in den
sprachabhängigen CMS-Inhalten.

### 5.2 Getrennte Komponenten

- `Setup`: Installation, Aktualisierung und optionale Datenbereinigung.
- `MediaMetadata`: Konstanten, Normalisierung und Zugriff auf Medienwerte.
- `Configuration`: sichere globale und verkaufskanalspezifische Einstellungen.
- `Administration`: Medienfelder, Vorschau, Einstellungen und Übersetzungen.
- `Storefront`: Twig-Erweiterung, Templates, Snippets und lokale SCSS-Dateien.
- `Cms`: Hintergrundbild-Integration und AI-Philosophie-Baustein.
- `Tests`: Unit-, Integrations-, Template- und Build-Prüfungen.

Jede komplexere Funktion, Ansicht, Einstellung und Komponente liegt in einer
eigenen, sprechend benannten Datei. PHP-, JavaScript-, Twig- und SCSS-Code wird
ausführlich und menschenlesbar auf Deutsch kommentiert.

### 5.3 Datenfluss

1. Eine berechtigte Person öffnet ein Bild in der Medienverwaltung.
2. Die Administration liest die Custom Fields und zeigt eine lokale Vorschau.
3. Beim Speichern schreibt Shopwares DAL ausschließlich normalisierte Werte.
4. Das Storefront lädt das bereits verwendete Medienobjekt.
5. Der Resolver bestimmt Status, individuelle oder globale Darstellung und
   Verkaufskanalsprache.
6. Die Twig-Komponente gibt entweder nichts oder ein escaped Badge aus.

Es gibt keinen Bildanalyse-, Upload- oder Netzwerkpfad außerhalb der normalen
Shopware-Medienverwaltung.

## 6. Berechtigungen, Datenschutz und Sicherheit

- Für das Lesen und Ändern gelten Shopwares vorhandene Medien- und
  Systemkonfigurationsrechte.
- Es wird kein eigener öffentlicher Schreibendpunkt eingerichtet.
- Administration und Storefront laden keine externen Schriften, Skripte,
  Styles, Bilder oder Analysewerkzeuge.
- Das Plugin speichert keine personenbezogenen Daten, Zugangsdaten, Tokens,
  IP-Adressen oder Nutzungsprotokolle.
- Statuswerte, Klassen, Sprache und Zahlen werden über Positivlisten geprüft.
- Ausgaben werden kontextgerecht escaped.
- Fehlende, alte oder manipulierte Werte werden bei jedem Lesen erneut
  normalisiert.
- `none` oder ein unbekannter Status erzeugt kein Badge.
- Fehlende Medienobjekte oder nicht unterstützte Ausgabekontexte führen zu
  leerer Ausgabe statt zu einem Storefront-Fehler.
- Sicherheitsmeldungen werden in `SECURITY.md` beschrieben und sollen keine
  sensiblen Daten in öffentlichen Issues enthalten.

## 7. Installation, Aktualisierung und Deinstallation

GitHub-Releases liefern ein geprüftes Shopware-ZIP mit dem obersten Ordner
`MGDAIImageLabels`. Entwicklungsdateien, Backups, lokale Konfigurationen und
Zugangsdaten sind ausgeschlossen. Das Plugin enthält keinen eigenen
ungefragten Update-Checker. Eine spätere Veröffentlichung im Shopware Store
bleibt außerhalb dieses ersten Projekts.

Installation und Aktualisierung sind idempotent. Bei der Deinstallation gilt
Shopwares Option `Benutzerdaten behalten`:

- aktiviert: Custom Fields, Kennzeichnungen und redaktionelle Inhalte bleiben;
- deaktiviert: ausschließlich eindeutig plugin-eigene Custom Fields und
  Konfigurationen werden entfernt.

Bilddateien, Alt-Texte, bestehende CMS-Seiten und Navigationen werden niemals
gelöscht.

## 8. Fehlerverhalten

- Validierungsfehler erscheinen verständlich in der Administration und ändern
  keine zuvor gültigen Werte.
- Ein fehlgeschlagener Speichervorgang zeigt eine zugängliche Fehlermeldung.
- Ein Storefront-Resolver darf keine Ausnahme bis zur Seite durchreichen,
  sofern nur optionale Kennzeichnungsdaten fehlen.
- Fehlende Übersetzungen fallen auf Englisch zurück.
- Nicht unterstützte Fremdtemplates bleiben unverändert und werden über die
  Integrationsdokumentation angebunden.
- Build- oder Aktivierungsfehler stoppen das Deployment vor einer
  Storefront-Freigabe.

## 9. Teststrategie und Abnahmekriterien

### 9.1 Automatisierte Prüfungen

- PHP-Unit-Tests für alle Positivlisten, Zahlenbereiche, Standards und
  Sprachauflösung;
- Integrationstests für Anlage, Update und Entfernung des Custom-Field-Sets;
- Tests für Medienwerte, Resolver und Twig-Ausgabe;
- Template-Tests gegen doppelte Labels und beschädigte Bildstruktur;
- Administrations-Tests für Vorschau und Speicherdaten;
- Composer-Validierung, PHP-Syntax, statische Analyse und Codestyle;
- Administration- und Storefront-Build;
- Shopware-Plugin-Validierung;
- Installations- und Smoke-Tests auf Shopware 6.6.10.x und aktueller 6.7.x.

### 9.2 Fachliche Abnahme

- Alle fünf Statuswerte lassen sich an einem Bild speichern und erneut laden.
- `none` zeigt kein Badge.
- Die vier Ecken und drei Glas-Varianten funktionieren auf Desktop und Mobil.
- Deutsch, Englisch und automatische Verkaufskanalsprache funktionieren.
- Deepfake besitzt einen erweiterten Screenreader-Text.
- Produktdetail, Listing, Erlebniswelt und Hintergrundbild zeigen genau ein
  Badge.
- Links, Zoom, Slider, Lazy Loading und responsive Bilder bleiben bedienbar.
- Nicht gekennzeichnete Bilder und Administrationsseiten bleiben unverändert.
- Es entstehen keine externen Requests durch das Plugin.

## 10. TableGuard-Live-Test und Rückfallweg

Die Installation im TableGuard-Liveshop ist ausdrücklich freigegeben, erfolgt
aber erst nach bestandenen lokalen Prüfungen.

1. Tatsächliche Shopware- und PHP-Version dokumentieren.
2. Vollständiges, wiederherstellbares Datei- und Datenbankbackup erstellen.
3. Wartungszustand und vorhandene Shopfunktion prüfen.
4. Release-ZIP hochladen und Plugin zunächst installieren.
5. Administration und Storefront bauen, Plugin aktivieren und Cache leeren.
6. Ein geeignetes Testmedium kennzeichnen.
7. Medienverwaltung, Startseite, Listing, Produktdetail, Erlebniswelt,
   Warenkorb und Checkout als Smoke-Test prüfen.
8. Netzwerk-, Browser- und Serverfehler kontrollieren, ohne sensible Daten in
   Logs oder Dokumentation zu kopieren.
9. Bei Fehlern Plugin deaktivieren, Cache und Theme neu bauen und
   erforderlichenfalls das vorherige Backup wiederherstellen.

Produktivdaten werden nur im notwendigen Umfang verändert. Testkennzeichnungen
werden dokumentiert und nach der Prüfung entweder bewusst beibehalten oder
entfernt.

## 11. Dokumentation und Veröffentlichung

Das Repository erhält mindestens:

- deutsche `README.md` mit Installation, Bedienung und Grenzen;
- englische Kurzanleitung;
- `SECURITY.md`, `CONTRIBUTING.md`, Lizenz und Changelog;
- Architektur-, Datenschutz-, Test-, Deployment- und Rückfalldokumentation;
- Integrationsanleitung für eigene Twig-Templates;
- reproduzierbares Release-Skript und GitHub-Release-ZIP.

Das bestehende WordPress-Repository wird in
`MGD-AI-Kennzeichnung-WordPress` umbenannt. GitHub-Weiterleitungen werden nach
der Umbenennung geprüft. Das neue Shopware-Repository wird öffentlich unter
`MichaelGahnDESIGN/MGD-AI-Kennzeichnung-Shopware-6` veröffentlicht.

## 12. Bewusste Grenzen der ersten Version

- Keine automatische rechtliche Bewertung von Bildern.
- Keine KI-Bilderkennung oder Metadatenanalyse.
- Keine Veränderung oder Einbettung des Labels in die Bilddatei.
- Keine automatische Änderung bestehender Footer oder veröffentlichter Seiten.
- Kein Shopware-Store-Listing im ersten Release.
- Keine Garantie für Drittanbieter-Themes ohne dokumentierte
  Template-Integration.
