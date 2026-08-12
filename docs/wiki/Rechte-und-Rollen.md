# Rechte und Rollen

Das Plugin verwendet Shopwares vorhandene Authentifizierung und Rollenrechte. Rollen sollten nur die Berechtigungen erhalten, die sie für ihre Aufgabe wirklich benötigen.

## Bilder kennzeichnen

Das normale Bearbeiten folgt Shopwares Medien- und Custom-Field-Rechten. Eine Redaktion benötigt Zugriff auf die Medienverwaltung sowie die Berechtigung, die betroffenen Medienfelder zu ändern.

Das Plugin eröffnet keinen alternativen Speicherendpunkt für diese drei Felder. Vorschau und Darstellung sind lokal; gespeichert wird über Shopwares vorhandene Aktion.

## Plugin-Konfiguration

Für globale und verkaufskanalbezogene Einstellungen ist Shopwares Systemkonfigurationsrecht erforderlich:

```text
system_config:update
```

## KI-Philosophie vorbereiten

Die Admin-Aktion verlangt alle folgenden Rechte:

```text
system_config:update
cms_page:create
cms_section:create
cms_block:create
cms_slot:create
```

Fehlt eines dieser Rechte, wird die Aktion nicht angeboten beziehungsweise sicher abgewiesen.

## Empfohlene Rollenaufteilung

| Rolle | Aufgaben |
| --- | --- |
| Redaktion | Medien prüfen, Status pflegen, Alternativtexte kontrollieren |
| Shop-Administration | Plugin installieren, Konfiguration, Cache und Theme |
| CMS-Verantwortliche | KI-Philosophie und Hintergrundbild-Elemente bearbeiten |
| Entwicklung/Agentur | Theme-Integration, Tests, Updates und Fehleranalyse |

## Sicherheitsprinzip

- keine gemeinsamen Administratorkonten,
- minimale Rechte pro Rolle,
- Zwei-Faktor-Authentifizierung, wenn im Betrieb verfügbar,
- keine Tokens oder Sitzungsdaten in Tickets,
- Rechte nach Projektende wieder entziehen,
- produktive Schreibaktionen protokollierbar und rücknehmbar planen.

