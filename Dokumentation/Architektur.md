# Architektur

## Zielbild

Das Plugin trennt gespeicherte Redaktionseingaben, geprüfte Fachwerte und die sichtbare Storefront-Ausgabe. Kein roher Custom-Field- oder Konfigurationswert gelangt direkt in eine CSS-Klasse, einen Übersetzungsschlüssel oder eine Zahl im HTML. Diese klare Grenze erleichtert Wartung und verhindert, dass unerwartete Daten die Ausgabe steuern.

## Bausteine

### Plugin-Lebenszyklus und Medienfelder

`src/MGDAIImageLabels.php` koordiniert Installation, Update und Deinstallation. `src/Setup/CustomFieldSetDefinitionFactory.php` erzeugt das eigene Custom-Field-Set mit reproduzierbaren IDs. `src/Setup/CustomFieldSetInstaller.php` prüft vor jeder Änderung Name und Eigentum. Dadurch werden gleichnamige oder kollidierende fremde Datensätze nicht überschrieben.

`ConfigurationRetentionService` koordiniert den Konfigurations-Datenerhalt. `ConfigurationKeys` ist die einzige Positivliste der exakt neun erlaubten Schlüssel. `ConfigurationBackupStorage` liest diese Werte bei einer Keep-Deinstallation direkt aus `system_config`, damit globale und verkaufskanalspezifische Einträge unabhängig vom Aktivierungszustand erfasst werden. Beim Reinstall läuft die Wiederherstellung in `plugin->install()`, nachdem Shopware seine Standardwerte geschrieben hat. Der verbrauchte Snapshot wird in derselben Transaktion gelöscht. Ein normaler Update- oder Installationslauf ohne Snapshot kann deshalb keine alten Werte zurückspielen.

Die drei Medienfelder sind bewusst geschlossen:

- `mgd_ai_status`: fachlicher KI-Status
- `mgd_ai_position`: optionale Position für dieses Medium
- `mgd_ai_theme`: optionales Erscheinungsbild für dieses Medium

Die Sprache gehört nicht ans Medium. Sie wird aus Plugin-Konfiguration und aktuellem Verkaufskanal bestimmt.

### Normalisierung und Viewmodell

Enums unter `src/Domain/` bilden ausschließlich erlaubte Status-, Positions-, Theme- und Sprachwerte ab. Die Normalizer unter `src/Media/` und `src/Configuration/` setzen bei fehlenden oder ungültigen Daten sichere Standardwerte. `LabelViewResolver` verbindet diese geprüften Werte zu einem unveränderlichen `LabelView` für Twig.

Ein Status `none`, ein unbekannter Status oder fehlende Daten erzeugen kein sichtbares Label. Medienbezogene Position und Theme haben Vorrang vor den globalen Darstellungswerten. Die Sprachwahl kann pro Verkaufskanal gespeichert werden; `auto` folgt dessen Locale und fällt für nicht deutsche Sprachen auf Englisch zurück.

### Storefront

Die Twig-Erweiterungen bieten kleine, klar begrenzte Funktionen zum Auflösen von Label, Thumbnail-Darstellung und Philosophie-Inhalt. `src/Resources/views/storefront/utilities/thumbnail.html.twig` erweitert Shopwares Standard-Thumbnail, ohne dessen Bildaufbau nachzubauen. Ein Rahmen legt die Kennzeichnung über das fertig gerenderte Bild. Kleine Galerie-Navigationselemente werden bewusst ausgenommen.

Das Badge verwendet nur feste Klassenzuordnungen und numerische, zuvor begrenzte CSS-Variablen. Unbekannte freie CSS-Werte werden nicht übernommen. Auf kleinen Bildern bleibt ein zugänglicher Textzustand erhalten, auch wenn die sichtbare Fläche nicht für das Badge reicht.

### Administration und Erlebniswelten

Die Medienvorschau liest den noch nicht gespeicherten lokalen Bearbeitungszustand, speichert aber nicht selbst. Die vorhandene Shopware-Speicheraktion bleibt die einzige Schreibaktion.

Das Element `mgd-ai-background-image` akzeptiert ausschließlich ein lokales Bildmedium und geschlossene Werte für Mindesthöhe, Bildposition, Dekoration und Hintergrundfarbe. Der PHP-CMS-Resolver validiert und normalisiert die Darstellung.

Die KI-Philosophie ist ein eigenes CMS-Element mit bereinigtem HTML aus einer engen Positivliste. Der Admin-Endpunkt zum Vorbereiten des Layouts ist authentifiziert und über Shopware-ACLs geschützt. Die Seite erhält eine deterministische ID und eine unsichtbare Eigentumskennung. Sie wird weder veröffentlicht noch mit Navigation, Kategorie, Landingpage oder Verkaufskanal verknüpft.

## Datenfluss

1. Redaktion ordnet einem Shopware-Medium Werte zu.
2. Shopware speichert sie in den vorhandenen `custom_fields` des Mediums.
3. Der Storefront-Aufruf übergibt das Medium an den Label-Resolver.
4. Normalizer verwerfen unbekannte Werte und begrenzen Zahlen.
5. Der Sprachresolver berücksichtigt Plugin- und Verkaufskanal-Kontext.
6. Twig erhält nur das geprüfte Viewmodell.
7. Das Theme rendert Text, feste Klassen und begrenzte CSS-Variablen.

## Datenbankentscheidungen

Das Plugin nutzt Shopwares Systemkonfiguration, Medien-Custom-Fields und auf ausdrückliche Aktion die Standard-CMS-Entitäten. Zusätzlich existiert genau eine kleine technische Tabelle `mgd_ai_image_labels_config_backup`. Sie enthält eine zufällige technische ID, einen positiv gelisteten Konfigurationsschlüssel, optional die Verkaufskanal-ID, den erwarteten Skalartyp, den Shopware-JSON-Wert und Zeitstempel. Sie enthält keine Bilder, Kunden-, Bestell-, Zahlungs-, Konto- oder Sitzungsdaten.

Der Snapshot wird vor jeder Keep-Deinstallation vollständig ersetzt. Ein vollständiger und typgeprüfter Restore löscht ihn sofort; eine fehlgeschlagene Wiederherstellung rollt alle Änderungen zurück und behält ihn für eine kontrollierte Fehlerbehebung. Ohne Datenerhalt wird die Tabelle explizit entfernt. Feste Set-IDs sowie Eigentumsprüfungen schützen weiterhin fremde Custom-Field-Datensätze.

## Fehler- und Rückfallverhalten

Ungültige redaktionelle Werte führen zu sicheren Standardwerten oder zu einem ausgeblendeten Label. Eigentumskonflikte bei Installation oder CMS-Erstellung brechen mit einer verständlichen Ausnahme ab, statt Daten zu überschreiben. Für betriebliche Rückfälle siehe [Deployment-und-Rueckfall.md](Deployment-und-Rueckfall.md).

## Kompatibilitätsstatus

Die statischen, Unit-, JavaScript- und Headless-Tests bilden den Quellcodevertrag ab. Die reale Matrix mit Installation, Update, Administration-Build und Storefront-Prüfung unter Shopware 6.6.10 und 6.7 ist noch offen und muss vor einer stabilen Produktionsfreigabe nachvollziehbar dokumentiert werden.
