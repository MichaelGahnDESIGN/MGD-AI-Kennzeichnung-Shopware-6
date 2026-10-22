# Plugin-Icon 0.1.5

Lokale Icon-Dateien in `src/Resources/config`. Die Vektordatei ist die editierbare Quelle; PNG-Dateien sind im installierbaren Paket enthalten. Keine externen Bilddienste oder Datenübertragungen.

Shopware liest `extra.plugin-icon` aus composer.json. Die referenzierte PNG-Datei liegt unter `src/Resources/config/plugin.png`. Nach dem Pluginupdate kann ein erneutes Laden der Erweiterungsverwaltung nötig sein.

Rückfall: vorheriges Release-ZIP wieder einspielen. Es gibt keine Datenmigration und keine neuen Einstellungen. Keine Kundeninstallation im Rahmen dieser Veröffentlichung.

Die CI-Matrix verwendet die bereits dokumentierten Prüfversionen Shopware 6.6.10.27 und 6.7.14.2. Die bisher offenen Versionsbereiche installierten inzwischen neuere Tags, die nicht im vorhandenen Quellcode-Vertragstest hinterlegt sind. Für neuere Shopware-Versionen wird damit keine zusätzliche Kompatibilitätsprüfung behauptet.
