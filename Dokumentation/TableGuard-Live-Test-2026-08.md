# Produktiver Referenztest im TableGuard-Shop

Version `0.1.1` wurde am 11. August 2026 nach vollständigen isolierten Tests zusätzlich in einem produktiven Shopware-Shop geprüft. Der Referenzshop verwendet Shopware `6.7.13.0`.

## Ergebnis

- Installation und Aktivierung erfolgreich
- Administration- und Storefront-Assets erfolgreich ausgeliefert
- Theme-Kompilierung und Cache-Bereinigung erfolgreich
- öffentliche Start-, Kategorie-, Produkt-, Warenkorb- und Inhaltsseiten weiterhin erreichbar
- Einstellungsseite und Medienvorschau in der Administration sichtbar
- nativer Shopware-Custom-Field-Bereich weiterhin vorhanden
- keine relevanten Plugin-Konsolen- oder Netzwerkfehler im lesenden Browsertest

Vor der Installation wurde eine lokale Datenbank- und Dateisicherung erstellt. Temporäre Installationshilfen wurden anschließend entfernt. Zugangsdaten und betriebliche Details sind nicht Bestandteil dieses öffentlichen Berichts.

Es wurde im Produktivtest bewusst kein reales Medium gekennzeichnet. Das Plugin erkennt KI-Inhalte nicht automatisch; die korrekte Kennzeichnung bleibt eine redaktionelle Entscheidung. Die umfassenden Status-, Positions-, Theme-, Sprach-, Barrierefreiheits- und Deinstallationsfälle wurden zuvor in isolierten Shopware-6.6.10.22- und 6.7.13.0-Umgebungen geprüft.

## Produktgalerie und Frontendtext in Version 0.1.2

Bei der anschließenden redaktionellen Kennzeichnung realer TableGuard-Medien wurde ein Theme-unabhängiger Shopware-Galerievertrag sichtbar: Das Hauptbild ist absolut positioniert, während der Galerieslot seine Höhe dynamisch ermittelt. Ein dazwischenliegender normaler Fill-Rahmen konnte deshalb auf null Pixel Höhe kollabieren, obwohl die Bilddatei vollständig geladen war.

Version `0.1.2` begrenzt die Reparatur auf einen Fill-Rahmen, der direkt unter Shopwares fester Klasse `.gallery-slider-item` liegt. Dieser Rahmen übernimmt den vorhandenen Slot absolut. Andere Bildkontexte, Galerievorschaubilder, Bildadressen, responsive Quellen, Alternativtexte, Lazy Loading, Zoomattribute und Slidersteuerung bleiben unverändert.

Zusätzlich verwendet das deutsche und englische Frontend für den Deepfake-Status einheitlich den sichtbaren Text `AI DEEPFAKE`. Der interne Statuswert und der zusätzliche Screenreader-Hinweis bleiben unverändert.

Vor dem Live-Update werden die fünf betroffenen Laufzeitdateien gesichert und mit SHA-256-Prüfsummen dokumentiert. Der Rückfall stellt diese Dateien wieder her und kompiliert anschließend Theme und Cache neu. Die produktive Browserabnahme umfasst Hauptbild, Label, Slider, Zoom, Galerievorschaubilder, Desktop, Mobil sowie die bereits integrierten Startseitenbilder.
