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

1. Changelog und unterstützte Versionen lesen.
2. Neues Backup anlegen.
3. Update zuerst in Staging installieren.
4. Plugin-Aktivität, Konfiguration und Custom Fields prüfen.
5. Administration und Storefront bauen.
6. Verkaufskanäle in beiden Sprachen testen.
7. Erst danach produktiv aktualisieren.

## Cache und Theme

Nach Installation, Update oder Theme-Wechsel:

```bash
bin/console cache:clear
bin/console theme:compile
```

Bei mehreren Verkaufskanälen jeden aktiven Kanal kontrollieren. CDN- oder Proxy-Caches müssen gegebenenfalls zusätzlich geleert werden.

## Rückfall

Bei einer Auffälligkeit zuerst das Plugin deaktivieren, Cache leeren und Theme kompilieren. Bleibt der Fehler bestehen, ist die Ursache wahrscheinlich nicht allein die aktive Plugin-Ausgabe. Für eine Deinstallation oder vollständige Wiederherstellung gilt die Anleitung [Deinstallation und Wiederherstellung](Deinstallation-und-Wiederherstellung).

