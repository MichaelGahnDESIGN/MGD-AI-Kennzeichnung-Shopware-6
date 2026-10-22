# Plugin-Icon 0.1.5

Lokale Icon-Dateien in `src/Resources/config`. Die Vektordatei ist die editierbare Quelle; PNG-Dateien sind im installierbaren Paket enthalten. Keine externen Bilddienste oder Datenübertragungen.

Shopware liest `extra.plugin-icon` aus composer.json. Die referenzierte PNG-Datei liegt unter `src/Resources/config/plugin.png`. Nach dem Pluginupdate kann ein erneutes Laden der Erweiterungsverwaltung nötig sein.

Rückfall: vorheriges Release-ZIP wieder einspielen. Es gibt keine Datenmigration und keine neuen Einstellungen. Keine Kundeninstallation im Rahmen dieser Veröffentlichung.
