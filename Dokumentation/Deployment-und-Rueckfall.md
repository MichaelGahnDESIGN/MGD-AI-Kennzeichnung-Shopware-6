# Deployment und Rückfall

## Ziel

Diese Anleitung beschreibt einen vorsichtigen Weg von Staging in einen produktiven Shop. Sie ersetzt kein betriebsindividuelles Notfallhandbuch. Verantwortliche müssen vor Beginn wissen, wer freigibt, wie lange das Wartungsfenster dauert und wie Datenbank sowie Dateien zuverlässig wiederhergestellt werden.

## Vor dem Deployment

1. Erfassen Sie aktuelle Shopware-, PHP-, Plugin- und Theme-Versionen.
2. Erstellen Sie ein verschlüsseltes Datenbank- und Dateibackup außerhalb des Webroots.
3. Prüfen Sie die Wiederherstellung des Backups in einer isolierten Umgebung.
4. Verwenden Sie in Staging anonymisierte oder künstliche Daten.
5. Installieren Sie exakt das vorgesehene Release-ZIP, nicht einen beliebigen Arbeitsordner.
6. Führen Sie Plugin-, Cache- und Theme-Build aus.
7. Prüfen Sie Administration, Medienfelder, Vorschau, Storefront, Erlebniswelten und alle aktiven Verkaufskanäle.
8. Halten Sie verantwortliche Person, Zeitpunkt, ZIP-Prüfsumme und Prüfergebnis im internen Änderungsprotokoll fest.

Die reale Produktmatrix für Shopware 6.6.10 und 6.7 ist noch nicht abgeschlossen. Vor deren dokumentiertem Abschluss darf ein produktiver Einsatz nur nach eigener vollständiger Staging-Prüfung und bewusster Risikoentscheidung erfolgen.

## Installation oder Update in Staging

Legen Sie den Ordner `MGDAIImageLabels` aus dem Release unter `custom/plugins/` ab und führen Sie aus:

```bash
bin/console plugin:refresh
bin/console plugin:install --activate MGDAIImageLabels
bin/console cache:clear
bin/console theme:compile
```

Für ein Update verwenden Sie Shopwares vorgesehenen Update-Ablauf für die konkret installierte Version. Überschreiben Sie keine produktiven Dateien während laufender Requests. Je nach Hosting sind atomarer Release-Wechsel oder Wartungsmodus sinnvoll.

Ein nachvollziehbarer CLI-Ablauf für ein bereits installiertes Plugin ist:

```bash
bin/console plugin:refresh
bin/console plugin:update MGDAIImageLabels
bin/console cache:clear
bin/console theme:compile
```

Das neue Plugin-Verzeichnis muss davor vollständig und atomar aus der geprüften ZIP bereitgestellt sein. Falls die Shopware-Installation Administration und Storefront aus Quellen baut und der Hosting-Ablauf dies nicht automatisch übernimmt, führen Sie zusätzlich die zu dieser Shopware-Version gehörenden Build-Skripte aus, üblicherweise `bin/build-administration.sh` und `bin/build-storefront.sh`. Hosting-spezifische Build-Container oder Deployment-Befehle haben Vorrang; führen Sie niemals unbesehen ein fremdes Skript im Livesystem aus.

Vor `plugin:update MGDAIImageLabels` gehören Datenbank- und Dateibackup, Staging-Abnahme und ein geplantes Wartungsfenster. Danach folgen die vollständige Abnahme unten und ein kurzer Smoke-Test aller kritischen Verkaufskanäle.

## Abnahme

- Plugin ist installiert und aktiv; Shopware zeigt keine Lebenszyklusfehler.
- Ein Testbild lässt sich mit jedem Status speichern.
- `Keine KI-Kennzeichnung` zeigt kein sichtbares Badge.
- Auto, Deutsch und Englisch liefern die erwartete Verkaufskanalausgabe.
- alle vier Positionen sowie hell, dunkel und automatisch sind lesbar.
- Produktdetail, Listing, Suche, Warenkorb und Checkout bleiben bedienbar.
- eigenes Theme, mobile Ansicht, Vergrößerung und Screenreader wurden geprüft.
- das Hintergrundelement verlangt bei inhaltlichen Bildern einen Alternativtext.
- die Philosophie-Aktion erzeugt nur ein unverknüpftes Layout; Veröffentlichung bleibt manuell.
- Server- und Shopware-Protokolle enthalten keine neuen Fehler oder sensiblen Ausgaben.

## Kontrolliertes Live-Deployment

1. Neues, aktuelles Backup erstellen und Wiederherstellungspunkt bestätigen.
2. Wartungsfenster oder atomaren Wechsel vorbereiten.
3. Dasselbe geprüfte ZIP anhand seiner SHA-256-Prüfsumme verwenden.
4. Installation beziehungsweise Update und Theme-Build ausführen.
5. Unmittelbar einen kurzen Funktionstest in jedem Verkaufskanal durchführen.
6. Fehlerquote und Shopware-Protokoll für einen vereinbarten Zeitraum beobachten.
7. Erst nach erfolgreicher Prüfung das Deployment als abgeschlossen markieren.

Keine Datenbankzugänge, Admin-Sitzungen oder Schlüssel in Befehlsprotokolle, Tickets oder Screenshots aufnehmen.

## Rückfall ohne Datenlöschung

Bei Darstellungsproblemen ist die zuerst bevorzugte, reversible Maßnahme:

```bash
bin/console plugin:deactivate MGDAIImageLabels
bin/console cache:clear
bin/console theme:compile
```

Die Medienfelder bleiben dabei erhalten. Prüfen Sie danach den Shop erneut. Analysieren Sie die Ursache in Staging und aktivieren Sie das Plugin erst nach erneuter Freigabe.

Wenn ein Versionswechsel ursächlich ist, deaktivieren Sie das Plugin und stellen Sie die **vorherige Release-ZIP** beziehungsweise deren entpackten, zuvor geprüften Plugin-Ordner atomar wieder her. Bauen Sie Cache, Administration und Storefront entsprechend dem gleichen Hosting-Ablauf neu und führen Sie den Smoke-Test erneut aus.

Hat das fehlgeschlagene Update Datenbankzustand oder Inhalte verändert, müssen Code und dazugehöriges Datenbankbackup gemeinsam auf einen konsistenten Stand zurückgeführt werden. Das geschieht nur im Wartungsmodus und nach Prüfung des Wiederherstellungsplans. Ein altes Datenbankbackup darf niemals blind über zwischenzeitlich eingegangene Bestellungen, Kunden- oder Zahlungsdaten geschrieben werden; solche Änderungen müssen vor einem Rückfall gesichert und fachlich abgeglichen werden.

## Deinstallation

Shopwares Option **Benutzerdaten behalten** bewahrt das plugin-eigene Custom-Field-Set und die Plugin-Systemkonfiguration. Das Plugin sichert die exakt neun eigenen Einstellungswerte zusätzlich lokal, damit ein späterer Reinstall globale und verkaufskanalspezifische Werte nach Shopwares Default-Import wiederherstellen kann. Nach einem erfolgreichen Reinstall bleiben Eigentümerzeile, Generationskopf und die gesicherten Werte bewusst erhalten. Das ermöglicht einen sicheren Retry, falls Shopware erst nach dem Plugin-Installationsschritt abbricht. Der nächste Keep-Zyklus ersetzt diese Generation vollständig; eine Deinstallation ohne Datenerhalt entfernt die nach Schema und Eigentümermarker geprüfte Tabelle. Bei einem Fehler darf der Installationslauf nicht als erfolgreich behandelt werden; Snapshot und Datenbankbackup bleiben bis zur Analyse unverändert verfügbar.

Ohne diese Option entfernt das Plugin sein eindeutig geprüftes Medienfeld-Set einschließlich Felddefinitionen und Medienrelation sowie die vollständige Snapshot-Tabelle; Shopwares Lebenszyklus entfernt zusätzlich die Plugin-Systemkonfiguration. Medien, fremde Tabellen und fremde Custom Fields bleiben erhalten. Bereits in Medien-JSON gespeicherte Schlüssel können als technisch ungenutzte Werte bestehen bleiben, weil keine automatische Massenbereinigung der Medien erfolgt. Ein zuvor erstelltes Philosophie-Layout bleibt ebenfalls bestehen und muss nach redaktioneller Prüfung bei Bedarf manuell entfernt werden.

Vor einer Deinstallation ohne Datenerhalt:

1. Exportieren oder dokumentieren Sie benötigte Kennzeichnungswerte.
2. Erstellen Sie ein aktuelles Backup.
3. Prüfen Sie, ob veröffentlichte Seiten das Philosophie-Layout verwenden.
4. Deinstallieren Sie zuerst in Staging.
5. Bestätigen Sie ausdrücklich, dass Felddefinitionen, Medienrelation und Plugin-Systemkonfiguration gelöscht werden dürfen.
6. Planen Sie bei einer geforderten vollständigen Löschung der verbliebenen JSON-Schlüssel einen separaten, getesteten Bereinigungslauf.

## Abbruchkriterien

Brechen Sie das Deployment ab oder führen Sie den Rückfall aus, wenn Installation oder Theme-Build fehlschlägt, Eigentumskonflikte gemeldet werden, die Administration nicht mehr erreichbar ist, Checkout-Funktionen beeinträchtigt sind, Labels fremde Inhalte überdecken oder ungewollte Datenübertragungen beobachtet werden. Bewahren Sie nur bereinigte technische Nachweise für die Analyse auf.
