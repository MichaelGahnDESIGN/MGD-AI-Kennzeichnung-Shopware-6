# Barrierefreiheit

Die Kennzeichnung soll auch ohne Farbwahrnehmung verständlich und mit assistiver Technik erreichbar sein.

## Technische Maßnahmen

- sichtbarer Text statt rein farblicher Markierung,
- semantischer Hinweis mit `role="note"`,
- zusätzlicher Screenreader-Text beim Deepfake-Status,
- keine `aria-hidden`-Ausblendung der Bedeutung,
- `pointer-events: none`, damit Links und Gesten am Bild bedienbar bleiben,
- reduzierter visueller Zustand für sehr kleine Medien bei erhaltenem zugänglichem Text,
- Unterstützung von `prefers-reduced-motion`,
- kontrastreicher Fallback ohne zwingende Hintergrundunschärfe.

## Kleine Bilder

Auf sehr kleinen Thumbnails wäre ein vollständiges sichtbares Badge unlesbar und könnte das Motiv verdecken. Das Plugin reduziert die visuelle Ausgabe deshalb. Der Text bleibt im Accessibility Tree erhalten.

Kleine Galerie-Navigationsbilder werden bewusst nicht mit einem zweiten sichtbaren Label überladen. Das Hauptbild bleibt der relevante Kontext.

## Alternativtext bleibt eigenständig

Das KI-Label ersetzt keinen Alt-Text. Beide Informationen beantworten unterschiedliche Fragen:

- Alt-Text: Was zeigt das Bild oder welchen Zweck erfüllt es?
- KI-Kennzeichnung: Wie wurde das Bild erstellt oder bearbeitet?

Das Plugin überschreibt vorhandene Alt-Texte nicht.

## Redaktionelle Prüfung

- Ist der Alternativtext inhaltlich sinnvoll?
- Ist das Bild wirklich dekorativ, wenn ein leerer Alt-Text gewählt wurde?
- Bleibt das Label bei 200 Prozent Zoom verständlich?
- Sind helles und dunkles Theme ausreichend kontrastreich?
- Ist Tastaturbedienung unverändert möglich?
- Wird der Deepfake-Zusatztext im Screenreader verständlich angekündigt?
- Verdeckt das Label keine entscheidenden Bildinformationen?

## Theme-Verantwortung

Ein Theme kann Farben, Abstände, Container und Überläufe verändern. Deshalb bleibt eine WCAG-orientierte Prüfung im tatsächlich verwendeten Storefront erforderlich. Die automatisierten Browsertests schützen die Plugin-Grundgeometrie, können aber nicht jede Kombination aus Theme, Inhalt und Zoom vollständig bewerten.

