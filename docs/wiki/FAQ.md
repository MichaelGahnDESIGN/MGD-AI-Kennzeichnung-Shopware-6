# Häufige Fragen

## Erkennt das Plugin KI-Bilder automatisch?

Nein. Es analysiert keine Bilder und sendet keine Dateien an einen KI-Dienst. Verantwortliche Redakteure wählen den Status selbst.

## Sagt mir das Plugin, ob eine Kennzeichnung rechtlich vorgeschrieben ist?

Nein. Es ist ein technisches Transparenzwerkzeug und keine Rechtsberatung.

## Wird das Label in die Bilddatei eingebrannt?

Nein. Das Originalmedium bleibt unverändert. Das Label wird beim Rendern des Storefronts ergänzt.

## Welche Sprachen werden ausgegeben?

Deutsch und Englisch. Im Standard **Automatisch** erhalten deutsche Verkaufskanäle Deutsch; andere Kontexte verwenden Englisch. Deutsch oder Englisch kann fest erzwungen werden.

## Warum sehe ich trotz gespeichertem Status kein Label?

Prüfen Sie, ob das Bild über Shopwares `sw_thumbnails` ausgegeben wird. Ein direkt in HTML eingetragenes `<img>` enthält kein Shopware-Medienobjekt. Cache, Theme-Build und ein vollständig überschriebenes Thumbnail-Template sind weitere mögliche Ursachen.

## Warum wird ein Bild als defektes Symbol angezeigt?

Öffnen Sie die Bild-URL direkt. Liefert sie HTTP 404, fehlen Datei oder Medieneintrag oder die URL ist veraltet. Das ist kein normaler Labelzustand. Folgen Sie der Seite [Fehlerbehebung](Fehlerbehebung).

## Funktionieren rohe HTML-Bilder?

Nicht automatisch. Ohne Medienobjekt kann das Plugin keine Custom Fields auflösen. Verwenden Sie Shopwares Bild-/Thumbnail-Ausgabe oder das plugin-eigene Hintergrundbild-Element.

## Was geschieht bei sehr kleinen Bildern?

Das sichtbare Badge wird platzsparend reduziert. Die zugängliche Textbedeutung bleibt für assistive Technik erhalten. Kleine Galerie-Navigationsthumbnails werden nicht doppelt gekennzeichnet.

## Kann ich beliebiges CSS in den Einstellungen speichern?

Nein. Position, Theme und Zahlenwerte sind bewusst begrenzt. Eigene Themes können die festen Plugin-Klassen kontrolliert überschreiben.

## Bleiben Daten bei der Deinstallation erhalten?

Mit Shopwares Option **Benutzerdaten behalten** bleiben Custom-Field-Definitionen, Medienwerte und Konfiguration erhalten. Ohne diese Option entfernt das Plugin seine Definitionen und Konfiguration; Bilddateien bleiben bestehen.

## Welche Shopware-Versionen sind geprüft?

Version `0.1.1` wurde vollständig mit Shopware `6.6.10.22` und `6.7.13.0` geprüft. Der Composer-Vertrag unterstützt `~6.6.10` und `~6.7.0`.

## Ist jedes eigene Theme automatisch kompatibel?

Nein. Themes, die Shopwares Thumbnail-Template vollständig umgehen, benötigen eine bewusste Integration. Standardnahe Themes, die `sw_thumbnails` und `parent()` erhalten, sind der vorgesehene Weg.

## Werden externe Dienste geladen?

Nein. Das Plugin benötigt für seine Laufzeit keine KI-, Tracking-, Schrift- oder Analysedienste.

## Wo melde ich ein Sicherheitsproblem?

Vertraulich über GitHub Security Advisories, nicht als öffentliches Issue mit Angriffsdaten. Keine Passwörter, Tokens, Kundendaten oder produktiven Logs mitsenden.

