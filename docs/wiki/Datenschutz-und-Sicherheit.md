# Datenschutz und Sicherheit

Das Plugin folgt dem Prinzip der Datenminimierung. Für eine Bildkennzeichnung werden weder automatische Bilderkennung noch externe Dienste benötigt.

## Verarbeitete Daten

Am Shopware-Medium werden nur diese redaktionellen Werte gespeichert:

```text
mgd_ai_status
mgd_ai_position
mgd_ai_theme
```

Globale und verkaufskanalbezogene Darstellungswerte liegen in Shopwares Systemkonfiguration. Das Plugin erzeugt keine eigenen Nutzerprofile.

## Was nicht passiert

- keine Übertragung von Bildern an KI-Anbieter,
- keine Gesichtserkennung,
- keine automatische Inhaltsbewertung,
- kein Tracking und keine Telemetrie,
- keine externen Schriften oder Analyse-Skripte,
- keine Speicherung eigener Passwörter oder Tokens,
- keine Verarbeitung von Zahlungsdaten,
- keine Veränderung der Bilddatei.

## Technische Schutzmaßnahmen

- geschlossene Enums für Status, Position und Theme,
- serverseitige Normalisierung aller Custom Fields,
- begrenzte Ganzzahlwerte für Gestaltung,
- sichere Standardwerte bei ungültiger Konfiguration,
- Shopware-DAL statt eigener unkontrollierter Datenbankzugriffe im normalen Betrieb,
- Shopware-ACL für Administration und CMS-Aktion,
- authentifizierte Admin-API-Anfrage,
- HTML-Sanitizer und Positivliste für KI-Philosophie-Inhalte,
- Bildtypprüfung im Admin und erneut serverseitig,
- deterministische Eigentumsmarker für plugin-eigene Definitionen,
- keine freie URL-, Klassen- oder Style-Steuerung aus Medienfeldern.

## Betreiberpflichten

Der Shopbetreiber bleibt verantwortlich für:

- Shopware- und Server-Sicherheitsupdates,
- TLS und sichere Administration,
- Rollen und minimale Rechte,
- Backups und Wiederherstellungstests,
- Schutz von Protokollen und Datenbankzugängen,
- Datenschutzerklärung und interne Redaktionsprozesse,
- rechtliche Prüfung der konkreten Kennzeichnung.

## Sicherheitslücken melden

Vermutete Sicherheitslücken niemals mit technischen Angriffsdaten als öffentliches Issue veröffentlichen. Verwenden Sie nach Möglichkeit den privaten GitHub-Bereich **Security → Advisories → Report a vulnerability**.

Eine hilfreiche Meldung enthält:

- Plugin-, Shopware- und PHP-Version,
- künstliche Reproduktionsdaten,
- nachvollziehbare Schritte,
- erwartetes und tatsächliches Verhalten,
- Einschätzung der Auswirkung.

Keine Passwörter, Sitzungscookies, Kundendaten, Datenbankexporte, produktiven Logs oder internen Hostnamen mitsenden. Details stehen in der [Sicherheitsrichtlinie](https://github.com/MichaelGahnDESIGN/MGD-AI-Kennzeichnung-Shopware-6/blob/main/SECURITY.md).

## DSGVO-Einordnung

Die drei Kennzeichnungswerte sind normalerweise keine personenbezogenen Daten. Das zugrunde liegende Bild kann jedoch Personen oder andere personenbezogene Informationen enthalten. Das Plugin ändert daran nichts und überträgt das Bild nicht. Rechtsgrundlage, Informationspflichten, Löschkonzept und Zugriffsrechte des Shopbetriebs bleiben unabhängig vom Plugin zu bewerten.

