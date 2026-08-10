# Sicherheit

## Sicherheitslücken verantwortungsvoll melden

Bitte veröffentlichen Sie vermutete Sicherheitslücken nicht als öffentliches Issue. Nutzen Sie, sobald das GitHub-Repository veröffentlicht ist, dessen privaten Bereich **Security > Advisories > Report a vulnerability**. Falls dieser Meldeweg vorübergehend nicht verfügbar ist, eröffnen Sie nur eine neutrale Kontaktanfrage ohne technische Angriffsdaten.

Eine hilfreiche Meldung enthält betroffene Version, Shopware- und PHP-Version, nachvollziehbare Schritte, erwartetes und tatsächliches Verhalten sowie eine Einschätzung der Auswirkung. Verwenden Sie ausschließlich Testdaten.

Senden Sie niemals Passwörter, Zugangsschlüssel, Sitzungscookies, personenbezogene Daten, Datenbankexporte, produktive Protokolle oder vollständige Kundendateien. Schwärzen Sie Hostnamen, interne Pfade und IDs. Ein minimaler, künstlicher Testfall ist ausreichend.

## Unterstützte Versionen

Bis zum Abschluss der realen Kompatibilitätsmatrix wird `0.1.0` als Vorabversion gepflegt. Sicherheitskorrekturen sollen auf der jeweils aktuellen veröffentlichten Version erfolgen. Ältere Stände erhalten nur nach ausdrücklicher Ankündigung Rückportierungen.

## Schutzmodell

Das Plugin nutzt Shopwares Authentifizierung, Rollenrechte, Datenabstraktionsschicht, Template-Bereinigung und Systemkonfiguration. Es speichert keine eigenen Konten und keine Zahlungsdaten. Bilder und Kennzeichnungswerte werden nicht an externe Dienste übertragen. Die CMS-Philosophie wird nur nach einer berechtigten, bewussten Admin-Aktion vorbereitet und nicht automatisch veröffentlicht.

Administratorinnen und Administratoren bleiben für Serverhärtung, Shopware-Sicherheitsupdates, TLS, Datenbankzugriff, Backups, Protokollschutz und eine sparsame Rechtevergabe verantwortlich.

## Ablauf nach einer Meldung

Der Eingang wird vertraulich geprüft. Nach Bestätigung werden Auswirkung, betroffene Versionen, Korrektur und ein sicherer Veröffentlichungszeitpunkt abgestimmt. Bitte veröffentlichen Sie Details erst nach Freigabe oder nachdem ausreichend Zeit für ein Update bestand.
