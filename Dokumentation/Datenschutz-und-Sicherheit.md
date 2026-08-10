# Datenschutz und Sicherheit

## Kurzfassung

Das Plugin arbeitet lokal innerhalb der bestehenden Shopware-Installation. Es überträgt weder Bilder noch Kennzeichnungsdaten an externe Dienste. Es erkennt keine KI-Inhalte und erstellt keine biometrischen Profile. Die redaktionelle Zuordnung bleibt eine menschliche Entscheidung.

Diese Dokumentation ist eine technische Beschreibung und keine Rechtsberatung. Shopbetreibende müssen ihre konkrete Verarbeitung, Inhalte und Informationspflichten selbst datenschutzrechtlich bewerten.

## Verarbeitete Daten

Das Plugin verwendet:

- den gewählten KI-Status eines Mediums
- optional Position und Theme des Labels am Medium
- globale Darstellungswerte und die Sprachwahl in Shopwares Systemkonfiguration
- die Sprache und ID des aktuellen Verkaufskanals zur Ausgabe
- auf ausdrückliche Admin-Aktion ein unverknüpftes Erlebniswelten-Layout mit redaktionellem Text

Die Werte werden in Shopwares vorhandenen Datenstrukturen gespeichert. Es gibt keine eigene Tracking-ID, kein Nutzerprofil und keine eigene Datenbanktabelle. Das Plugin schreibt keine Zahlungs-, Bestell-, Adress-, E-Mail-, Konto- oder Sitzungsdaten.

## Keine externe Übertragung

Es werden keine Analyse-, Telemetrie-, KI-, CDN- oder Tracking-Anfragen durch das Plugin eingerichtet. Das ausgewählte Bild bleibt ein normales Shopware-Medium. Ob die allgemeine Shopinstallation Bilder über ein CDN oder andere Erweiterungen ausliefert, liegt außerhalb dieses Plugins und muss separat geprüft werden.

Die Administrationsvorschau verwendet die bereits von Shopware bereitgestellte Medienadresse. Sie startet keine Bildklassifikation und erzeugt keine zusätzliche Datei.

## Keine automatische KI-Erkennung

Eine zuverlässige automatische Erkennung ist nicht Bestandteil des Plugins. Das Label beweist weder, dass ein Bild KI-generiert ist, noch dass ein nicht gekennzeichnetes Bild ohne KI entstanden ist. Verantwortliche Redaktionen benötigen einen nachvollziehbaren Prozess, etwa Angaben von Urhebern, Produktionsnotizen und eine Freigabe vor Veröffentlichung.

## Eingabe- und Ausgabeschutz

Status, Position, Theme und Layoutoptionen stammen aus festen Positivlisten. Zahlen werden auf festgelegte Bereiche begrenzt. Das Storefront erhält ein geprüftes Viewmodell statt roher Medienwerte. CMS-Philosophie-HTML wird serverseitig auf erlaubte, einfache Inhaltselemente reduziert; Skripte, Styles und Ereignisattribute gehören nicht dazu.

Die CMS-Hintergrundkonfiguration akzeptiert kein freies HTML, keine freie URL und keine freie CSS-Klasse. Nicht dekorative Bilder erfordern einen Alternativtext. Dennoch müssen eigene Themes und redaktionelle Inhalte im Zielshop getestet werden.

## Rechte und Zugriff

Shopwares Rollen- und Rechteverwaltung schützt Medienbearbeitung und Systemkonfiguration. Das Vorbereiten der KI-Philosophie erfordert ausdrücklich Rechte für Systemkonfiguration sowie das Erstellen der benötigten CMS-Strukturen. Nach dem Prinzip der geringsten Rechte sollen nur zuständige Rollen diese Berechtigungen erhalten.

Der Endpunkt ist eine authentifizierte Admin-API-Aktion. Er veröffentlicht keine Seite. Die deterministische ID und zusätzliche Eigentumsprüfung verhindern, dass ein fremder CMS-Datensatz still überschrieben wird.

## Protokolle und Fehlermeldungen

Das Plugin protokolliert selbst keine Bilder, Custom-Field-Inhalte oder personenbezogenen Daten. Shopware oder der Webserver können technische Fehler allgemein protokollieren. Produktionsprotokolle benötigen Zugriffsbeschränkung, kurze angemessene Aufbewahrung und Schwärzung vor einer Weitergabe.

Sicherheitsberichte dürfen keine echten Zugangsdaten, Kundendaten, Datenbankexporte oder produktiven Medien enthalten. Der vertrauliche Meldeweg steht in [../SECURITY.md](../SECURITY.md).

## Aufbewahrung und Löschung

Kennzeichnungswerte bleiben so lange am Medium gespeichert, wie der Shop sie benötigt. Bei einer Deinstallation mit Shopwares Option **Benutzerdaten behalten** bleiben die plugin-eigenen Felder bestehen. Ohne diese Option entfernt der Lebenszyklus ausschließlich das eindeutig plugin-eigene Custom-Field-Set. Medien selbst und fremde Felder bleiben unberührt.

Ein vorbereiteter Philosophie-Inhalt wird bewusst nicht automatisch gelöscht, da er redaktionell verändert oder veröffentlicht worden sein kann. Verantwortliche entfernen ihn bei Bedarf manuell über Erlebniswelten.

## Betriebliche Empfehlungen

- Sicherheitsupdates für Shopware, PHP, Webserver und Datenbank zeitnah einspielen.
- Administration nur verschlüsselt und mit starker Authentifizierung erreichbar machen.
- Rollen sparsam vergeben und regelmäßig prüfen.
- Backups verschlüsseln, Zugriff begrenzen und Wiederherstellung testen.
- Staging mit anonymisierten oder künstlichen Daten betreiben.
- Änderungen zuerst in Staging und anschließend in jedem Verkaufskanal prüfen.

Die reale Shopware-6.6.10-/6.7-Kompatibilitätsmatrix ist noch nicht abgeschlossen. Bis zu ihrem dokumentierten Abschluss ist eine individuelle Staging-Prüfung zwingend.
