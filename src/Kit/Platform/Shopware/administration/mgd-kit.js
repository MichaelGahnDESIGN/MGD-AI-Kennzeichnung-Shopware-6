// MGD Plugin-Baukasten - Admin-Seite "MGD AI Kennzeichnung" fertig geschrieben, kein Build nötig
// Daten aus /api/_action/mgd-kit/mgd-ai-kennzeichnung-shopware/... , keine fremden Bilder/Schriften (Bilder als data:-URI über /media)
(function (root) {
    'use strict';

    var PRODUCT = 'mgd-ai-kennzeichnung-shopware';
    var MODULE = 'mgd-kit-' + PRODUCT;
    var API = '/_action/mgd-kit/' + PRODUCT;
    var STYLE_ID = MODULE + '-style';
    // Links nur auf diese Adressen (Server prüft auch)
    var LINK_PREFIXES = ['https://theme.michael-gahn.de/', 'https://github.com/MichaelGahnDESIGN/'];
    var PLUGIN_PRIVS = ['system.plugin_maintain', 'system.plugin_upload'];
    var FILE_RE = /^[a-f0-9]{32}\.webp$/;
    var DATA_RE = /^data:image\/webp;base64,[A-Za-z0-9+\/]+={0,2}$/;
    var ACCENT_RE = /^#[0-9a-fA-F]{6}$/;
    // Katalog-CSS (Catalog/assets/kit-catalog.css), setzt der Baukasten beim Kopieren ein
    var CATALOG_CSS = "/* Weitere Plugins JTL + Shopware - Kopie aus Platform/WordPress/assets/kit-catalog.css, gleiche Klassen  */\n/* hell, keine fremden Schriften/Bilder. Shopware kriegt die Datei beim Bauen in mgd-kit.js reinkopiert */\n.mgd-kit-catalog {\n  --ink: #172d2a;\n  --muted: #4d5d59;\n  --line: #d9e1dc;\n  --paper: #f5f6f2;\n  --accent: #b51f2a;\n  --focus: #a51d29;\n  container-type: inline-size;\n  color: var(--ink);\n  max-width: 1500px;\n  margin: 0;\n  font-size: 14px;\n  line-height: 1.6;\n  overflow-wrap: anywhere;\n}\n.mgd-kit-catalog *,\n.mgd-kit-catalog *::before,\n.mgd-kit-catalog *::after {\n  box-sizing: border-box;\n}\n.mgd-kit-catalog [hidden] {\n  display: none !important;\n}\n/* nur für Screenreader, gibts in JTL/Shopware nicht wie in WP */\n.mgd-kit-catalog .mgd-kit-catalog__sr {\n  position: absolute !important;\n  width: 1px;\n  height: 1px;\n  padding: 0;\n  margin: -1px;\n  overflow: hidden;\n  clip: rect(0, 0, 0, 0);\n  white-space: nowrap;\n  border: 0;\n}\n.mgd-kit-catalog .mgd-kit-catalog__stale {\n  color: var(--muted);\n  font-size: 13px;\n}\n.mgd-kit-catalog a,\n.mgd-kit-catalog button {\n  text-decoration: none !important;\n}\n/* Reset gegen kit-jtl.css (.mgd-kit-jtl button / label / h3) */\n.mgd-kit-catalog button,\n.mgd-kit-catalog .mgd-kit-catalog__button {\n  margin: 0;\n  font-family: inherit;\n}\n.mgd-kit-catalog label {\n  min-height: 0;\n}\n.mgd-kit-catalog h2,\n.mgd-kit-catalog h3,\n.mgd-kit-catalog h4 {\n  font-family: inherit;\n}\n.mgd-kit-catalog p {\n  font-size: inherit;\n}\n/* Fokus immer sichtbar */\n.mgd-kit-catalog a:focus-visible,\n.mgd-kit-catalog button:focus-visible,\n.mgd-kit-catalog input:focus-visible,\n.mgd-kit-catalog select:focus-visible {\n  outline: 3px solid var(--focus) !important;\n  outline-offset: 3px;\n  box-shadow: none !important;\n}\n/*Hero Kopfbereich */\n.mgd-kit-catalog .mgd-kit-catalog__hero {\n  position: relative;\n  display: flex;\n  align-items: center;\n  justify-content: space-between;\n  overflow: hidden;\n  background: #172d2a;\n  color: #fff;\n  border-radius: 20px;\n  padding: 32px 40px;\n  min-height: 260px;\n}\n.mgd-kit-catalog__hero-copy {\n  position: relative;\n  z-index: 1;\n  min-width: 0;\n}\n.mgd-kit-catalog .mgd-kit-catalog__eyebrow {\n  display: flex;\n  flex-wrap: wrap;\n  align-items: center;\n  gap: 12px;\n  letter-spacing: 0.16em;\n  font-size: 11px;\n  font-weight: 700;\n  color: #d6e4db;\n  margin: 0 0 16px;\n}\n.mgd-kit-catalog__brand {\n  display: inline-flex;\n  align-items: center;\n  justify-content: center;\n  background: #c3202d;\n  color: #fff;\n  border-radius: 5px;\n  width: 44px;\n  height: 32px;\n  letter-spacing: -1px;\n  font-size: 16px;\n}\n.mgd-kit-catalog .mgd-kit-catalog__hero h3 {\n  font-size: 38px;\n  line-height: 1.12;\n  font-weight: 700;\n  letter-spacing: -1.3px;\n  color: #fff;\n  margin: 0 0 17px;\n  padding: 0;\n}\n.mgd-kit-catalog .mgd-kit-catalog__hero h3 em {\n  font-style: normal;\n  color: #e5c58e;\n}\n.mgd-kit-catalog .mgd-kit-catalog__hero-copy \u003E p:last-child {\n  color: #d3ded7;\n  margin: 0;\n  line-height: 1.6;\n  font-size: 14px;\n}\n.mgd-kit-catalog__hero-art {\n  position: relative;\n  flex: 0 0 34%;\n  height: 215px;\n  margin-left: 20px;\n}\n.mgd-kit-catalog__orbit {\n  position: absolute;\n  width: 270px;\n  height: 270px;\n  border: 1px solid #ffffff24;\n  border-radius: 50%;\n  top: -35px;\n  left: 50%;\n  transform: translateX(-50%);\n}\n.mgd-kit-catalog__orbit::after {\n  content: \"\";\n  position: absolute;\n  inset: 28px;\n  border: 1px solid #ffffff15;\n  border-radius: 50%;\n}\n.mgd-kit-catalog__hero-icon {\n  position: absolute;\n  border-radius: 21px;\n  box-shadow: 0 18px 32px #0004;\n}\n.mgd-kit-catalog__hero-icon--0 { left: 4%; top: 55px; transform: rotate(-13deg); }\n.mgd-kit-catalog__hero-icon--1 { left: 37%; top: 20px; transform: rotate(8deg); z-index: 1; }\n.mgd-kit-catalog__hero-icon--2 { left: 67%; top: 76px; transform: rotate(17deg); }\n.mgd-kit-catalog__art-caption {\n  position: absolute;\n  bottom: 0;\n  left: 0;\n  right: 0;\n  text-align: center;\n  color: #c7d4cb;\n  font-size: 9px;\n  letter-spacing: 0.16em;\n  font-weight: 600;\n}\n/* Leiste mit Anzahl */\n.mgd-kit-catalog__collection-bar {\n  display: flex;\n  align-items: center;\n  justify-content: space-between;\n  padding: 19px 2px 25px;\n  gap: 16px;\n}\n.mgd-kit-catalog .mgd-kit-catalog__collection-bar p {\n  display: flex;\n  flex-wrap: wrap;\n  gap: 20px;\n  margin: 0;\n  color: var(--muted);\n  font-size: 12px;\n}\n.mgd-kit-catalog__collection-bar strong {\n  color: var(--ink);\n}\n.mgd-kit-catalog .mgd-kit-catalog__notice {\n  border-left: 4px solid var(--accent);\n  background: #fff;\n  padding: 14px 18px;\n  border-radius: 4px;\n  color: var(--ink);\n}\n/* Suche + Filter */\n.mgd-kit-catalog__toolbar {\n  display: flex;\n  flex-wrap: wrap;\n  gap: 12px;\n  align-items: center;\n  background: #fff;\n  padding: 14px;\n  border: 1px solid var(--line);\n  border-radius: 12px;\n}\n.mgd-kit-catalog__search {\n  flex: 1 1 260px;\n  position: relative;\n  min-width: 0;\n}\n.mgd-kit-catalog__search label {\n  position: absolute;\n  left: 15px;\n  top: 7px;\n  font-size: 25px;\n  line-height: 1.3;\n  color: #566a62;\n}\n.mgd-kit-catalog .mgd-kit-catalog__search input {\n  width: 100%;\n  max-width: none;\n  height: 46px;\n  min-height: 46px;\n  border: 1px solid #8a9a93;\n  border-radius: 7px;\n  background: var(--paper);\n  padding: 0 14px 0 44px;\n  font-size: 14px;\n  color: var(--ink);\n}\n.mgd-kit-catalog .mgd-kit-catalog__select {\n  display: flex;\n  align-items: center;\n  gap: 9px;\n  min-height: 46px;\n  font-size: 12px;\n  color: var(--muted);\n  white-space: nowrap;\n}\n.mgd-kit-catalog .mgd-kit-catalog__select select {\n  border: 1px solid #8a9a93;\n  border-radius: 7px;\n  height: 46px;\n  min-height: 46px;\n  max-width: 190px;\n  padding-left: 12px;\n  font-size: 12px;\n  color: var(--ink);\n  background-color: #fff;\n}\n.mgd-kit-catalog__browse-heading {\n  display: flex;\n  align-items: center;\n  justify-content: space-between;\n  gap: 12px;\n  margin: 22px 0 18px;\n}\n.mgd-kit-catalog__categories {\n  display: flex;\n  gap: 6px;\n  flex-wrap: wrap;\n}\n.mgd-kit-catalog .mgd-kit-catalog__categories button {\n  display: inline-flex;\n  align-items: center;\n  background: none;\n  border: 1px solid transparent;\n  border-radius: 6px;\n  font-size: 12px;\n  min-height: 44px;\n  min-width: 44px;\n  padding: 8px 12px;\n  cursor: pointer;\n  color: var(--muted);\n  font-weight: 600;\n}\n.mgd-kit-catalog .mgd-kit-catalog__categories button[aria-pressed=\"true\"] {\n  background: var(--ink);\n  color: #fff;\n}\n.mgd-kit-catalog .mgd-kit-catalog__categories button:hover {\n  border-color: var(--ink);\n}\n.mgd-kit-catalog .mgd-kit-catalog__count {\n  white-space: nowrap;\n  font-size: 12px;\n  color: var(--muted);\n  margin: 0;\n}\n/* Karten Grid - passt sich der Breite an, 390px ohne Querscrollen */\n.mgd-kit-catalog__grid {\n  display: grid;\n  grid-template-columns: repeat(auto-fill, minmax(min(100%, 280px), 1fr));\n  gap: 20px;\n}\n.mgd-kit-catalog__card {\n  --mgd-kit-cat-accent: #7c9a88;\n  --wash: color-mix(in srgb, var(--mgd-kit-cat-accent) 22%, #fff);\n  display: flex;\n  flex-direction: column;\n  min-width: 0;\n  background: #fff;\n  border: 1px solid #dce2dc;\n  border-radius: 13px;\n  overflow: hidden;\n  box-shadow: 0 3px 9px #192c2410;\n  transition: box-shadow 0.18s, border-color 0.18s;\n}\n.mgd-kit-catalog__card:hover {\n  box-shadow: 0 10px 24px #192c2415;\n  border-color: #a3b3a7;\n}\n/* ohne Bild nur Farbfläche aus accent */\n.mgd-kit-catalog__card-art {\n  position: relative;\n  background: #e4ece5;\n  background: linear-gradient(135deg, var(--wash), color-mix(in srgb, var(--mgd-kit-cat-accent) 45%, #fff));\n  height: 149px;\n  display: flex;\n  align-items: center;\n  justify-content: center;\n  overflow: hidden;\n}\n.mgd-kit-catalog__card-background {\n  position: absolute;\n  inset: 0;\n  width: 100%;\n  height: 100%;\n  object-fit: cover;\n  object-position: center;\n}\n.mgd-kit-catalog__card-icon {\n  position: relative;\n  z-index: 1;\n  border-radius: 17px;\n  box-shadow: 0 0 0 4px #ffffffb3, 0 10px 24px #14251e40;\n}\n/* deckende Schilder, Text bleibt auf jedem Bild lesbar */\n.mgd-kit-catalog__category,\n.mgd-kit-catalog__version {\n  z-index: 2;\n  background: #fff;\n  border: 1px solid #e5e8e3;\n  border-radius: 5px;\n  padding: 4px 7px;\n  line-height: 1.4;\n  color: var(--ink);\n}\n.mgd-kit-catalog__category {\n  position: absolute;\n  top: 13px;\n  left: 17px;\n  font-size: 10px;\n  font-weight: 700;\n  letter-spacing: 0.07em;\n  text-transform: uppercase;\n}\n.mgd-kit-catalog__version {\n  position: absolute;\n  bottom: 9px;\n  right: 13px;\n  font-size: 10px;\n}\n.mgd-kit-catalog__card-body {\n  padding: 21px;\n  display: flex;\n  flex-direction: column;\n  flex: 1;\n}\n.mgd-kit-catalog__card-heading {\n  display: flex;\n  flex-direction: column;\n  align-items: flex-start;\n  gap: 5px;\n}\n.mgd-kit-catalog .mgd-kit-catalog__card h4 {\n  font-size: 20px;\n  letter-spacing: -0.45px;\n  line-height: 1.25;\n  color: var(--ink);\n  margin: 0;\n}\n.mgd-kit-catalog__price {\n  color: var(--muted);\n  font-size: 11px;\n}\n.mgd-kit-catalog .mgd-kit-catalog__description {\n  color: var(--muted);\n  font-size: 13px;\n  line-height: 1.65;\n  margin: 13px 0 16px;\n  min-height: 64px;\n}\n/* Häkchen Punkte */\n.mgd-kit-catalog__features {\n  margin: 0 0 20px;\n  padding: 0;\n  list-style: none;\n  font-size: 12px;\n}\n.mgd-kit-catalog__features li {\n  padding-left: 19px;\n  position: relative;\n  margin: 5px 0;\n  line-height: 1.5;\n}\n.mgd-kit-catalog__features li::before {\n  content: \"✓\";\n  position: absolute;\n  left: 0;\n  font-weight: bold;\n  color: #2c5a45;\n}\n.mgd-kit-catalog__card-status {\n  display: flex;\n  flex-wrap: wrap;\n  gap: 8px;\n  justify-content: space-between;\n  align-items: center;\n  font-size: 11px;\n  color: var(--muted);\n  margin-top: auto;\n  margin-bottom: 16px;\n}\n.mgd-kit-catalog__status {\n  font-size: 11px;\n  font-weight: 600;\n  line-height: 1.5;\n  color: #3f5446;\n  background: #eef2ee;\n  padding: 4px 8px;\n  border-radius: 4px;\n}\n.mgd-kit-catalog__status--installed { background: #e7f1e8; color: #24543a; }\n.mgd-kit-catalog__status--update { background: #fff1cb; color: #5e4200; }\n.mgd-kit-catalog__actions {\n  display: flex;\n  flex-wrap: wrap;\n  align-items: center;\n  justify-content: space-between;\n  gap: 12px;\n  border-top: 1px solid #e5e9e4;\n  padding-top: 15px;\n}\n.mgd-kit-catalog__actions form {\n  margin: 0;\n}\n.mgd-kit-catalog .mgd-kit-catalog__details {\n  display: flex;\n  align-items: center;\n  gap: 10px;\n  color: var(--ink);\n  font-size: 12px;\n  font-weight: 600;\n  min-height: 44px;\n}\n.mgd-kit-catalog .mgd-kit-catalog__details:hover {\n  color: var(--accent);\n}\n/* Basic_BTN Ansehen / Mehr erfahren */\n.mgd-kit-catalog .mgd-kit-catalog__button {\n  display: inline-flex;\n  justify-content: center;\n  align-items: center;\n  gap: 15px;\n  min-height: 44px;\n  min-width: 44px;\n  padding: 9px 15px;\n  border: 1px solid var(--ink);\n  border-radius: 6px;\n  background: var(--ink);\n  color: #fff !important;\n  font-size: 12px;\n  font-weight: 600;\n  line-height: 1.5;\n  cursor: pointer;\n  max-width: 100%;\n  text-align: center;\n}\n.mgd-kit-catalog .mgd-kit-catalog__button:hover {\n  background: #29473d;\n}\n.mgd-kit-catalog .mgd-kit-catalog__button--quiet {\n  color: var(--ink) !important;\n  background: #f5f7f3;\n  border-color: #b9c6b9;\n}\n.mgd-kit-catalog .mgd-kit-catalog__button--quiet:hover {\n  background: #e7ede7;\n}\n.mgd-kit-catalog .mgd-kit-catalog__button:disabled {\n  opacity: 0.65;\n  cursor: wait;\n}\n.mgd-kit-catalog__unavailable {\n  font-size: 12px;\n  color: var(--muted);\n}\n/* Leerzustand */\n.mgd-kit-catalog__empty {\n  text-align: center;\n  padding: 60px 20px;\n  background: #fff;\n  border: 1px dashed #a7b7a8;\n  border-radius: 12px;\n}\n.mgd-kit-catalog__empty \u003E span {\n  font-size: 40px;\n}\n.mgd-kit-catalog .mgd-kit-catalog__empty h4 {\n  font-size: 22px;\n  color: var(--ink);\n  margin: 8px 0;\n}\n.mgd-kit-catalog .mgd-kit-catalog__empty p {\n  color: var(--muted);\n}\n.mgd-kit-catalog__footer {\n  display: flex;\n  justify-content: space-between;\n  gap: 24px;\n  border-top: 1px solid var(--line);\n  margin-top: 30px;\n  padding: 18px 2px 4px;\n  font-size: 12px;\n  color: var(--muted);\n}\n.mgd-kit-catalog .mgd-kit-catalog__footer p {\n  margin: 0;\n  color: var(--muted);\n  font-size: 12px;\n}\n.mgd-kit-catalog .mgd-kit-catalog__footer p:first-child {\n  display: flex;\n  flex-direction: column;\n}\n.mgd-kit-catalog__footer strong {\n  color: var(--ink);\n}\n.mgd-kit-catalog .mgd-kit-catalog__footer p:last-child {\n  text-align: right;\n}\n/* WTF Modal Effekt - Details Dialog */\n.mgd-kit-catalog__dialog {\n  border: 0;\n  border-radius: 16px;\n  padding: 30px;\n  width: min(560px, calc(100vw - 32px));\n  max-height: 85vh;\n  overflow: auto;\n  color: var(--ink);\n  background: #fff;\n  box-shadow: 0 30px 100px #0004;\n}\n.mgd-kit-catalog__dialog::backdrop {\n  background: #0d1e23a8;\n}\n.mgd-kit-catalog__dialog-header {\n  display: flex;\n  align-items: center;\n  justify-content: space-between;\n}\n.mgd-kit-catalog .mgd-kit-catalog__dialog-header p {\n  font-size: 11px;\n  letter-spacing: 0.14em;\n  color: var(--muted);\n}\n.mgd-kit-catalog .mgd-kit-catalog__dialog-header button {\n  width: 44px;\n  height: 44px;\n  justify-content: center;\n  background: #eef2ed;\n  border: 0;\n  border-radius: 50%;\n  font-size: 28px;\n  line-height: 1;\n  cursor: pointer;\n  color: var(--ink);\n}\n.mgd-kit-catalog .mgd-kit-catalog__dialog h2 {\n  font-size: 28px;\n  line-height: 1.2;\n  margin: 15px 0;\n  color: var(--ink);\n}\n.mgd-kit-catalog__dialog li {\n  margin-bottom: 9px;\n}\n.mgd-kit-catalog__dialog ul {\n  padding-left: 20px;\n  list-style: disc;\n}\n.mgd-kit-catalog__dialog .mgd-kit-catalog__button {\n  margin-top: 12px;\n}\n/* schmal (Container, nicht Fenster - steckt ja im Dashboard) */\n@container (max-width: 900px) {\n  .mgd-kit-catalog .mgd-kit-catalog__hero { padding: 28px; }\n  .mgd-kit-catalog__hero-icon { width: 78px; height: 78px; }\n  .mgd-kit-catalog__search { flex-basis: 100%; }\n  .mgd-kit-catalog .mgd-kit-catalog__select { flex: 1 1 160px; }\n  .mgd-kit-catalog .mgd-kit-catalog__select select { max-width: none; width: 100%; }\n}\n@container (max-width: 650px) {\n  .mgd-kit-catalog .mgd-kit-catalog__hero { padding: 22px; min-height: 0; border-radius: 13px; }\n  .mgd-kit-catalog .mgd-kit-catalog__hero h3 { font-size: 28px; letter-spacing: -0.8px; }\n  .mgd-kit-catalog__hero-art { display: none; }\n  .mgd-kit-catalog .mgd-kit-catalog__eyebrow { font-size: 10px; letter-spacing: 0.08em; }\n  .mgd-kit-catalog__collection-bar { align-items: flex-start; flex-direction: column; padding-bottom: 16px; gap: 3px; }\n  .mgd-kit-catalog .mgd-kit-catalog__collection-bar p { gap: 10px; }\n  .mgd-kit-catalog .mgd-kit-catalog__select { min-width: 100%; }\n  .mgd-kit-catalog .mgd-kit-catalog__select select { flex: 1; }\n  .mgd-kit-catalog__browse-heading { align-items: flex-start; flex-direction: column; }\n  .mgd-kit-catalog__categories { gap: 3px; }\n  .mgd-kit-catalog .mgd-kit-catalog__categories button { padding: 8px; }\n  .mgd-kit-catalog__card-body { padding: 16px; }\n  .mgd-kit-catalog__footer { flex-direction: column; gap: 12px; }\n  .mgd-kit-catalog .mgd-kit-catalog__footer p:last-child { text-align: left; }\n  .mgd-kit-catalog .mgd-kit-catalog__description { min-height: 0; }\n  .mgd-kit-catalog__dialog { padding: 20px; }\n}\n@media (prefers-reduced-motion: reduce) {\n  .mgd-kit-catalog__card { transition: none; }\n}\n/* Kontrast-Modus Windows */\n@media (forced-colors: active) {\n  .mgd-kit-catalog__card-background { display: none; }\n  .mgd-kit-catalog .mgd-kit-catalog__button,\n  .mgd-kit-catalog .mgd-kit-catalog__categories button { border: 1px solid ButtonText; }\n  .mgd-kit-catalog .mgd-kit-catalog__categories button[aria-pressed=\"true\"] { border: 3px solid Highlight; }\n  .mgd-kit-catalog .mgd-kit-catalog__hero,\n  .mgd-kit-catalog__card-art,\n  .mgd-kit-catalog__card { border: 1px solid CanvasText; }\n  .mgd-kit-catalog a:focus-visible,\n  .mgd-kit-catalog button:focus-visible,\n  .mgd-kit-catalog input:focus-visible,\n  .mgd-kit-catalog select:focus-visible { outline-color: Highlight !important; }\n}\n\n/* Tag-Chips (mehrere wählbar) */\n.mgd-kit-catalog__tags{display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin:0 0 14px}\n.mgd-kit-catalog__tags-title{font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;opacity:.75;margin-right:4px}\n.mgd-kit-catalog__tags button{min-height:30px;padding:0 12px;border:1px solid currentColor;border-radius:999px;background:transparent;color:inherit;font:inherit;font-size:13px;cursor:pointer;opacity:.85}\n.mgd-kit-catalog__tags button[aria-pressed=\"true\"]{background:var(--mgd-kit-cat-accent,#1e2c2c);border-color:var(--mgd-kit-cat-accent,#1e2c2c);color:#fff;opacity:1}\n.mgd-kit-catalog__tags button:focus-visible{outline:3px solid #2271b1;outline-offset:2px}\n.mgd-kit-catalog__tags .mgd-kit-catalog__tags-reset{border-style:dashed}\n";
    // Texte mit eigenem Schlüssel, Wortlaut setzt die Plattform nach Freigabe ein
    var TEXTS = {
        finishMissing: 'Die neuen Dateien sind eingespielt. Bitte klicke unter Erweiterungen bei diesem Plugin auf Aktualisieren.'
    };

    var STYLE = [
        '.mgd-kit-sw p{margin:0 0 10px;line-height:1.5}',
        '.mgd-kit-sw .mgd-kit-note{padding:12px;border-left:4px solid #b45309;background:#fffbeb;color:#1f2d3d}',
        '.mgd-kit-sw .mgd-kit-error{padding:12px;border-left:4px solid #b91c1c;background:#fef2f2;color:#1f2d3d}',
        '.mgd-kit-sw .mgd-kit-ok{padding:12px;border-left:4px solid #15803d;background:#f0fdf4;color:#1f2d3d}',
        '.mgd-kit-sw ol{margin:0 0 10px 20px}',
        '.mgd-kit-sw .mgd-kit-small{font-size:13px;color:#3d4c5c}',
        '.mgd-kit-sw label{display:block;margin:0 0 6px;font-weight:600}',
        '.mgd-kit-sw input[type=password]{width:100%;max-width:420px;min-height:44px;padding:8px;border:1px solid #6b7c8d;border-radius:4px}',
        // Vue 2 kann kein template mit key > Wrapper-span trägt die Hero-Position
        '.mgd-kit-sw span.mgd-kit-catalog__hero-icon{display:block;width:94px;height:94px;overflow:hidden}',
        '.mgd-kit-sw span.mgd-kit-catalog__hero-icon>*{display:block;width:100%;height:100%}',
        '.mgd-kit-sw a:focus-visible,.mgd-kit-sw input:focus-visible{outline:3px solid #1d4ed8;outline-offset:2px}'
    ].join('');

    var TEMPLATE = [
        '<sw-page class="mgd-kit-sw">',
        '  <template #smart-bar-header><h2>{{ title }}</h2></template>',
        '  <template #content>',
        '    <sw-card-view>',
        '      <p v-if="isLoading" role="status">Wird geladen …</p>',
        '      <p v-else-if="loadError" class="mgd-kit-error" role="alert">Die Seite konnte gerade nicht geladen werden. Bitte lade sie neu.</p>',
        '      <template v-else>',
        '        <sw-card title="Info">',
        '          <p>Plugin: {{ status.name }}</p>',
        '          <p>Version: {{ updates.installed || "unbekannt" }}</p>',
        '        </sw-card>',
        '        <sw-card title="Updates">',
        '          <p v-if="!updates.available">Die Update-Prüfung hat gerade nicht geklappt. Versuche es später noch einmal.</p>',
        '          <p v-else-if="!updates.newer">Du hast die aktuelle Version.</p>',
        '          <template v-else>',
        '            <p>Version {{ updates.newest }} ist verfügbar.</p>',
        '            <p v-if="updates.changelog" class="mgd-kit-small">{{ updates.changelog }}</p>',
        '            <sw-button v-if="status.canUpdate && !fallback" variant="primary" :disabled="isUpdating" :isLoading="isUpdating" @click="runUpdate">Jetzt aktualisieren</sw-button>',
        '            <p v-if="!status.canUpdate && !fallback" class="mgd-kit-small">Den Knopf „Jetzt aktualisieren“ sehen nur Admins mit dem Recht, Erweiterungen hochzuladen und zu verwalten.</p>',
        '          </template>',
        '          <p v-if="updates.notice" class="mgd-kit-note">{{ updates.notice.text }} <a v-if="isLink(updates.notice.url)" :href="updates.notice.url" target="_blank" rel="noopener noreferrer">Zur aktuellen Version</a></p>',
        '          <p v-if="message" :class="messageKey === \'finishMissing\' ? \'mgd-kit-note\' : (messageOk ? \'mgd-kit-ok\' : \'mgd-kit-error\')" role="status">{{ message }}</p>',
        '          <template v-if="fallback || (updates.newer && !status.canUpdate)">',
        '            <p>So aktualisierst du von Hand:</p>',
        '            <ol><li v-for="(step, i) in steps" :key="i">{{ step }}</li></ol>',
        '            <p v-if="isLink(downloadLink)"><a :href="downloadLink" target="_blank" rel="noopener noreferrer">ZIP-Datei herunterladen</a></p>',
        '          </template>',
        '        </sw-card>',
        '        <sw-card title="Weitere Plugins">',
        '          <div class="mgd-kit-catalog">',
        '            <header class="mgd-kit-catalog__hero"><div class="mgd-kit-catalog__hero-copy"><p class="mgd-kit-catalog__eyebrow"><span class="mgd-kit-catalog__brand">MGD</span> SHOPWARE · PLUGIN-KOLLEKTION</p>',
        '              <h3>Mehr aus deinem<br><em>Shop machen.</em></h3><p>Durchdachte Erweiterungen von Michael Gahn DESIGN.<br>Entdecke die passenden Werkzeuge für dein Shopware.</p></div>',
        '              <div class="mgd-kit-catalog__hero-art" aria-hidden="true"><span class="mgd-kit-catalog__orbit"></span>',
        '                <span v-for="(item, i) in heroItems" :key="item.slug" :class="\'mgd-kit-catalog__hero-icon mgd-kit-catalog__hero-icon--\' + i"><img v-if="mediaSrc(item.icon)" :src="mediaSrc(item.icon)" width="94" height="94" alt=""><svg v-else viewBox="0 0 128 128" width="94" height="94" aria-hidden="true" focusable="false"><rect width="128" height="128" rx="27" fill="#263b37"/><g fill="none" stroke="#e5c58e" stroke-width="5"><rect x="28" y="28" width="27" height="27" rx="5"/><rect x="28" y="73" width="27" height="27" rx="5"/><rect x="73" y="73" width="27" height="27" rx="5"/></g><path d="M86 27v28M72 41h28" stroke="#e6eee7" stroke-width="5" stroke-linecap="round"/></svg></span>',
        '                <span class="mgd-kit-catalog__art-caption">GEMACHT FÜR DEIN SHOPWARE</span></div></header>',
        '            <div class="mgd-kit-catalog__collection-bar"><p><strong>{{ recommendations.length }} Erweiterungen</strong><span>Installation nur von Hand</span></p></div>',
        '            <p v-if="recStale" class="mgd-kit-catalog__stale">Die Liste konnte gerade nicht aktualisiert werden. Du siehst den letzten bekannten Stand.</p>',
        '            <section class="mgd-kit-catalog__browser" aria-label="Plugins entdecken">',
        '              <div v-if="recommendations.length" class="mgd-kit-catalog__toolbar">',
        '                <div class="mgd-kit-catalog__search"><label :for="uid + \'-search\'"><span aria-hidden="true">⌕</span><span class="mgd-kit-catalog__sr">Plugins durchsuchen</span></label><input :id="uid + \'-search\'" v-model="search" type="search" placeholder="Was möchtest du verbessern?" autocomplete="off"></div>',
        '                <label class="mgd-kit-catalog__select">Preis<select v-model="price"><option value="all">Alle Preise</option><option value="free">Kostenlos</option><option value="paid">Kostenpflichtig</option></select></label>',
        '              </div>',
        '              <div class="mgd-kit-catalog__browse-heading">',
        '                <div v-if="recommendations.length" class="mgd-kit-catalog__categories" role="group" aria-label="Kategorie filtern"><button type="button" :aria-pressed="String(category === \'all\')" @click="category = \'all\'">Alle entdecken</button><button v-for="c in categories" :key="c" type="button" :aria-pressed="String(category === c)" @click="category = c">{{ c }}</button></div>',
        '                <div v-if="allTags.length" class="mgd-kit-catalog__tags" role="group" aria-label="Nach Tag filtern"><span class="mgd-kit-catalog__tags-title">Tags</span><button v-for="t in allTags" :key="t" type="button" :aria-pressed="String(pickedTags.indexOf(t) >= 0)" @click="toggleTag(t)">{{ t }}</button><button v-if="pickedTags.length" type="button" class="mgd-kit-catalog__tags-reset" @click="pickedTags = []">Alle Tags zurücksetzen</button></div>',
        '                <p class="mgd-kit-catalog__count" aria-live="polite" aria-atomic="true">{{ countLabel }}</p>',
        '              </div>',
        '              <div class="mgd-kit-catalog__grid">',
        '                <article v-for="item in filtered" :key="item.slug" class="mgd-kit-catalog__card" :style="accentStyle(item)">',
        '                  <div class="mgd-kit-catalog__card-art">',
        '                    <img v-if="mediaSrc(item.image)" class="mgd-kit-catalog__card-background" :src="mediaSrc(item.image)" width="1080" height="360" :alt="item.imageAlt || \'\'" :aria-hidden="item.imageAlt ? null : \'true\'">',
        '                    <span class="mgd-kit-catalog__category">{{ item.category }}</span>',
        '                    <img v-if="mediaSrc(item.icon)" class="mgd-kit-catalog__card-icon" :src="mediaSrc(item.icon)" width="78" height="78" alt=""><svg v-else class="mgd-kit-catalog__card-icon" viewBox="0 0 128 128" width="78" height="78" aria-hidden="true" focusable="false"><rect width="128" height="128" rx="27" fill="#263b37"/><g fill="none" stroke="#e5c58e" stroke-width="5"><rect x="28" y="28" width="27" height="27" rx="5"/><rect x="28" y="73" width="27" height="27" rx="5"/><rect x="73" y="73" width="27" height="27" rx="5"/></g><path d="M86 27v28M72 41h28" stroke="#e6eee7" stroke-width="5" stroke-linecap="round"/></svg>',
        '                    <span v-if="item.version" class="mgd-kit-catalog__version">v{{ item.version }}</span>',
        '                  </div>',
        '                  <div class="mgd-kit-catalog__card-body">',
        '                    <div class="mgd-kit-catalog__card-heading"><h4>{{ item.name }}</h4><span class="mgd-kit-catalog__price">{{ item.tier === \'free\' ? \'Kostenlos\' : \'Kostenpflichtig\' }}</span></div>',
        '                    <p class="mgd-kit-catalog__description">{{ item.description }}</p>',
        '                    <ul v-if="features(item).length" class="mgd-kit-catalog__features"><li v-for="(f, j) in features(item)" :key="j">{{ f }}</li></ul>',
        '                    <div class="mgd-kit-catalog__card-status"><span :class="\'mgd-kit-catalog__status mgd-kit-catalog__status--\' + stateOf(item)">{{ statusLabel(item) }}</span></div>',
        '                    <div class="mgd-kit-catalog__actions">',
        '                      <a class="mgd-kit-catalog__details" :href="detailsUrl(item)" target="_blank" rel="noopener noreferrer" aria-haspopup="dialog" :aria-label="\'Details zu \' + item.name" @click="openDetails(item, $event)">Details <span aria-hidden="true">↗</span></a>',
        '                      <a class="mgd-kit-catalog__button" :href="detailsUrl(item)" target="_blank" rel="noopener noreferrer" :aria-label="item.name + \' ansehen (neuer Tab)\'">Ansehen</a>',
        '                    </div>',
        '                  </div>',
        '                </article>',
        '              </div>',
        '              <div v-if="filtered.length === 0" class="mgd-kit-catalog__empty"><span aria-hidden="true">⌕</span>',
        '                <h4>{{ recommendations.length ? "Noch nicht das Richtige dabei?" : "Hier ist gerade nichts gelistet." }}</h4>',
        '                <p>{{ recommendations.length ? "Versuche einen anderen Suchbegriff oder setze die Filter zurück." : "Bitte schau später noch einmal vorbei." }}</p>',
        '                <button v-if="recommendations.length" class="mgd-kit-catalog__button" type="button" @click="resetFilters">Filter zurücksetzen</button>',
        '              </div>',
        '            </section>',
        '            <footer class="mgd-kit-catalog__footer"><p><strong>Michael Gahn DESIGN</strong><span>Werkzeuge mit einem klaren Zweck.</span></p>',
        '              <p>{{ recCentral ? "Katalog von theme.michael-gahn.de" : "Ersatzkatalog aktiv · Zentraler Feed derzeit nicht erreichbar" }}<template v-if="recChecked"><br><span>Geprüft: {{ recChecked }} · Zwischenspeicherung bis zu 15 Minuten</span></template></p></footer>',
        '            <dialog ref="dialog" class="mgd-kit-catalog__dialog" :aria-labelledby="uid + \'-dialog-title\'" @close="onDialogClose" @click="onDialogClick">',
        '              <div class="mgd-kit-catalog__dialog-header"><p>MGD PLUGIN-KOLLEKTION</p><button ref="dialogClose" type="button" aria-label="Details schließen" @click="closeDetails">×</button></div>',
        '              <h2 :id="uid + \'-dialog-title\'">{{ dialogItem ? dialogItem.name : "Plugin-Details" }}</h2>',
        '              <div v-if="dialogItem">',
        '                <p>{{ dialogItem.description }}</p>',
        '                <ul v-if="features(dialogItem).length"><li v-for="(f, j) in features(dialogItem)" :key="j">{{ f }}</li></ul>',
        '                <p>{{ dialogItem.tier === "free" ? "Kostenlos erhältlich." : "Kostenpflichtige Erweiterung. Informationen und Konditionen findest du beim Anbieter." }}</p>',
        '                <a class="mgd-kit-catalog__button" :href="detailsUrl(dialogItem)" target="_blank" rel="noopener noreferrer">Mehr erfahren <span class="mgd-kit-catalog__sr">(neuer Tab)</span></a>',
        '              </div>',
        '            </dialog>',
        '          </div>',
        '        </sw-card>',
        '        <sw-card v-if="status.install" title="Installation melden">',
        '          <sw-switch-field :label="status.install.label" :value="status.install.enabled" :disabled="isSaving" @update:value="setInstall" @change="setInstall"></sw-switch-field>',
        '          <p class="mgd-kit-small">{{ status.install.hint }}</p>',
        '          <p class="mgd-kit-small" role="status">{{ status.install.status }}</p>',
        '        </sw-card>',
        '        <sw-card v-if="status.license" title="Lizenz">',
        '          <label :for="licenseId">Lizenzschlüssel</label>',
        '          <input :id="licenseId" v-model="licenseToken" type="password" autocomplete="off" spellcheck="false">',
        '          <p v-if="status.license.hasToken" class="mgd-kit-small">Eingetragen ist ein Schlüssel, der auf {{ status.license.hint }} endet.</p>',
        '          <p role="status">{{ status.license.status }}</p>',
        '          <p v-if="licenseMessage" role="status">{{ licenseMessage }}</p>',
        '          <sw-button variant="primary" :disabled="isSaving" @click="saveLicense(\'save\')">Speichern</sw-button>',
        '          <sw-button v-if="status.license.hasToken" :disabled="isSaving" @click="saveLicense(\'remove\')">Entfernen</sw-button>',
        '          <p class="mgd-kit-small">Zur Prüfung gehen nur der Lizenzschlüssel und die Domain deines Shops an theme.michael-gahn.de.</p>',
        '        </sw-card>',
        '      </template>',
        '    </sw-card-view>',
        '  </template>',
        '</sw-page>'
    ].join('\n');

    function isLink(url) {
        return typeof url === 'string' && url.length < 500 && !/[\s"'<>\\]/.test(url) && LINK_PREFIXES.some(function (p) {
            return url.indexOf(p) === 0;
        });
    }

    function normalize(value) {
        return String(value || '').toLocaleLowerCase('de').normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/ß/g, 'ss');
    }

    // wie WP: d.m.Y · H:i (UTC)
    function formatDate(ts) {
        var d = new Date(ts * 1000);
        var p = function (n) { return (n < 10 ? '0' : '') + n; };

        return p(d.getUTCDate()) + '.' + p(d.getUTCMonth() + 1) + '.' + d.getUTCFullYear() + ' · ' + p(d.getUTCHours()) + ':' + p(d.getUTCMinutes());
    }

    // Kennung prüfen, Link immer selbst bauen
    function detailsUrl(item) {
        var slug = item && typeof item.slug === 'string' && /^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(item.slug) ? item.slug : '';

        return 'https://theme.michael-gahn.de/plugins/' + slug;
    }

    // nur eigene Dateinamen aus dem Cache, nie URLs
    function mediaName(name) {
        return typeof name === 'string' && FILE_RE.test(name) ? name : '';
    }

    function isDataUri(v) {
        return typeof v === 'string' && v.length < 1000000 && DATA_RE.test(v);
    }

    function addStyle(doc) {
        if (!doc || doc.getElementById(STYLE_ID)) {
            return;
        }
        var el = doc.createElement('style');
        el.id = STYLE_ID;
        el.textContent = STYLE + (CATALOG_CSS.indexOf('{{') === 0 ? '' : CATALOG_CSS);
        doc.head.appendChild(el);
    }

    var component = {
        template: TEMPLATE,
        inject: ['loginService', 'acl'],
        data: function () {
            return {
                isLoading: true, loadError: false, isUpdating: false, isSaving: false,
                status: {}, recommendations: [], recStale: false, recCentral: true, recChecked: '',
                media: {}, search: '', price: 'all', category: 'all', pickedTags: [], dialogItem: null, trigger: null,
                message: '', messageKey: '', messageOk: false, fallback: null,
                licenseToken: '', licenseMessage: ''
            };
        },
        computed: {
            title: function () {
                return 'MGD AI Kennzeichnung';
            },
            updates: function () {
                return this.status.updates || {};
            },
            steps: function () {
                var s = this.fallback && Array.isArray(this.fallback.steps) ? this.fallback.steps : this.status.fallbackSteps;

                return Array.isArray(s) ? s : [];
            },
            downloadLink: function () {
                return this.fallback && this.fallback.url ? this.fallback.url : this.updates.downloadUrl;
            },
            licenseId: function () {
                return MODULE + '-license';
            },
            uid: function () {
                return MODULE + '-cat';
            },
            heroItems: function () {
                return this.recommendations.slice(0, 3);
            },
            allTags: function () {
                var out = [];
                (this.recommendations || []).forEach(function (it) {
                    (Array.isArray(it.tags) ? it.tags : []).forEach(function (t) {
                        if (typeof t === 'string' && t && out.indexOf(t) < 0) {
                            out.push(t);
                        }
                    });
                });

                return out;
            },
            categories: function () {
                var out = [];
                this.recommendations.forEach(function (it) {
                    if (typeof it.category === 'string' && it.category && out.indexOf(it.category) < 0) {
                        out.push(it.category);
                    }
                });

                return out;
            },
            // Suche wie WP: alle Wörter, ohne Akzente/ß
            filtered: function () {
                var vm = this;
                var terms = normalize(vm.search).trim().split(/\s+/).filter(Boolean);

                return vm.recommendations.filter(function (it) {
                    // Suche findet auch Tags
                    var tags = Array.isArray(it.tags) ? it.tags : [];
                    var hay = normalize([it.name, it.description, it.category].concat(vm.features(it), tags).join(' '));
                    var tagOk = vm.pickedTags.every(function (t) { return tags.indexOf(t) >= 0; });

                    return tagOk && (vm.category === 'all' || it.category === vm.category)
                        && (vm.price === 'all' || it.tier === vm.price)
                        && terms.every(function (t) { return hay.indexOf(t) >= 0; });
                });
            },
            countLabel: function () {
                var n = this.filtered.length;

                return n + ' ' + (n === 1 ? 'Plugin' : 'Plugins');
            }
        },
        created: function () {
            addStyle(root.document);
            this.load();
        },
        methods: {
            isLink: isLink,
            detailsUrl: detailsUrl,
            setRecommendations: function (d) {
                var items = Array.isArray(d.items) ? d.items : [];
                this.recommendations = items.filter(function (it) {
                    return it && typeof it.slug === 'string' && /^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(it.slug) && typeof it.name === 'string';
                });
                this.recStale = d.source === 'stale' || d.source === 'fallback';
                this.recCentral = d.source === 'fresh' || d.source === 'cache' || d.source === 'stale';
                this.recChecked = typeof d.checkedAt === 'number' && d.checkedAt > 0 ? formatDate(d.checkedAt) : '';
            },
            // Bilder einzeln nacheinander über die Admin-API, als data:-URI
            loadMedia: function () {
                var vm = this;
                var names = [];
                vm.recommendations.forEach(function (it) {
                    [it.icon, it.image].forEach(function (n) {
                        if (mediaName(n) && names.indexOf(n) < 0) {
                            names.push(n);
                        }
                    });
                });

                return names.reduce(function (p, n) {
                    return p.then(function () {
                        return vm.http().get(API + '/media/' + n, vm.opts()).then(function (r) {
                            var data = r && r.data ? r.data.data : '';
                            if (isDataUri(data)) {
                                var next = Object.assign({}, vm.media);
                                next[n] = data;
                                vm.media = next;
                            }
                        }).catch(function () {
                            // kein Bild > Farbfläche + Icon
                        });
                    });
                }, Promise.resolve());
            },
            mediaSrc: function (name) {
                var n = mediaName(name);

                return n && isDataUri(this.media[n]) ? this.media[n] : '';
            },
            features: function (item) {
                return item && Array.isArray(item.features) ? item.features.filter(function (f) { return typeof f === 'string'; }).slice(0, 3) : [];
            },
            accentStyle: function (item) {
                return item && typeof item.accent === 'string' && ACCENT_RE.test(item.accent) ? { '--mgd-kit-cat-accent': item.accent } : {};
            },
            stateOf: function (item) {
                return item && (item.state === 'installed' || item.state === 'update') ? item.state : 'available';
            },
            // aktiv/inaktiv nur wenn der Server es sicher weiß
            statusLabel: function (item) {
                if (item && item.active === true) {
                    return 'Aktiv';
                }

                return item && item.installed === true ? 'Installiert · inaktiv' : 'Für deinen Shop';
            },
            resetFilters: function () {
                this.search = '';
                this.price = 'all';
                this.category = 'all';
                this.pickedTags = [];
            },
            toggleTag: function (t) {
                var i = this.pickedTags.indexOf(t);
                this.pickedTags = i >= 0 ? this.pickedTags.filter(function (x) { return x !== t; }) : this.pickedTags.concat([t]);
            },
            openDetails: function (item, event) {
                var d = this.$refs ? this.$refs.dialog : null;
                if (!d || typeof d.showModal !== 'function') {
                    return;
                }
                if (event) {
                    event.preventDefault();
                    this.trigger = event.currentTarget || null;
                }
                this.dialogItem = item;
                d.showModal();
                if (this.$refs.dialogClose) {
                    this.$refs.dialogClose.focus();
                }
            },
            closeDetails: function () {
                if (this.$refs && this.$refs.dialog) {
                    this.$refs.dialog.close();
                }
            },
            onDialogClick: function (event) {
                if (event && this.$refs && event.target === this.$refs.dialog) {
                    this.closeDetails();
                }
            },
            onDialogClose: function () {
                if (this.trigger && typeof this.trigger.focus === 'function') {
                    this.trigger.focus();
                }
            },
            http: function () {
                return Shopware.Application.getContainer('init').httpClient;
            },
            opts: function () {
                return { headers: { Authorization: 'Bearer ' + this.loginService.getToken(), 'Content-Type': 'application/json' } };
            },
            load: function () {
                var vm = this;
                vm.isLoading = true;

                return vm.http().get(API + '/status', vm.opts()).then(function (r) {
                    vm.status = r && r.data ? r.data : {};
                    vm.loadError = false;

                    return vm.http().get(API + '/recommendations', vm.opts()).then(function (r2) {
                        vm.setRecommendations(r2 && r2.data ? r2.data : {});

                        return vm.loadMedia();
                    }).catch(function () {
                        vm.recommendations = [];
                        vm.recStale = true;
                        vm.recCentral = false;
                    });
                }).catch(function () {
                    vm.loadError = true;
                }).then(function () {
                    vm.isLoading = false;
                });
            },
            // 2 Schritte: einspielen, dann eigener Aufruf fürs Shopware-Update (neuer Code geladen)
            runUpdate: function () {
                var vm = this;
                if (vm.isUpdating || !PLUGIN_PRIVS.every(function (p) { return vm.acl.can(p); })) {
                    return null;
                }
                vm.isUpdating = true;
                vm.message = '';
                vm.fallback = null;

                return vm.http().post(API + '/update', {}, vm.opts()).then(function (r) {
                    var d = r && r.data ? r.data : {};
                    if (d.ok === true && d.step === 'uploaded') {
                        vm.show(d);

                        return vm.http().post(API + '/update/finish', {}, vm.opts()).then(function (r2) {
                            vm.show(r2 && r2.data ? r2.data : {});
                        }).catch(function (e) {
                            // 404 = Schritt 2 nicht erreichbar, Dateien sind aber drin > Hinweis statt Fehler
                            if (e && e.response && e.response.status === 404) {
                                vm.showKey('finishMissing');

                                return null;
                            }
                            throw e;
                        });
                    }
                    vm.show(d);

                    return null;
                }).catch(function (e) {
                    vm.show(e && e.response && e.response.data ? e.response.data : {});
                }).then(function () {
                    vm.isUpdating = false;
                });
            },
            showKey: function (key) {
                this.messageKey = key;
                this.messageOk = false;
                this.message = TEXTS[key] || '';
                this.fallback = null;
            },
            show: function (d) {
                this.messageKey = '';
                this.messageOk = d.ok === true;
                this.message = typeof d.message === 'string' ? d.message : 'Das Update hat nicht geklappt.';
                this.fallback = d.ok === true ? null : (d.fallback || { url: this.updates.downloadUrl, steps: this.status.fallbackSteps });
            },
            // 6.5 schickt change, ab 6.6 update:value > doppelt abfangen
            setInstall: function (on) {
                var vm = this;
                if (typeof on !== 'boolean' || !vm.status.install || on === vm.status.install.enabled || vm.isSaving) {
                    return null;
                }
                vm.isSaving = true;

                return vm.http().post(API + '/install-report', { enabled: on }, vm.opts()).then(function (r) {
                    if (r && r.data && r.data.install) {
                        vm.status.install = r.data.install;
                    }
                }).catch(function () {
                    vm.status.install = Object.assign({}, vm.status.install, { status: 'Das Speichern hat nicht geklappt.' });
                }).then(function () {
                    vm.isSaving = false;
                });
            },
            saveLicense: function (action) {
                var vm = this;
                vm.isSaving = true;

                return vm.http().post(API + '/license', { action: action, token: action === 'save' ? vm.licenseToken : '' }, vm.opts()).then(function (r) {
                    var d = r && r.data ? r.data : {};
                    vm.licenseMessage = d.message || '';
                    if (d.license) {
                        vm.status.license = d.license;
                    }
                }).catch(function (e) {
                    var d = e && e.response && e.response.data ? e.response.data : {};
                    vm.licenseMessage = d.message || 'Das hat nicht geklappt.';
                }).then(function () {
                    vm.licenseToken = '';
                    vm.isSaving = false;
                });
            }
        }
    };

    // MGD Farbwähler (Ui/assets/mgd-color.js + .css), setzt der Baukasten beim Kopieren ein
    /* MGD Farbwähler - eine Komponente für alle Plattformen (WP, Shopware, JTL, Themes), ohne Fremdbibliothek
   Speicher per Adapter: MGDColor.setStore({load, save}), Standard localStorage. Texte per MGDColor.texts */
(function () {
  "use strict";
  if (window.MGDColor) return;

  const T = {
    pick: "Farbe wählen",
    dialog: "Farbwähler",
    area: "Sättigung und Helligkeit",
    areaValue: "Sättigung %s %, Helligkeit %v %",
    hue: "Farbton",
    hex: "HEX",
    copy: "Kopieren",
    copied: "Kopiert",
    quick: "Schnellfarben",
    recent: "Zuletzt benutzt",
    mine: "Meine Farben",
    save: "Farbe speichern",
    remove: "%c entfernen",
    empty: "Noch keine Farben gespeichert.",
    done: "Fertig",
    cancel: "Abbrechen",
    clear: "Leeren",
    invalid: "Bitte eine Farbe wie #1A2B3C eingeben.",
    none: "keine",
  };
  const QUICK = ["#000000", "#FFFFFF", "#1E2C2C", "#D6C49A", "#B73843", "#2271B1", "#00A32A", "#DBA617", "#8C5BD6", "#F0F0F1"];
  const MAX_MINE = 24;
  const MAX_RECENT = 8;
  const HEX_RE = /^#[0-9A-F]{6}$/;

  // Farbmathe
  const norm = (v) => {
    let s = String(v || "").trim().toUpperCase();
    if (!s.startsWith("#")) s = "#" + s;
    if (/^#[0-9A-F]{3}$/.test(s)) s = "#" + s[1] + s[1] + s[2] + s[2] + s[3] + s[3];
    return HEX_RE.test(s) ? s : null;
  };
  const toRgb = (h) => [1, 3, 5].map((i) => parseInt(h.slice(i, i + 2), 16));
  const toHex = (r, g, b) => "#" + [r, g, b].map((x) => Math.round(x).toString(16).padStart(2, "0")).join("").toUpperCase();
  const toHsv = (hex) => {
    const [r, g, b] = toRgb(hex).map((x) => x / 255);
    const max = Math.max(r, g, b), min = Math.min(r, g, b), d = max - min;
    let h = 0;
    if (d) h = max === r ? ((g - b) / d) % 6 : max === g ? (b - r) / d + 2 : (r - g) / d + 4;
    return { h: (h * 60 + 360) % 360, s: max ? d / max : 0, v: max };
  };
  const fromHsv = ({ h, s, v }) => {
    const f = (n) => {
      const k = (n + h / 60) % 6;
      return (v - v * s * Math.max(0, Math.min(k, 4 - k, 1))) * 255;
    };
    return toHex(f(5), f(3), f(1));
  };
  const clamp = (x, a, b) => Math.min(b, Math.max(a, x));
  const fmt = (t, map) => Object.keys(map).reduce((s, k) => s.split("%" + k).join(map[k]), t);
  const el = (tag, cls, attrs) => {
    const e = document.createElement(tag);
    if (cls) e.className = cls;
    Object.entries(attrs || {}).forEach(([k, v]) => e.setAttribute(k, v));
    return e;
  };
  const clean = (list, max) => (Array.isArray(list) ? list : []).map(norm).filter((c, i, a) => c && a.indexOf(c) === i).slice(0, max);

  // Speicher: Standard nur im Browser, Plattform-Adapter überschreibt
  let store = {
    load: async () => {
      try { return JSON.parse(localStorage.getItem("mgd-color") || "{}"); } catch (e) { return {}; }
    },
    save: async (d) => {
      try { localStorage.setItem("mgd-color", JSON.stringify(d)); } catch (e) { /* privat-Modus */ }
    },
  };
  let data = null;
  const loadData = async () => {
    if (data) return data;
    let d = {};
    try { d = (await store.load()) || {}; } catch (e) { d = {}; }
    data = { mine: clean(d.mine, MAX_MINE), recent: clean(d.recent, MAX_RECENT) };
    return data;
  };
  const saveData = () => { try { store.save({ mine: data.mine, recent: data.recent }); } catch (e) { /* egal */ } };

  // ein Popover für alle Felder
  let pop = null, ui = {}, cur = null, hsv = { h: 0, s: 0, v: 0 }, before = "", trigger = null;

  const build = () => {
    pop = el("div", "mgd-color", { role: "dialog", "aria-label": T.dialog, hidden: "" });
    ui.area = el("div", "mgd-color__area", { role: "slider", tabindex: "0", "aria-label": T.area, "aria-valuemin": "0", "aria-valuemax": "100" });
    ui.thumb = el("span", "mgd-color__thumb", { "aria-hidden": "true" });
    ui.area.append(ui.thumb);
    ui.hue = el("input", "mgd-color__hue", { type: "range", min: "0", max: "360", step: "1", "aria-label": T.hue });
    const row = el("div", "mgd-color__row");
    ui.preview = el("span", "mgd-color__preview", { "aria-hidden": "true" });
    const hexId = "mgd-color-hex-" + Math.random().toString(36).slice(2, 8);
    const lab = el("label", "mgd-color__label", { for: hexId });
    lab.textContent = T.hex;
    ui.hex = el("input", "mgd-color__hex", { id: hexId, type: "text", maxlength: "7", spellcheck: "false", autocomplete: "off", inputmode: "text" });
    ui.copy = el("button", "mgd-color__btn", { type: "button" });
    ui.copy.textContent = T.copy;
    row.append(ui.preview, lab, ui.hex, ui.copy);
    ui.msg = el("p", "mgd-color__msg", { role: "status", "aria-live": "polite" });
    ui.quick = group(T.quick);
    ui.recent = group(T.recent);
    ui.mine = group(T.mine);
    ui.save = el("button", "mgd-color__btn mgd-color__save", { type: "button" });
    ui.save.textContent = T.save;
    ui.mine.box.after(ui.save);
    const foot = el("div", "mgd-color__foot");
    // Leeren = Feld ohne Farbe (Plugin nimmt dann seinen Standard, Befund login)
    ui.clear = el("button", "mgd-color__btn mgd-color__clear", { type: "button" });
    ui.clear.textContent = T.clear;
    ui.cancel = el("button", "mgd-color__btn", { type: "button" });
    ui.cancel.textContent = T.cancel;
    ui.done = el("button", "mgd-color__btn mgd-color__btn--primary", { type: "button" });
    ui.done.textContent = T.done;
    foot.append(ui.clear, ui.cancel, ui.done);
    pop.append(ui.area, ui.hue, row, ui.msg, ui.quick.wrap, ui.recent.wrap, ui.mine.wrap, foot);
    document.body.append(pop);
    events();
  };

  function group(title) {
    const wrap = el("div", "mgd-color__group");
    const h = el("p", "mgd-color__title");
    h.textContent = title;
    const box = el("div", "mgd-color__swatches", { role: "list" });
    wrap.append(h, box);
    return { wrap, box };
  }

  const swatches = (g, list, removable) => {
    g.box.replaceChildren();
    if (!list.length && removable) {
      const p = el("p", "mgd-color__empty");
      p.textContent = T.empty;
      g.box.append(p);
    }
    list.forEach((c) => {
      const item = el("span", "mgd-color__item", { role: "listitem" });
      const b = el("button", "mgd-color__chip", { type: "button", "aria-label": c, title: c, "data-c": c });
      b.style.background = c;
      item.append(b);
      if (removable) {
        const x = el("button", "mgd-color__del", { type: "button", "aria-label": fmt(T.remove, { c }), "data-del": c });
        x.textContent = "×";
        item.append(x);
      }
      g.box.append(item);
    });
    if (!removable && !list.length) g.wrap.hidden = true; else g.wrap.hidden = false;
  };

  // quiet = nur anzeigen, nix ins Feld schreiben (Öffnen eines leeren Felds)
  const render = (from, quiet) => {
    const hex = fromHsv(hsv);
    ui.area.style.setProperty("--mgd-hue", "hsl(" + hsv.h + " 100% 50%)");
    ui.thumb.style.left = hsv.s * 100 + "%";
    ui.thumb.style.top = (1 - hsv.v) * 100 + "%";
    ui.thumb.style.background = hex;
    ui.area.setAttribute("aria-valuenow", String(Math.round(hsv.s * 100)));
    ui.area.setAttribute("aria-valuetext", fmt(T.areaValue, { s: Math.round(hsv.s * 100), v: Math.round(hsv.v * 100) }));
    if (from !== "hue") ui.hue.value = String(Math.round(hsv.h));
    if (from !== "hex") ui.hex.value = hex;
    ui.preview.style.background = hex;
    if (!quiet) apply(hex);
  };

  // Knopf einfärben, leer = Schachbrett
  function paint(btn, hex) {
    btn.classList.toggle("mgd-color__swatch--empty", !hex);
    btn.style.background = hex || "";
    btn.setAttribute("aria-label", T.pick + ": " + (hex || T.none));
  }

  const apply = (hex) => {
    if (!cur) return;
    cur.input.value = hex;
    paint(cur.btn, hex);
    cur.input.dispatchEvent(new Event("input", { bubbles: true }));
  };

  const setHex = (hex, from) => {
    const n = norm(hex);
    if (!n) return false;
    const keepHue = hsv.h;
    hsv = toHsv(n);
    if (hsv.s === 0) hsv.h = keepHue;
    render(from);
    return true;
  };

  const areaPick = (e) => {
    const r = ui.area.getBoundingClientRect();
    hsv.s = clamp((e.clientX - r.left) / r.width, 0, 1);
    hsv.v = 1 - clamp((e.clientY - r.top) / r.height, 0, 1);
    render();
  };

  function events() {
    ui.area.addEventListener("pointerdown", (e) => {
      ui.area.setPointerCapture(e.pointerId);
      areaPick(e);
      ui.area.focus();
    });
    ui.area.addEventListener("pointermove", (e) => { if (ui.area.hasPointerCapture(e.pointerId)) areaPick(e); });
    ui.area.addEventListener("keydown", (e) => {
      const step = e.shiftKey ? 0.1 : 0.01;
      const k = { ArrowLeft: ["s", -step], ArrowRight: ["s", step], ArrowUp: ["v", step], ArrowDown: ["v", -step] }[e.key];
      if (!k) return;
      e.preventDefault();
      hsv[k[0]] = clamp(hsv[k[0]] + k[1], 0, 1);
      render();
    });
    ui.hue.addEventListener("input", () => { hsv.h = Number(ui.hue.value); render("hue"); });
    ui.hex.addEventListener("input", () => {
      const ok = norm(ui.hex.value);
      ui.msg.textContent = ok || ui.hex.value.length < 4 ? "" : T.invalid;
      if (ok) setHex(ok, "hex");
    });
    ui.copy.addEventListener("click", async () => {
      const v = fromHsv(hsv);
      try { await navigator.clipboard.writeText(v); } catch (e) { ui.hex.select(); document.execCommand && document.execCommand("copy"); }
      ui.msg.textContent = T.copied + ": " + v;
    });
    pop.addEventListener("click", (e) => {
      const del = e.target.closest("[data-del]");
      if (del) {
        data.mine = data.mine.filter((c) => c !== del.dataset.del);
        saveData();
        swatches(ui.mine, data.mine, true);
        ui.save.focus();
        return;
      }
      const chip = e.target.closest("[data-c]");
      if (chip) setHex(chip.dataset.c);
    });
    ui.save.addEventListener("click", () => {
      const v = fromHsv(hsv);
      data.mine = clean([v, ...data.mine], MAX_MINE);
      saveData();
      swatches(ui.mine, data.mine, true);
    });
    ui.clear.addEventListener("click", () => { apply(""); before = ""; close(true); });
    ui.cancel.addEventListener("click", () => close(false));
    ui.done.addEventListener("click", () => close(true));
    pop.addEventListener("keydown", (e) => {
      if (e.key === "Escape") { e.preventDefault(); close(false); }
      if (e.key === "Tab") trap(e);
    });
    document.addEventListener("pointerdown", (e) => {
      if (!pop.hidden && !pop.contains(e.target) && (!cur || e.target !== cur.btn)) close(true);
    });
    window.addEventListener("resize", () => { if (!pop.hidden) place(); });
  }

  // Tab bleibt im Fenster
  function trap(e) {
    const f = [...pop.querySelectorAll("button, input, [tabindex='0']")].filter((x) => !x.closest("[hidden]"));
    if (!f.length) return;
    const first = f[0], last = f[f.length - 1];
    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
  }

  // Desktop neben dem Knopf, schmal = unten als Blatt
  const place = () => {
    pop.classList.toggle("mgd-color--sheet", window.innerWidth < 600);
    if (window.innerWidth < 600) { pop.style.left = pop.style.top = ""; return; }
    const r = cur.btn.getBoundingClientRect();
    const w = pop.offsetWidth, h = pop.offsetHeight;
    let left = r.left + window.scrollX, top = r.bottom + window.scrollY + 6;
    if (left + w > window.scrollX + document.documentElement.clientWidth - 8) left = window.scrollX + document.documentElement.clientWidth - w - 8;
    if (r.bottom + h + 6 > window.innerHeight && r.top - h - 6 > 0) top = r.top + window.scrollY - h - 6;
    // passt weder unten noch oben > so weit hoch, dass es ganz zu sehen ist
    else if (r.bottom + h + 6 > window.innerHeight) top = window.scrollY + Math.max(8, window.innerHeight - h - 8);
    pop.style.left = Math.max(8, left) + "px";
    pop.style.top = Math.max(8, top) + "px";
  };

  const open = async (field) => {
    if (!pop) build();
    await loadData();
    cur = field;
    trigger = field.btn;
    before = norm(field.input.value) || "";
    hsv = toHsv(before || "#000000");
    ui.msg.textContent = "";
    swatches(ui.quick, QUICK, false);
    swatches(ui.recent, data.recent, false);
    swatches(ui.mine, data.mine, true);
    pop.hidden = false;
    field.btn.setAttribute("aria-expanded", "true");
    render(undefined, true);
    place();
    ui.area.focus();
  };

  const close = (keep) => {
    if (!cur || pop.hidden) return;
    if (!keep) apply(before);
    else {
      const v = norm(cur.input.value);
      if (v && v !== before) {
        data.recent = clean([v, ...data.recent], MAX_RECENT);
        saveData();
      }
    }
    cur.input.dispatchEvent(new Event("change", { bubbles: true }));
    cur.btn.setAttribute("aria-expanded", "false");
    pop.hidden = true;
    const t = trigger;
    cur = null;
    if (t) t.focus();
  };

  // Feld aufrüsten: input[type=color] / .wp-color-picker / [data-mgd-color] > Textfeld + Farbknopf, name + Wert bleiben
  const attach = (input) => {
    if (!input || input.dataset.mgdColorReady) return;
    input.dataset.mgdColorReady = "1";
    // leer bleibt leer (Befund login): type=color meldet sonst immer #000000 > Attribut lesen
    const raw = input.type === "color" ? input.getAttribute("value") || "" : input.value;
    const start = norm(raw) || "";
    if (input.type === "color") input.type = "text";
    input.value = start;
    input.classList.add("mgd-color__input");
    input.setAttribute("maxlength", "7");
    input.setAttribute("spellcheck", "false");
    const wrap = el("span", "mgd-color__field");
    input.before(wrap);
    const btn = el("button", "mgd-color__swatch", { type: "button", "aria-haspopup": "dialog", "aria-expanded": "false" });
    paint(btn, start);
    wrap.append(btn, input);
    const field = { input, btn };
    btn.addEventListener("click", () => (cur === field && !pop.hidden ? close(true) : open(field)));
    input.addEventListener("input", () => {
      const v = norm(input.value);
      if (v || input.value === "") paint(btn, v || "");
    });
    input.addEventListener("blur", () => { const v = norm(input.value); if (v) input.value = v; });
  };

  // Felder, die ein Framework verwaltet (Shopware-Admin): nix umbauen, nur den vorhandenen Knopf/Vorschau nutzen
  const bind = (input, trigger) => {
    if (!input || !trigger || trigger.dataset.mgdColorReady) return;
    trigger.dataset.mgdColorReady = "1";
    trigger.setAttribute("aria-haspopup", "dialog");
    const field = { input, btn: trigger };
    // Capture: das eigene Fenster vom Framework geht nicht mehr auf
    trigger.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopImmediatePropagation();
      if (cur === field && pop && !pop.hidden) close(true); else open(field);
    }, true);
  };

  // Plattform kann weitere Felder anmelden (JTL: input.colorpicker)
  let selectors = "input[type=color], input.wp-color-picker, input[data-mgd-color]";
  const init = (root) => (root || document).querySelectorAll(selectors).forEach(attach);

  window.MGDColor = {
    texts: T,
    attach,
    bind,
    init,
    addSelector: (sel) => { if (typeof sel === "string" && sel.trim() !== "") selectors += ", " + sel; },
    norm,
    setStore: (s) => { if (s && typeof s.load === "function" && typeof s.save === "function") { store = s; data = null; } },
    setTexts: (t) => Object.assign(T, t || {}),
  };
  init();
  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", () => init());
})();

    var COLOR_CSS = "/* MGD Farbwähler - Hell/Dunkel per prefers-color-scheme, ohne externe Schriften */\n.mgd-color {\n  --mgd-c-bg: #ffffff;\n  --mgd-c-fg: #1d2327;\n  --mgd-c-muted: #50575e;\n  --mgd-c-line: #dcdcde;\n  --mgd-c-soft: #f6f7f7;\n  --mgd-c-accent: #1e2c2c;\n  --mgd-c-accent-fg: #ffffff;\n  --mgd-c-focus: #2271b1;\n  position: absolute;\n  z-index: 100100;\n  width: 296px;\n  max-height: calc(100vh - 16px);\n  overflow: auto;\n  box-sizing: border-box;\n  padding: 14px;\n  border: 1px solid var(--mgd-c-line);\n  border-radius: 14px;\n  background: var(--mgd-c-bg);\n  color: var(--mgd-c-fg);\n  box-shadow: 0 12px 32px rgba(0, 0, 0, .18), 0 2px 6px rgba(0, 0, 0, .08);\n  font: 13px/1.4 -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif;\n}\n@media (prefers-color-scheme: dark) {\n  .mgd-color {\n    --mgd-c-bg: #1e2124;\n    --mgd-c-fg: #f0f0f1;\n    --mgd-c-muted: #a7aaad;\n    --mgd-c-line: #3c434a;\n    --mgd-c-soft: #2c3338;\n    --mgd-c-accent: #d6c49a;\n    --mgd-c-accent-fg: #1e2c2c;\n    --mgd-c-focus: #72aee6;\n  }\n  .mgd-color__chip, .mgd-color__preview { box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .28); }\n}\n.mgd-color[hidden] { display: none; }\n.mgd-color *, .mgd-color *::before, .mgd-color *::after { box-sizing: border-box; }\n.mgd-color :focus-visible { outline: 3px solid var(--mgd-c-focus); outline-offset: 2px; }\n\n/* Farbfläche */\n.mgd-color__area {\n  position: relative;\n  height: 160px;\n  border-radius: 10px;\n  background: linear-gradient(to top, #000, transparent), linear-gradient(to right, #fff, var(--mgd-hue, red));\n  cursor: crosshair;\n  touch-action: none;\n}\n.mgd-color__thumb {\n  position: absolute;\n  width: 20px;\n  height: 20px;\n  margin: -10px 0 0 -10px;\n  border: 3px solid #fff;\n  border-radius: 50%;\n  box-shadow: 0 0 0 1px rgba(0, 0, 0, .35), 0 2px 4px rgba(0, 0, 0, .3);\n  pointer-events: none;\n}\n\n/* Farbton-Regler */\n.mgd-color__hue {\n  -webkit-appearance: none;\n  appearance: none;\n  display: block;\n  width: 100%;\n  height: 14px;\n  margin: 14px 0 12px;\n  border-radius: 7px;\n  background: linear-gradient(to right, #f00, #ff0, #0f0, #0ff, #00f, #f0f, #f00);\n  cursor: pointer;\n}\n.mgd-color__hue::-webkit-slider-thumb { -webkit-appearance: none; width: 20px; height: 20px; border: 3px solid #fff; border-radius: 50%; background: transparent; box-shadow: 0 0 0 1px rgba(0, 0, 0, .35); }\n.mgd-color__hue::-moz-range-thumb { width: 14px; height: 14px; border: 3px solid #fff; border-radius: 50%; background: transparent; box-shadow: 0 0 0 1px rgba(0, 0, 0, .35); }\n\n.mgd-color__row { display: flex; align-items: center; gap: 8px; }\n.mgd-color__preview { flex: 0 0 32px; height: 32px; border-radius: 8px; box-shadow: inset 0 0 0 1px rgba(0, 0, 0, .15); }\n.mgd-color__label { font-weight: 600; color: var(--mgd-c-muted); }\n.mgd-color__hex {\n  flex: 1 1 auto;\n  min-width: 0;\n  height: 32px;\n  padding: 0 8px;\n  border: 1px solid var(--mgd-c-line);\n  border-radius: 8px;\n  background: var(--mgd-c-soft);\n  color: var(--mgd-c-fg);\n  font: 600 13px/1 ui-monospace, SFMono-Regular, Menlo, monospace;\n  text-transform: uppercase;\n}\n.mgd-color__btn {\n  min-height: 32px;\n  padding: 0 12px;\n  border: 1px solid var(--mgd-c-line);\n  border-radius: 8px;\n  background: var(--mgd-c-soft);\n  color: var(--mgd-c-fg);\n  font: inherit;\n  font-weight: 600;\n  cursor: pointer;\n}\n.mgd-color__btn:hover { border-color: var(--mgd-c-muted); }\n.mgd-color__btn--primary { border-color: var(--mgd-c-accent); background: var(--mgd-c-accent); color: var(--mgd-c-accent-fg); }\n.mgd-color__msg { min-height: 1.4em; margin: 6px 0 0; color: var(--mgd-c-muted); font-size: 12px; }\n\n.mgd-color__group { margin-top: 8px; }\n.mgd-color__title { margin: 0 0 6px; color: var(--mgd-c-muted); font-size: 11px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }\n.mgd-color__swatches { display: flex; flex-wrap: wrap; gap: 6px; }\n.mgd-color__item { position: relative; display: inline-flex; }\n.mgd-color__chip {\n  width: 28px;\n  height: 28px;\n  padding: 0;\n  border: 0;\n  border-radius: 8px;\n  box-shadow: inset 0 0 0 1px rgba(0, 0, 0, .18);\n  cursor: pointer;\n}\n.mgd-color__del {\n  position: absolute;\n  top: -6px;\n  right: -6px;\n  width: 18px;\n  height: 18px;\n  padding: 0;\n  border: 1px solid var(--mgd-c-line);\n  border-radius: 50%;\n  background: var(--mgd-c-bg);\n  color: var(--mgd-c-fg);\n  font-size: 12px;\n  line-height: 14px;\n  cursor: pointer;\n  opacity: 0;\n}\n.mgd-color__item:hover .mgd-color__del, .mgd-color__del:focus-visible { opacity: 1; }\n@media (hover: none) { .mgd-color__del { opacity: 1; } }\n.mgd-color__empty { margin: 0; color: var(--mgd-c-muted); font-size: 12px; }\n.mgd-color__save { width: 100%; margin-top: 8px; }\n.mgd-color__clear { margin-right: auto; }\n.mgd-color__foot { display: flex; justify-content: flex-end; gap: 8px; margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--mgd-c-line); }\n\n/* schmal: Blatt von unten, große Ziele */\n.mgd-color--sheet {\n  position: fixed;\n  right: 0;\n  bottom: 0;\n  left: 0;\n  width: auto;\n  max-height: 88vh;\n  overflow: auto;\n  border-radius: 16px 16px 0 0;\n}\n.mgd-color--sheet .mgd-color__area { height: 200px; }\n.mgd-color--sheet .mgd-color__chip { width: 36px; height: 36px; }\n.mgd-color--sheet .mgd-color__btn { min-height: 44px; }\n\n/* Feld im Formular */\n.mgd-color__field { display: inline-flex; align-items: center; gap: 6px; vertical-align: middle; }\n.mgd-color__swatch {\n  width: 32px;\n  height: 32px;\n  padding: 0;\n  border: 1px solid rgba(0, 0, 0, .25);\n  border-radius: 8px;\n  box-shadow: inset 0 0 0 2px #fff;\n  cursor: pointer;\n}\n.mgd-color__swatch--empty { background: repeating-conic-gradient(#c3c4c7 0 25%, #fff 0 50%) 0 0 / 10px 10px !important; }\n.mgd-color__input { width: 7.5em; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; text-transform: uppercase; }\n@media (prefers-reduced-motion: no-preference) { .mgd-color { transition: opacity .12s ease; } }\n";
    var TECH = 'MGDAIImageLabels';
    var THEME_COLORS = 'no' === 'yes';

    // Meine Farben über die Kit-API (für alle MGD-Plugins gleich), Zuletzt benutzt im Browser
    function colorStore() {
        var http = function () { return Shopware.Application.getContainer('init').httpClient; };
        var opts = function () { return { headers: { Authorization: 'Bearer ' + Shopware.Service('loginService').getToken(), 'Content-Type': 'application/json' } }; };
        var rec = function () { try { return JSON.parse(root.localStorage.getItem('mgd-color-recent') || '[]'); } catch (e) { return []; } };

        return {
            load: function () {
                return http().get(API + '/colors', opts()).then(function (r) {
                    return { mine: (r && r.data && r.data.mine) || [], recent: rec() };
                }).catch(function () { return { mine: [], recent: rec() }; });
            },
            save: function (d) {
                try { root.localStorage.setItem('mgd-color-recent', JSON.stringify(d.recent || [])); } catch (e) { /* privat */ }
                return http().post(API + '/colors', { mine: d.mine || [] }, opts()).catch(function () {});
            }
        };
    }

    // nur Farbfelder aus der eigenen Plugin-Konfig (<Tech>.config.*), fremde und Core bleiben wie sie sind
    function colorBind(vm) {
        try {
            var M = root.MGDColor;
            if (!M || !vm || !vm.$el || !vm.$el.querySelector) {
                return;
            }
            var name = String((vm.$attrs && vm.$attrs.name) || vm.name || '');
            // Theme-Plugins (--theme-colors=yes): auch die Farben im Theme-Manager
            var inTheme = THEME_COLORS && !!(vm.$el.closest && vm.$el.closest('.sw-theme-manager-detail'));
            if (name.indexOf(TECH + '.config.') !== 0 && !inTheme) {
                return;
            }
            if (!document.getElementById('mgd-color-style') && COLOR_CSS.indexOf('{{') !== 0) {
                var st = document.createElement('style');
                st.id = 'mgd-color-style';
                st.textContent = COLOR_CSS;
                document.head.appendChild(st);
            }
            M.setStore(colorStore());
            var input = vm.$el.querySelector('input');
            var trig = vm.$el.querySelector('.sw-colorpicker__previewWrapper, .sw-colorpicker__preview-wrapper, .sw-colorpicker__preview');
            if (input && trig) {
                M.bind(input, trig);
            }
        } catch (e) { /* nie die Admin killen */ }
    }

    function colorInstall() {
        if (!Shopware.Component.override) {
            return;
        }
        Shopware.Component.override('sw-colorpicker', {
            mounted: function () {
                var vm = this;
                vm.$nextTick(function () { colorBind(vm); });
            }
        });
    }

    var api = { PRODUCT: PRODUCT, colorBind: colorBind, MODULE: MODULE, TEXTS: TEXTS, CATALOG_CSS: CATALOG_CSS, isLink: isLink, detailsUrl: detailsUrl, mediaName: mediaName, isDataUri: isDataUri, normalize: normalize, component: component };
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = api;
    }
    if (typeof Shopware === 'undefined') {
        return;
    }

    Shopware.Component.register(MODULE + '-page', component);
    colorInstall();
    // --settings-item=no: Plugin hat schon einen eigenen Eintrag > keinen zweiten (Seite bleibt per Route erreichbar)
    var withItem = 'yes' !== 'no';
    Shopware.Module.register(MODULE, {
        type: 'plugin',
        name: MODULE,
        title: 'MGD AI Kennzeichnung',
        description: 'Updates, weitere Plugins und Einstellungen',
        color: '#1f2d3d',
        icon: 'regular-cog',
        routes: {
            index: { component: MODULE + '-page', path: 'index', meta: { parentPath: 'sw.settings.index.plugins', privilege: 'system_config:read' } }
        },
        // Einstellungen > Erweiterungen
        settingsItem: withItem ? [{
            name: MODULE,
            to: MODULE.replace(/-/g, '.') + '.index',
            label: 'MGD AI Kennzeichnung',
            group: 'plugins',
            icon: 'regular-cog',
            privilege: 'system_config:read'
        }] : []
    });
})(typeof window !== 'undefined' ? window : this);
