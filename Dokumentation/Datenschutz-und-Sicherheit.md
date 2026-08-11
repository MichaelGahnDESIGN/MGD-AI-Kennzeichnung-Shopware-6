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

Die Werte werden überwiegend in Shopwares vorhandenen Datenstrukturen gespeichert. Für eine Reinstallation nach **Benutzerdaten behalten** nutzt das Plugin zusätzlich die kleine Tabelle `mgd_ai_image_labels_config_backup`. Darin stehen ausschließlich die exakt neun eigenen Konfigurationsschlüssel, deren globale oder verkaufskanalspezifische Zuordnung, Skalartyp, JSON-Wert und technische Zeitstempel. Es gibt keine Tracking-ID und kein Nutzerprofil. Das Plugin schreibt keine Bild-, Zahlungs-, Bestell-, Adress-, E-Mail-, Konto- oder Sitzungsdaten in diese Tabelle.

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

Kennzeichnungswerte bleiben so lange am Medium gespeichert, wie der Shop sie benötigt. Bei einer Deinstallation mit Shopwares Option **Benutzerdaten behalten** bleiben das plugin-eigene Custom-Field-Set und die Plugin-Systemkonfiguration bestehen. Ein lokaler Snapshot bewahrt die neun Einstellungen über Shopwares Default-Import beim Reinstall. Nach dem Schreiben wird der vollständige Datenbankzustand noch einmal direkt und strikt gegen den Snapshot geprüft. Der Snapshot bleibt bis zum nächsten Keep-Zyklus bestehen, damit ein Fehler in einem späteren Shopware-Installationsschritt einen sicheren Retry erlaubt. Ein Eigentümermarker, ein Generationskopf, eindeutige Scope-Hashes und eine Datenbanksperre schützen leere Snapshots, Parallelzugriffe und fremde gleichnamige Tabellen. Gespeichert werden nur Plugin-Einstellungen, keine Nutzer- oder Shopinhalte.

Die Wiederherstellung und die direkte Nachprüfung laufen in einer Datenbanktransaktion. Datenbankänderungen werden bei einem Fehler zurückgerollt. Bereits ausgeführte Reaktionen fremder Event-Listener außerhalb dieser Transaktion können technisch nicht zurückgerollt werden; deshalb darf ein fehlgeschlagener Installationslauf nicht als erfolgreich behandelt werden und muss nach Ursachenbehebung wiederholt werden.

Ohne Datenerhalt löscht das Plugin sein eindeutig eigenes Set und die vollständige Snapshot-Tabelle; Shopwares Kaskade entfernt Felddefinitionen und Medienrelation. Das Plugin entfernt zusätzlich alle globalen und verkaufskanalspezifischen Zeilen seiner exakt neun positiv gelisteten Konfigurationsschlüssel. Das schließt eine bekannte Lücke in Shopware 6.6, das kanalbezogene Werte beim normalen Uninstall stehen lassen kann. Fremde Schlüssel, Tabellen und Konfigurationen sind weder Teil der Positivliste noch Ziel einer Löschung.

Die Medienentität speichert Custom-Field-Werte als JSON. Das Entfernen der Definition löscht diese drei JSON-Schlüssel nicht nachweisbar aus jedem bestehenden Medium; sie können als technisch ungenutzte Werte verbleiben. Das vermeidet eine schwer kontrollierbare Massenänderung an Medien, bedeutet aber auch, dass eine vollständige fachliche Datenlöschung einen separaten, vorher gesicherten und in Staging geprüften Bereinigungslauf benötigt. Bilddateien, fremde Felder und andere Medienwerte bleiben unberührt.

Ein vorbereiteter Philosophie-Inhalt wird bewusst nicht automatisch gelöscht, da er redaktionell verändert oder veröffentlicht worden sein kann. Verantwortliche entfernen ihn bei Bedarf manuell über Erlebniswelten.

## Betriebliche Empfehlungen

- Sicherheitsupdates für Shopware, PHP, Webserver und Datenbank zeitnah einspielen.
- Administration nur verschlüsselt und mit starker Authentifizierung erreichbar machen.
- Rollen sparsam vergeben und regelmäßig prüfen.
- Backups verschlüsseln, Zugriff begrenzen und Wiederherstellung testen.
- Staging mit anonymisierten oder künstlichen Daten betreiben.
- Änderungen zuerst in Staging und anschließend in jedem Verkaufskanal prüfen.

Die reale Matrix für Version 0.1.1 ist unter Shopware 6.6.10.22 und 6.7.13.0 abgeschlossen. Eine individuelle Staging-Prüfung bleibt wegen shopabhängiger Themes, Plugins, Rollen und Infrastruktur zwingend.
