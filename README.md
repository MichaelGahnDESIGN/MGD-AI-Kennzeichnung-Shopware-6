# MGD AI Kennzeichnung Shopware 6

MGD AI Kennzeichnung macht den Einsatz KI-erzeugter oder KI-bearbeiteter Bilder in einem Shopware-6-Storefront transparent. Redaktionelle Mitarbeitende ordnen den Status direkt am Medium zu. Das Plugin zeigt daraus eine gut lesbare, zweisprachige Kennzeichnung am Bild an.

Wichtig: Das Plugin erkennt KI-Inhalte **nicht automatisch**. Es sendet keine Bilder an einen KI-Dienst und bewertet keine Dateien. Die inhaltlich verantwortliche Person trifft und pflegt die Kennzeichnung selbst.

## Funktionen

- fünf redaktionelle Zustände: keine Kennzeichnung, vollständig KI-generiert, teilweise KI-generiert, mit KI verändert und Deepfake
- Position und helles, dunkles oder automatisches Erscheinungsbild je Medium
- zentrale, begrenzte Einstellungen für Größe, Abstand, Innenabstand, Eckenradius und Unschärfe
- deutsche oder englische Ausgabe; im Standard `Automatisch` folgt sie der Sprache des Verkaufskanals
- Vorschau in der Medienverwaltung, ohne einen eigenen Speicherweg zu eröffnen
- Kennzeichnung von Shopware-Thumbnails und ein eigenes Erlebniswelten-Element für Hintergrundbilder
- optional vorbereitbares, zunächst unverknüpftes Erlebniswelten-Layout zur eigenen KI-Philosophie
- barrierearmer Textstatus zusätzlich zur visuellen Darstellung

## Voraussetzungen

- Shopware ab 6.6.10 oder eine kompatible Version aus der 6.7-Reihe
- PHP ab 8.2
- Schreibzugriff für Shopwares Plugin-, Cache- und Theme-Build-Prozesse
- ein Backup vor Installation, Update oder Entfernung in einem produktiven Shop

## Installation

### Als ZIP im Administrationsbereich

1. Laden Sie das Release `MGDAIImageLabels-<Version>.zip` herunter. Entpacken Sie es nicht.
2. Öffnen Sie in Shopware **Erweiterungen > Meine Erweiterungen**.
3. Laden Sie das ZIP hoch, installieren und aktivieren Sie die Erweiterung.
4. Leeren Sie bei Bedarf den Shopware-Cache und kompilieren Sie das Storefront-Theme neu.
5. Prüfen Sie die Darstellung zuerst in einer Staging-Umgebung und danach in allen aktiven Verkaufskanälen.

### Per Shopware-CLI

Kopieren Sie den im ZIP enthaltenen Ordner `MGDAIImageLabels` nach `custom/plugins/`. Führen Sie anschließend im Shopware-Projekt aus:

```bash
bin/console plugin:refresh
bin/console plugin:install --activate MGDAIImageLabels
bin/console cache:clear
bin/console theme:compile
```

Die Befehle werden bewusst ohne Zugangsdaten gezeigt. Datenbankkennwörter und andere Geheimnisse gehören ausschließlich in die geschützte Serverkonfiguration.

## Bedienung

### Ein Bild kennzeichnen

1. Öffnen Sie das Bild in **Inhalte > Medien**.
2. Wählen Sie im Custom-Field-Bereich **KI-Bildkennzeichnung** den KI-Status.
3. Optional überschreiben Sie Position und Theme nur für dieses Medium.
4. Kontrollieren Sie die lokale Vorschau.
5. Speichern Sie mit Shopwares vorhandener Speicheraktion.

`Keine KI-Kennzeichnung` blendet das sichtbare Label aus. Ungültige oder unbekannte Werte werden ebenfalls sicher als nicht sichtbar behandelt. Das Plugin verändert die Bilddatei nicht.

### Sprache und Darstellung einstellen

Öffnen Sie die Plugin-Konfiguration unter **Erweiterungen > Meine Erweiterungen**. `Automatisch` ist der empfohlene Sprachstandard: Deutschsprachige Verkaufskanäle erhalten deutsche, alle anderen unterstützten Kontexte englische Texte. Alternativ lässt sich Deutsch oder Englisch fest vorgeben. Die Sprache kann pro Verkaufskanal überschrieben werden; Darstellungswerte gelten global.

Die verfügbaren Zahlenfelder besitzen feste Grenzen. Freie CSS-Werte werden weder gespeichert noch ausgegeben. Das automatische Theme berücksichtigt das Farbschema des Endgeräts.

### Erlebniswelten

Das Element **Gekennzeichnetes Hintergrundbild** verwendet ein Shopware-Bildmedium, feste Bildpositionen und eine sichere Hintergrundfarbe. Nicht dekorative Bilder benötigen einen sinnvollen Alternativtext. Das Element übernimmt die Kennzeichnung des gewählten Mediums.

Unter **Einstellungen > Erweiterungen > MGD KI-Bildkennzeichnung** kann eine zweisprachige KI-Philosophie als Erlebniswelten-Layout vorbereitet werden. Dieser Schritt veröffentlicht oder verknüpft nichts. Prüfen, bearbeiten und veröffentlichen Sie das Layout anschließend bewusst über Shopwares Erlebniswelten.

## Rechte und Sicherheit

Das normale Bearbeiten von Medienfeldern folgt Shopwares Medien- und Custom-Field-Rechten. Für das Vorbereiten der Philosophie-Seite sind zusätzlich Rechte zum Ändern der Systemkonfiguration und zum Erstellen von CMS-Seiten, Sektionen, Blöcken und Elementen erforderlich. Vergeben Sie diese Rechte nur an zuständige Rollen.

Sicherheitsmeldungen gehören in einen privaten Meldeweg und niemals mit Zugangsdaten in ein öffentliches Issue. Einzelheiten stehen in [SECURITY.md](SECURITY.md). Datenschutz und technische Schutzgrenzen erklärt [Dokumentation/Datenschutz-und-Sicherheit.md](Dokumentation/Datenschutz-und-Sicherheit.md).

## Barrierefreiheit

Die Kennzeichnung ist Text und nicht nur Farbe. Sie wird als Hinweis ausgezeichnet, bleibt für assistive Technik auch auf sehr kleinen Bildern verfügbar und blockiert weder Links noch Gesten. Beim Status Deepfake ergänzt das Plugin einen erläuternden Screenreader-Text. Dekorative Hintergrundbilder werden mit leerem Alternativtext ausgegeben; inhaltliche Bilder benötigen eine redaktionelle Beschreibung.

Die konkrete Barrierefreiheit hängt weiterhin vom verwendeten Theme, dessen Kontrasten und der redaktionellen Pflege ab. Testen Sie Tastaturbedienung, Vergrößerung, Hell-/Dunkelmodus und Screenreader im tatsächlichen Storefront.

## Update und Deinstallation

Ein Update aktualisiert die plugin-eigene Custom-Field-Definition wiederholbar. Erstellen Sie trotzdem vor jedem produktiven Update eine Datenbank- und Dateisicherung.

Bei der Deinstallation entscheidet Shopwares Option **Benutzerdaten behalten** über Medienfelder und Systemkonfiguration: Ist sie aktiv, bleiben das plugin-eigene Custom-Field-Set und die Plugin-Einstellungen bestehen. Zusätzlich legt das Plugin einen lokalen, eng begrenzten Snapshot seiner exakt neun Einstellungswerte an. Das ist nötig, weil Shopware bei einer späteren Reinstallation zunächst die Plugin-Standardwerte schreibt. Im Installationsschritt werden globale und verkaufskanalspezifische Werte aus dem Snapshot wiederhergestellt; anschließend wird der verbrauchte Snapshot gelöscht.

Ohne **Benutzerdaten behalten** entfernt das Plugin sein eindeutig zugeordnetes Set und seine Snapshot-Tabelle; Shopwares Lebenszyklus löscht zusätzlich die Plugin-Systemkonfiguration. Fremde Konfigurationen und Tabellen werden nicht berührt.

Die Bilddateien und fremde Custom Fields bleiben immer unberührt. Da Medien ihre Custom-Field-Inhalte als JSON speichern, bereinigt das Plugin die drei Schlüssel nicht einzeln in jedem Medium. Sie können nach einer Deinstallation ohne Datenerhalt als technisch ungenutzte Werte verbleiben und bei einer späteren Neuinstallation wieder zugeordnet werden. Wer auch diese Werte löschen muss, benötigt davor einen geprüften, gesicherten Bereinigungslauf. Das vorbereitete Philosophie-Layout wird nicht automatisch entfernt, damit redaktionelle Inhalte nicht überraschend verloren gehen.

Ein sicherer Rückfall ist in [Dokumentation/Deployment-und-Rueckfall.md](Dokumentation/Deployment-und-Rueckfall.md) beschrieben.

## Grenzen und Kompatibilität

- Die fachliche Richtigkeit der Kennzeichnung bleibt redaktionelle Verantwortung.
- Eigenständige Theme-Templates, die Shopwares Thumbnail-Template vollständig ersetzen, können die automatische Einbindung umgehen.
- Sehr kleine Bilder zeigen den Hinweis aus Platzgründen visuell reduziert; der zugängliche Text bleibt erhalten.
- Die reale Installations- und Darstellungsmatrix für Shopware 6.6.10 und 6.7 ist als separater Qualitätsschritt noch offen. Vor dessen dokumentiertem Abschluss ist Version 0.1.0 als Vorabversion zu behandeln und nicht ungeprüft produktiv auszurollen.

Hinweise für eigene Themes stehen in [Dokumentation/Integration-eigener-Themes.md](Dokumentation/Integration-eigener-Themes.md). Die technische Aufteilung erklärt [Dokumentation/Architektur.md](Dokumentation/Architektur.md).

## Entwicklung

Beiträge sind willkommen. Bitte lesen Sie [CONTRIBUTING.md](CONTRIBUTING.md). Das Release-Paket wird reproduzierbar erzeugt mit:

```bash
bash scripts/build-release.sh
```

Versionen und Änderungen stehen in [CHANGELOG.md](CHANGELOG.md).

## Lizenz

Dieses Projekt ist unter `GPL-2.0-or-later` veröffentlicht. Siehe [LICENSE](LICENSE).
