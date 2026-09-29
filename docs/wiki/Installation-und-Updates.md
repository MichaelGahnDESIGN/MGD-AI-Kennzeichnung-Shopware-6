# Installation und Updates

Diese Anleitung richtet sich an Shopbetreiber und Administratoren. Produktive Änderungen sollten zuerst in einer Staging-Umgebung geprüft werden.

## Voraussetzungen

| Bereich | Anforderung |
| --- | --- |
| Shopware | `~6.6.10` oder `~6.7.0` |
| PHP | `^8.2` |
| Rechte | Erweiterungen installieren, Cache leeren und Theme kompilieren |
| Sicherung | Datenbank und Plugin-Dateien vor jeder produktiven Änderung |

Version `0.1.1` wurde vollständig mit Shopware `6.6.10.22` und `6.7.13.0` geprüft. Andere kompatible Patchstände sollten vor dem produktiven Einsatz separat getestet werden.

## Vor der Installation

1. Datenbank sichern.
2. `custom/plugins` sichern.
3. Aktive Shopware-, PHP- und Theme-Version notieren.
4. Rückfallweg und zuständige Person festlegen.
5. Prüfen, ob individuelle Themes das Shopware-Thumbnail-Template ersetzen.

Backups müssen verschlüsselt oder angemessen zugriffsgeschützt gespeichert werden. Zugangsdaten, Kundendaten und Datenbankexporte gehören nicht in GitHub-Issues.

## Installation als ZIP

1. Das aktuelle ZIP auf der [Release-Seite](https://github.com/MichaelGahnDESIGN/MGD-AI-Kennzeichnung-Shopware-6/releases/latest) herunterladen.
2. In Shopware **Erweiterungen → Meine Erweiterungen** öffnen.
3. **Erweiterung hochladen** wählen und das ZIP unverändert hochladen.
4. **MGD AI Kennzeichnung Shopware 6** installieren.
5. Plugin aktivieren.
6. Cache leeren und Theme kompilieren.
7. Administration und Storefront prüfen.

## Installation per CLI

Den Ordner `MGDAIImageLabels` aus dem ZIP nach `custom/plugins/` kopieren. Danach im Shopware-Projekt ausführen:

```bash
bin/console plugin:refresh
bin/console plugin:install --activate MGDAIImageLabels
bin/console cache:clear
bin/console theme:compile
```

Die Befehle werden bewusst ohne Server- oder Datenbankzugänge gezeigt.

## Prüfung nach der Installation

- Plugin wird als installiert und aktiv angezeigt.
- Einstellungsseite lässt sich öffnen.
- Ein Bildmedium zeigt den Bereich **KI-Bildkennzeichnung**.
- Status, Position und Theme lassen sich auswählen.
- Vorschau reagiert, ohne bereits zu speichern.
- Nach dem Speichern bleibt das Originalbild erreichbar.
- Standard-Shopware-Bilder werden im Storefront weiterhin geladen.
- Cache und Theme-Bau enden ohne Fehler.
- Browserkonsole und Netzwerkansicht enthalten keine Pluginfehler.

Verwenden Sie ein dafür freigegebenes Testmedium. Kennzeichnen Sie keine echten Inhalte nur zu Testzwecken falsch.

## Update

### Einmaliger Umstieg auf den GitHub-Updater

Die Versionen bis einschließlich 0.1.2 besitzen keinen GitHub-Updater. Deshalb 0.1.3 einmalig über das unveränderte Release-ZIP in **Erweiterungen → Meine Erweiterungen** oder per CLI installieren. Ohne diesen Schritt kann eine alte Installation neue GitHub-Releases nicht erkennen.

### Danach: optionaler stündlicher Check

1. Changelog, Shopware-/PHP-Kompatibilität und Rückfallplan prüfen; Datenbank und Plugin-Dateien sichern.
2. In den Plugin-Einstellungen global **GitHub-Updates → Stündlich GitHub-Releases prüfen und Update vorbereiten** aktivieren. Standard ist **aus**.
3. Shopwares Scheduled-Task-Scheduler und Queue-Worker müssen tatsächlich laufen. Ohne sie findet kein periodischer Check statt.
4. Das Plugin fragt höchstens stündlich das neueste stabile öffentliche GitHub-Release ab. Nur das exakt zum Tag passende ZIP-Asset mit GitHub-SHA-256-Prüfsumme wird akzeptiert. ZIP-Pfade, Dateitypen, Plugin-Identität und unveränderte Composer-Laufzeitabhängigkeiten werden geprüft.
5. Die neue Version wird im privaten Ordner var/mgd-ai-image-labels mit einer Sicherung der bisherigen Plugin-Dateien vorbereitet. Danach **Plugin aktualisieren** in Shopware ausführen. Die Vorbereitung allein führt Shopwares Update-Lebenszyklus nicht aus.
6. Plugin-Aktivität, Konfiguration, Custom Fields, Administration, Storefront und Verkaufskanäle in beiden Sprachen prüfen.

Ein GitHub-Release löst **kein sofortiges Push-Ereignis** in Shopware aus. Der Rhythmus ist auf eine Stunde gesetzt; ein Administrator kann den geschützten Sofortcheck POST /api/_action/mgd-ai-image-labels/update/check mit der Shopware-Admin-API und system_config:update auslösen. Dafür keine Zugangsdaten in Skripten oder URLs speichern. Eine sichtbare Sofortcheck-Schaltfläche ist derzeit nicht Bestandteil der Administration.

Auch nach einem erfolgreichen Check wird **nicht stillschweigend** das Plugin aktualisiert. Der Administrator wählt Zeitpunkt und Wartungsfenster. Wenn das Release Laufzeitabhängigkeiten ändert, bricht die Vorbereitung ab; dann ist die native manuelle Shopware-Installation mit Kompatibilitätsprüfung erforderlich. Der Mechanismus funktioniert nur bei ZIP-Installationen unter custom/plugins/MGDAIImageLabels, nicht bei Composer-verwalteten Plugins. Der Webserver benötigt Schreibrechte für diesen Ordner und das private var-Verzeichnis. Bei fehlenden Rechten oder GitHub-Ausfall bleibt die bestehende Installation erhalten.

Die Vorbereitung verschiebt die vorhandenen Plugin-Dateien in eine private Rückfallsicherung. Zwischen zwei Verzeichnisumbenennungen gibt es eine kurze Umschaltlücke: Bei produktiven Shops ein Wartungsfenster verwenden. Die Sicherung nicht unbesehen löschen. Vor jedem produktiven Versionswechsel zunächst Staging prüfen; das Plugin ersetzt weder ein Datenbank- noch ein Serverbackup.

## Cache und Theme

Nach Installation, Update oder Theme-Wechsel:

```bash
bin/console cache:clear
bin/console theme:compile
```

Bei mehreren Verkaufskanälen jeden aktiven Kanal kontrollieren. CDN- oder Proxy-Caches müssen gegebenenfalls zusätzlich geleert werden.

## Rückfall

Bei einer Auffälligkeit zuerst das Plugin deaktivieren, Cache leeren und Theme kompilieren. Bleibt der Fehler bestehen, ist die Ursache wahrscheinlich nicht allein die aktive Plugin-Ausgabe. Für eine Deinstallation oder vollständige Wiederherstellung gilt die Anleitung [Deinstallation und Wiederherstellung](Deinstallation-und-Wiederherstellung).
