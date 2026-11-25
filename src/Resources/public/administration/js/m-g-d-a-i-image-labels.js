(()=>{var ht=Object.create;var Ce=Object.defineProperty;var ft=Object.getOwnPropertyDescriptor;var bt=Object.getOwnPropertyNames;var kt=Object.getPrototypeOf,vt=Object.prototype.hasOwnProperty;var xt=(i,n)=>()=>{try{return n||i((n={exports:{}}).exports,n),n.exports}catch(d){throw n=0,d}};var _t=(i,n,d,u)=>{if(n&&typeof n=="object"||typeof n=="function")for(let p of bt(n))!vt.call(i,p)&&p!==d&&Ce(i,p,{get:()=>n[p],enumerable:!(u=ft(n,p))||u.enumerable});return i};var wt=(i,n,d)=>(d=i!=null?ht(kt(i)):{},_t(n||!i||!i.__esModule?Ce(d,"default",{value:i,enumerable:!0}):d,i));var Je=xt((Ye,G)=>{(function(i){"use strict";var n="mgd-ai-kennzeichnung-shopware",d="mgd-kit-"+n,u="/_action/mgd-kit/"+n,p=d+"-style",W=["https://theme.michael-gahn.de/","https://github.com/MichaelGahnDESIGN/"],O=["system.plugin_maintain","system.plugin_upload"],M=/^[a-f0-9]{32}\.webp$/,B=/^data:image\/webp;base64,[A-Za-z0-9+\/]+={0,2}$/,C=/^#[0-9a-fA-F]{6}$/,z=`/* Weitere Plugins JTL + Shopware - Kopie aus Platform/WordPress/assets/kit-catalog.css, gleiche Klassen  */
/* hell, keine fremden Schriften/Bilder. Shopware kriegt die Datei beim Bauen in mgd-kit.js reinkopiert */
.mgd-kit-catalog {
  --ink: #172d2a;
  --muted: #4d5d59;
  --line: #d9e1dc;
  --paper: #f5f6f2;
  --accent: #b51f2a;
  --focus: #a51d29;
  container-type: inline-size;
  color: var(--ink);
  max-width: 1500px;
  margin: 0;
  font-size: 14px;
  line-height: 1.6;
  overflow-wrap: anywhere;
}
.mgd-kit-catalog *,
.mgd-kit-catalog *::before,
.mgd-kit-catalog *::after {
  box-sizing: border-box;
}
.mgd-kit-catalog [hidden] {
  display: none !important;
}
/* nur f\xFCr Screenreader, gibts in JTL/Shopware nicht wie in WP */
.mgd-kit-catalog .mgd-kit-catalog__sr {
  position: absolute !important;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}
.mgd-kit-catalog .mgd-kit-catalog__stale {
  color: var(--muted);
  font-size: 13px;
}
.mgd-kit-catalog a,
.mgd-kit-catalog button {
  text-decoration: none !important;
}
/* Reset gegen kit-jtl.css (.mgd-kit-jtl button / label / h3) */
.mgd-kit-catalog button,
.mgd-kit-catalog .mgd-kit-catalog__button {
  margin: 0;
  font-family: inherit;
}
.mgd-kit-catalog label {
  min-height: 0;
}
.mgd-kit-catalog h2,
.mgd-kit-catalog h3,
.mgd-kit-catalog h4 {
  font-family: inherit;
}
.mgd-kit-catalog p {
  font-size: inherit;
}
/* Fokus immer sichtbar */
.mgd-kit-catalog a:focus-visible,
.mgd-kit-catalog button:focus-visible,
.mgd-kit-catalog input:focus-visible,
.mgd-kit-catalog select:focus-visible {
  outline: 3px solid var(--focus) !important;
  outline-offset: 3px;
  box-shadow: none !important;
}
/*Hero Kopfbereich */
.mgd-kit-catalog .mgd-kit-catalog__hero {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: space-between;
  overflow: hidden;
  background: #172d2a;
  color: #fff;
  border-radius: 20px;
  padding: 32px 40px;
  min-height: 260px;
}
.mgd-kit-catalog__hero-copy {
  position: relative;
  z-index: 1;
  min-width: 0;
}
.mgd-kit-catalog .mgd-kit-catalog__eyebrow {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 12px;
  letter-spacing: 0.16em;
  font-size: 11px;
  font-weight: 700;
  color: #d6e4db;
  margin: 0 0 16px;
}
.mgd-kit-catalog__brand {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #c3202d;
  color: #fff;
  border-radius: 5px;
  width: 44px;
  height: 32px;
  letter-spacing: -1px;
  font-size: 16px;
}
.mgd-kit-catalog .mgd-kit-catalog__hero h3 {
  font-size: 38px;
  line-height: 1.12;
  font-weight: 700;
  letter-spacing: -1.3px;
  color: #fff;
  margin: 0 0 17px;
  padding: 0;
}
.mgd-kit-catalog .mgd-kit-catalog__hero h3 em {
  font-style: normal;
  color: #e5c58e;
}
.mgd-kit-catalog .mgd-kit-catalog__hero-copy > p:last-child {
  color: #d3ded7;
  margin: 0;
  line-height: 1.6;
  font-size: 14px;
}
.mgd-kit-catalog__hero-art {
  position: relative;
  flex: 0 0 34%;
  height: 215px;
  margin-left: 20px;
}
.mgd-kit-catalog__orbit {
  position: absolute;
  width: 270px;
  height: 270px;
  border: 1px solid #ffffff24;
  border-radius: 50%;
  top: -35px;
  left: 50%;
  transform: translateX(-50%);
}
.mgd-kit-catalog__orbit::after {
  content: "";
  position: absolute;
  inset: 28px;
  border: 1px solid #ffffff15;
  border-radius: 50%;
}
.mgd-kit-catalog__hero-icon {
  position: absolute;
  border-radius: 21px;
  box-shadow: 0 18px 32px #0004;
}
.mgd-kit-catalog__hero-icon--0 { left: 4%; top: 55px; transform: rotate(-13deg); }
.mgd-kit-catalog__hero-icon--1 { left: 37%; top: 20px; transform: rotate(8deg); z-index: 1; }
.mgd-kit-catalog__hero-icon--2 { left: 67%; top: 76px; transform: rotate(17deg); }
.mgd-kit-catalog__art-caption {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  text-align: center;
  color: #c7d4cb;
  font-size: 9px;
  letter-spacing: 0.16em;
  font-weight: 600;
}
/* Leiste mit Anzahl */
.mgd-kit-catalog__collection-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 19px 2px 25px;
  gap: 16px;
}
.mgd-kit-catalog .mgd-kit-catalog__collection-bar p {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  margin: 0;
  color: var(--muted);
  font-size: 12px;
}
.mgd-kit-catalog__collection-bar strong {
  color: var(--ink);
}
.mgd-kit-catalog .mgd-kit-catalog__notice {
  border-left: 4px solid var(--accent);
  background: #fff;
  padding: 14px 18px;
  border-radius: 4px;
  color: var(--ink);
}
/* Suche + Filter */
.mgd-kit-catalog__toolbar {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  align-items: center;
  background: #fff;
  padding: 14px;
  border: 1px solid var(--line);
  border-radius: 12px;
}
.mgd-kit-catalog__search {
  flex: 1 1 260px;
  position: relative;
  min-width: 0;
}
.mgd-kit-catalog__search label {
  position: absolute;
  left: 15px;
  top: 7px;
  font-size: 25px;
  line-height: 1.3;
  color: #566a62;
}
.mgd-kit-catalog .mgd-kit-catalog__search input {
  width: 100%;
  max-width: none;
  height: 46px;
  min-height: 46px;
  border: 1px solid #8a9a93;
  border-radius: 7px;
  background: var(--paper);
  padding: 0 14px 0 44px;
  font-size: 14px;
  color: var(--ink);
}
.mgd-kit-catalog .mgd-kit-catalog__select {
  display: flex;
  align-items: center;
  gap: 9px;
  min-height: 46px;
  font-size: 12px;
  color: var(--muted);
  white-space: nowrap;
}
.mgd-kit-catalog .mgd-kit-catalog__select select {
  border: 1px solid #8a9a93;
  border-radius: 7px;
  height: 46px;
  min-height: 46px;
  max-width: 190px;
  padding-left: 12px;
  font-size: 12px;
  color: var(--ink);
  background-color: #fff;
}
.mgd-kit-catalog__browse-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin: 22px 0 18px;
}
.mgd-kit-catalog__categories {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}
.mgd-kit-catalog .mgd-kit-catalog__categories button {
  display: inline-flex;
  align-items: center;
  background: none;
  border: 1px solid transparent;
  border-radius: 6px;
  font-size: 12px;
  min-height: 44px;
  min-width: 44px;
  padding: 8px 12px;
  cursor: pointer;
  color: var(--muted);
  font-weight: 600;
}
.mgd-kit-catalog .mgd-kit-catalog__categories button[aria-pressed="true"] {
  background: var(--ink);
  color: #fff;
}
.mgd-kit-catalog .mgd-kit-catalog__categories button:hover {
  border-color: var(--ink);
}
.mgd-kit-catalog .mgd-kit-catalog__count {
  white-space: nowrap;
  font-size: 12px;
  color: var(--muted);
  margin: 0;
}
/* Karten Grid - passt sich der Breite an, 390px ohne Querscrollen */
.mgd-kit-catalog__grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(min(100%, 280px), 1fr));
  gap: 20px;
}
.mgd-kit-catalog__card {
  --mgd-kit-cat-accent: #7c9a88;
  --wash: color-mix(in srgb, var(--mgd-kit-cat-accent) 22%, #fff);
  display: flex;
  flex-direction: column;
  min-width: 0;
  background: #fff;
  border: 1px solid #dce2dc;
  border-radius: 13px;
  overflow: hidden;
  box-shadow: 0 3px 9px #192c2410;
  transition: box-shadow 0.18s, border-color 0.18s;
}
.mgd-kit-catalog__card:hover {
  box-shadow: 0 10px 24px #192c2415;
  border-color: #a3b3a7;
}
/* ohne Bild nur Farbfl\xE4che aus accent */
.mgd-kit-catalog__card-art {
  position: relative;
  background: #e4ece5;
  background: linear-gradient(135deg, var(--wash), color-mix(in srgb, var(--mgd-kit-cat-accent) 45%, #fff));
  height: 149px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}
.mgd-kit-catalog__card-background {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
}
.mgd-kit-catalog__card-icon {
  position: relative;
  z-index: 1;
  border-radius: 17px;
  box-shadow: 0 0 0 4px #ffffffb3, 0 10px 24px #14251e40;
}
/* deckende Schilder, Text bleibt auf jedem Bild lesbar */
.mgd-kit-catalog__category,
.mgd-kit-catalog__version {
  z-index: 2;
  background: #fff;
  border: 1px solid #e5e8e3;
  border-radius: 5px;
  padding: 4px 7px;
  line-height: 1.4;
  color: var(--ink);
}
.mgd-kit-catalog__category {
  position: absolute;
  top: 13px;
  left: 17px;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.07em;
  text-transform: uppercase;
}
.mgd-kit-catalog__version {
  position: absolute;
  bottom: 9px;
  right: 13px;
  font-size: 10px;
}
.mgd-kit-catalog__card-body {
  padding: 21px;
  display: flex;
  flex-direction: column;
  flex: 1;
}
.mgd-kit-catalog__card-heading {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 5px;
}
.mgd-kit-catalog .mgd-kit-catalog__card h4 {
  font-size: 20px;
  letter-spacing: -0.45px;
  line-height: 1.25;
  color: var(--ink);
  margin: 0;
}
.mgd-kit-catalog__price {
  color: var(--muted);
  font-size: 11px;
}
.mgd-kit-catalog .mgd-kit-catalog__description {
  color: var(--muted);
  font-size: 13px;
  line-height: 1.65;
  margin: 13px 0 16px;
  min-height: 64px;
}
/* H\xE4kchen Punkte */
.mgd-kit-catalog__features {
  margin: 0 0 20px;
  padding: 0;
  list-style: none;
  font-size: 12px;
}
.mgd-kit-catalog__features li {
  padding-left: 19px;
  position: relative;
  margin: 5px 0;
  line-height: 1.5;
}
.mgd-kit-catalog__features li::before {
  content: "\u2713";
  position: absolute;
  left: 0;
  font-weight: bold;
  color: #2c5a45;
}
.mgd-kit-catalog__card-status {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  justify-content: space-between;
  align-items: center;
  font-size: 11px;
  color: var(--muted);
  margin-top: auto;
  margin-bottom: 16px;
}
.mgd-kit-catalog__status {
  font-size: 11px;
  font-weight: 600;
  line-height: 1.5;
  color: #3f5446;
  background: #eef2ee;
  padding: 4px 8px;
  border-radius: 4px;
}
.mgd-kit-catalog__status--installed { background: #e7f1e8; color: #24543a; }
.mgd-kit-catalog__status--update { background: #fff1cb; color: #5e4200; }
.mgd-kit-catalog__actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  border-top: 1px solid #e5e9e4;
  padding-top: 15px;
}
.mgd-kit-catalog__actions form {
  margin: 0;
}
.mgd-kit-catalog .mgd-kit-catalog__details {
  display: flex;
  align-items: center;
  gap: 10px;
  color: var(--ink);
  font-size: 12px;
  font-weight: 600;
  min-height: 44px;
}
.mgd-kit-catalog .mgd-kit-catalog__details:hover {
  color: var(--accent);
}
/* Basic_BTN Ansehen / Mehr erfahren */
.mgd-kit-catalog .mgd-kit-catalog__button {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  gap: 15px;
  min-height: 44px;
  min-width: 44px;
  padding: 9px 15px;
  border: 1px solid var(--ink);
  border-radius: 6px;
  background: var(--ink);
  color: #fff !important;
  font-size: 12px;
  font-weight: 600;
  line-height: 1.5;
  cursor: pointer;
  max-width: 100%;
  text-align: center;
}
.mgd-kit-catalog .mgd-kit-catalog__button:hover {
  background: #29473d;
}
.mgd-kit-catalog .mgd-kit-catalog__button--quiet {
  color: var(--ink) !important;
  background: #f5f7f3;
  border-color: #b9c6b9;
}
.mgd-kit-catalog .mgd-kit-catalog__button--quiet:hover {
  background: #e7ede7;
}
.mgd-kit-catalog .mgd-kit-catalog__button:disabled {
  opacity: 0.65;
  cursor: wait;
}
.mgd-kit-catalog__unavailable {
  font-size: 12px;
  color: var(--muted);
}
/* Leerzustand */
.mgd-kit-catalog__empty {
  text-align: center;
  padding: 60px 20px;
  background: #fff;
  border: 1px dashed #a7b7a8;
  border-radius: 12px;
}
.mgd-kit-catalog__empty > span {
  font-size: 40px;
}
.mgd-kit-catalog .mgd-kit-catalog__empty h4 {
  font-size: 22px;
  color: var(--ink);
  margin: 8px 0;
}
.mgd-kit-catalog .mgd-kit-catalog__empty p {
  color: var(--muted);
}
.mgd-kit-catalog__footer {
  display: flex;
  justify-content: space-between;
  gap: 24px;
  border-top: 1px solid var(--line);
  margin-top: 30px;
  padding: 18px 2px 4px;
  font-size: 12px;
  color: var(--muted);
}
.mgd-kit-catalog .mgd-kit-catalog__footer p {
  margin: 0;
  color: var(--muted);
  font-size: 12px;
}
.mgd-kit-catalog .mgd-kit-catalog__footer p:first-child {
  display: flex;
  flex-direction: column;
}
.mgd-kit-catalog__footer strong {
  color: var(--ink);
}
.mgd-kit-catalog .mgd-kit-catalog__footer p:last-child {
  text-align: right;
}
/* WTF Modal Effekt - Details Dialog */
.mgd-kit-catalog__dialog {
  border: 0;
  border-radius: 16px;
  padding: 30px;
  width: min(560px, calc(100vw - 32px));
  max-height: 85vh;
  overflow: auto;
  color: var(--ink);
  background: #fff;
  box-shadow: 0 30px 100px #0004;
}
.mgd-kit-catalog__dialog::backdrop {
  background: #0d1e23a8;
}
.mgd-kit-catalog__dialog-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.mgd-kit-catalog .mgd-kit-catalog__dialog-header p {
  font-size: 11px;
  letter-spacing: 0.14em;
  color: var(--muted);
}
.mgd-kit-catalog .mgd-kit-catalog__dialog-header button {
  width: 44px;
  height: 44px;
  justify-content: center;
  background: #eef2ed;
  border: 0;
  border-radius: 50%;
  font-size: 28px;
  line-height: 1;
  cursor: pointer;
  color: var(--ink);
}
.mgd-kit-catalog .mgd-kit-catalog__dialog h2 {
  font-size: 28px;
  line-height: 1.2;
  margin: 15px 0;
  color: var(--ink);
}
.mgd-kit-catalog__dialog li {
  margin-bottom: 9px;
}
.mgd-kit-catalog__dialog ul {
  padding-left: 20px;
  list-style: disc;
}
.mgd-kit-catalog__dialog .mgd-kit-catalog__button {
  margin-top: 12px;
}
/* schmal (Container, nicht Fenster - steckt ja im Dashboard) */
@container (max-width: 900px) {
  .mgd-kit-catalog .mgd-kit-catalog__hero { padding: 28px; }
  .mgd-kit-catalog__hero-icon { width: 78px; height: 78px; }
  .mgd-kit-catalog__search { flex-basis: 100%; }
  .mgd-kit-catalog .mgd-kit-catalog__select { flex: 1 1 160px; }
  .mgd-kit-catalog .mgd-kit-catalog__select select { max-width: none; width: 100%; }
}
@container (max-width: 650px) {
  .mgd-kit-catalog .mgd-kit-catalog__hero { padding: 22px; min-height: 0; border-radius: 13px; }
  .mgd-kit-catalog .mgd-kit-catalog__hero h3 { font-size: 28px; letter-spacing: -0.8px; }
  .mgd-kit-catalog__hero-art { display: none; }
  .mgd-kit-catalog .mgd-kit-catalog__eyebrow { font-size: 10px; letter-spacing: 0.08em; }
  .mgd-kit-catalog__collection-bar { align-items: flex-start; flex-direction: column; padding-bottom: 16px; gap: 3px; }
  .mgd-kit-catalog .mgd-kit-catalog__collection-bar p { gap: 10px; }
  .mgd-kit-catalog .mgd-kit-catalog__select { min-width: 100%; }
  .mgd-kit-catalog .mgd-kit-catalog__select select { flex: 1; }
  .mgd-kit-catalog__browse-heading { align-items: flex-start; flex-direction: column; }
  .mgd-kit-catalog__categories { gap: 3px; }
  .mgd-kit-catalog .mgd-kit-catalog__categories button { padding: 8px; }
  .mgd-kit-catalog__card-body { padding: 16px; }
  .mgd-kit-catalog__footer { flex-direction: column; gap: 12px; }
  .mgd-kit-catalog .mgd-kit-catalog__footer p:last-child { text-align: left; }
  .mgd-kit-catalog .mgd-kit-catalog__description { min-height: 0; }
  .mgd-kit-catalog__dialog { padding: 20px; }
}
@media (prefers-reduced-motion: reduce) {
  .mgd-kit-catalog__card { transition: none; }
}
/* Kontrast-Modus Windows */
@media (forced-colors: active) {
  .mgd-kit-catalog__card-background { display: none; }
  .mgd-kit-catalog .mgd-kit-catalog__button,
  .mgd-kit-catalog .mgd-kit-catalog__categories button { border: 1px solid ButtonText; }
  .mgd-kit-catalog .mgd-kit-catalog__categories button[aria-pressed="true"] { border: 3px solid Highlight; }
  .mgd-kit-catalog .mgd-kit-catalog__hero,
  .mgd-kit-catalog__card-art,
  .mgd-kit-catalog__card { border: 1px solid CanvasText; }
  .mgd-kit-catalog a:focus-visible,
  .mgd-kit-catalog button:focus-visible,
  .mgd-kit-catalog input:focus-visible,
  .mgd-kit-catalog select:focus-visible { outline-color: Highlight !important; }
}

/* Tag-Chips (mehrere w\xE4hlbar) */
.mgd-kit-catalog__tags{display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin:0 0 14px}
.mgd-kit-catalog__tags-title{font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;opacity:.75;margin-right:4px}
.mgd-kit-catalog__tags button{min-height:30px;padding:0 12px;border:1px solid currentColor;border-radius:999px;background:transparent;color:inherit;font:inherit;font-size:13px;cursor:pointer;opacity:.85}
.mgd-kit-catalog__tags button[aria-pressed="true"]{background:var(--mgd-kit-cat-accent,#1e2c2c);border-color:var(--mgd-kit-cat-accent,#1e2c2c);color:#fff;opacity:1}
.mgd-kit-catalog__tags button:focus-visible{outline:3px solid #2271b1;outline-offset:2px}
.mgd-kit-catalog__tags .mgd-kit-catalog__tags-reset{border-style:dashed}
`,I={finishMissing:"Die neuen Dateien sind eingespielt. Bitte klicke unter Erweiterungen bei diesem Plugin auf Aktualisieren."},Qe=[".mgd-kit-sw p{margin:0 0 10px;line-height:1.5}",".mgd-kit-sw .mgd-kit-note{padding:12px;border-left:4px solid #b45309;background:#fffbeb;color:#1f2d3d}",".mgd-kit-sw .mgd-kit-error{padding:12px;border-left:4px solid #b91c1c;background:#fef2f2;color:#1f2d3d}",".mgd-kit-sw .mgd-kit-ok{padding:12px;border-left:4px solid #15803d;background:#f0fdf4;color:#1f2d3d}",".mgd-kit-sw ol{margin:0 0 10px 20px}",".mgd-kit-sw .mgd-kit-small{font-size:13px;color:#3d4c5c}",".mgd-kit-sw label{display:block;margin:0 0 6px;font-weight:600}",".mgd-kit-sw input[type=password]{width:100%;max-width:420px;min-height:44px;padding:8px;border:1px solid #6b7c8d;border-radius:4px}",".mgd-kit-sw span.mgd-kit-catalog__hero-icon{display:block;width:94px;height:94px;overflow:hidden}",".mgd-kit-sw span.mgd-kit-catalog__hero-icon>*{display:block;width:100%;height:100%}",".mgd-kit-sw a:focus-visible,.mgd-kit-sw input:focus-visible{outline:3px solid #1d4ed8;outline-offset:2px}"].join(""),et=['<sw-page class="mgd-kit-sw">',"  <template #smart-bar-header><h2>{{ title }}</h2></template>","  <template #content>","    <sw-card-view>",'      <p v-if="isLoading" role="status">Wird geladen \u2026</p>','      <p v-else-if="loadError" class="mgd-kit-error" role="alert">Die Seite konnte gerade nicht geladen werden. Bitte lade sie neu.</p>',"      <template v-else>",'        <sw-card title="Info">',"          <p>Plugin: {{ status.name }}</p>",'          <p>Version: {{ updates.installed || "unbekannt" }}</p>',"        </sw-card>",'        <sw-card title="Updates">','          <p v-if="!updates.available">Die Update-Pr\xFCfung hat gerade nicht geklappt. Versuche es sp\xE4ter noch einmal.</p>','          <p v-else-if="!updates.newer">Du hast die aktuelle Version.</p>',"          <template v-else>","            <p>Version {{ updates.newest }} ist verf\xFCgbar.</p>",'            <p v-if="updates.changelog" class="mgd-kit-small">{{ updates.changelog }}</p>','            <sw-button v-if="status.canUpdate && !fallback" variant="primary" :disabled="isUpdating" :isLoading="isUpdating" @click="runUpdate">Jetzt aktualisieren</sw-button>','            <p v-if="!status.canUpdate && !fallback" class="mgd-kit-small">Den Knopf \u201EJetzt aktualisieren\u201C sehen nur Admins mit dem Recht, Erweiterungen hochzuladen und zu verwalten.</p>',"          </template>",'          <p v-if="updates.notice" class="mgd-kit-note">{{ updates.notice.text }} <a v-if="isLink(updates.notice.url)" :href="updates.notice.url" target="_blank" rel="noopener noreferrer">Zur aktuellen Version</a></p>',`          <p v-if="message" :class="messageKey === 'finishMissing' ? 'mgd-kit-note' : (messageOk ? 'mgd-kit-ok' : 'mgd-kit-error')" role="status">{{ message }}</p>`,'          <template v-if="fallback || (updates.newer && !status.canUpdate)">',"            <p>So aktualisierst du von Hand:</p>",'            <ol><li v-for="(step, i) in steps" :key="i">{{ step }}</li></ol>','            <p v-if="isLink(downloadLink)"><a :href="downloadLink" target="_blank" rel="noopener noreferrer">ZIP-Datei herunterladen</a></p>',"          </template>","        </sw-card>",'        <sw-card title="Weitere Plugins">','          <div class="mgd-kit-catalog">','            <header class="mgd-kit-catalog__hero"><div class="mgd-kit-catalog__hero-copy"><p class="mgd-kit-catalog__eyebrow"><span class="mgd-kit-catalog__brand">MGD</span> SHOPWARE \xB7 PLUGIN-KOLLEKTION</p>',"              <h3>Mehr aus deinem<br><em>Shop machen.</em></h3><p>Durchdachte Erweiterungen von Michael Gahn DESIGN.<br>Entdecke die passenden Werkzeuge f\xFCr dein Shopware.</p></div>",'              <div class="mgd-kit-catalog__hero-art" aria-hidden="true"><span class="mgd-kit-catalog__orbit"></span>',`                <span v-for="(item, i) in heroItems" :key="item.slug" :class="'mgd-kit-catalog__hero-icon mgd-kit-catalog__hero-icon--' + i"><img v-if="mediaSrc(item.icon)" :src="mediaSrc(item.icon)" width="94" height="94" alt=""><svg v-else viewBox="0 0 128 128" width="94" height="94" aria-hidden="true" focusable="false"><rect width="128" height="128" rx="27" fill="#263b37"/><g fill="none" stroke="#e5c58e" stroke-width="5"><rect x="28" y="28" width="27" height="27" rx="5"/><rect x="28" y="73" width="27" height="27" rx="5"/><rect x="73" y="73" width="27" height="27" rx="5"/></g><path d="M86 27v28M72 41h28" stroke="#e6eee7" stroke-width="5" stroke-linecap="round"/></svg></span>`,'                <span class="mgd-kit-catalog__art-caption">GEMACHT F\xDCR DEIN SHOPWARE</span></div></header>','            <div class="mgd-kit-catalog__collection-bar"><p><strong>{{ recommendations.length }} Erweiterungen</strong><span>Installation nur von Hand</span></p></div>','            <p v-if="recStale" class="mgd-kit-catalog__stale">Die Liste konnte gerade nicht aktualisiert werden. Du siehst den letzten bekannten Stand.</p>','            <section class="mgd-kit-catalog__browser" aria-label="Plugins entdecken">','              <div v-if="recommendations.length" class="mgd-kit-catalog__toolbar">',`                <div class="mgd-kit-catalog__search"><label :for="uid + '-search'"><span aria-hidden="true">\u2315</span><span class="mgd-kit-catalog__sr">Plugins durchsuchen</span></label><input :id="uid + '-search'" v-model="search" type="search" placeholder="Was m\xF6chtest du verbessern?" autocomplete="off"></div>`,'                <label class="mgd-kit-catalog__select">Preis<select v-model="price"><option value="all">Alle Preise</option><option value="free">Kostenlos</option><option value="paid">Kostenpflichtig</option></select></label>',"              </div>",'              <div class="mgd-kit-catalog__browse-heading">',`                <div v-if="recommendations.length" class="mgd-kit-catalog__categories" role="group" aria-label="Kategorie filtern"><button type="button" :aria-pressed="String(category === 'all')" @click="category = 'all'">Alle entdecken</button><button v-for="c in categories" :key="c" type="button" :aria-pressed="String(category === c)" @click="category = c">{{ c }}</button></div>`,'                <div v-if="allTags.length" class="mgd-kit-catalog__tags" role="group" aria-label="Nach Tag filtern"><span class="mgd-kit-catalog__tags-title">Tags</span><button v-for="t in allTags" :key="t" type="button" :aria-pressed="String(pickedTags.indexOf(t) >= 0)" @click="toggleTag(t)">{{ t }}</button><button v-if="pickedTags.length" type="button" class="mgd-kit-catalog__tags-reset" @click="pickedTags = []">Alle Tags zur\xFCcksetzen</button></div>','                <p class="mgd-kit-catalog__count" aria-live="polite" aria-atomic="true">{{ countLabel }}</p>',"              </div>",'              <div class="mgd-kit-catalog__grid">','                <article v-for="item in filtered" :key="item.slug" class="mgd-kit-catalog__card" :style="accentStyle(item)">','                  <div class="mgd-kit-catalog__card-art">',`                    <img v-if="mediaSrc(item.image)" class="mgd-kit-catalog__card-background" :src="mediaSrc(item.image)" width="1080" height="360" :alt="item.imageAlt || ''" :aria-hidden="item.imageAlt ? null : 'true'">`,'                    <span class="mgd-kit-catalog__category">{{ item.category }}</span>','                    <img v-if="mediaSrc(item.icon)" class="mgd-kit-catalog__card-icon" :src="mediaSrc(item.icon)" width="78" height="78" alt=""><svg v-else class="mgd-kit-catalog__card-icon" viewBox="0 0 128 128" width="78" height="78" aria-hidden="true" focusable="false"><rect width="128" height="128" rx="27" fill="#263b37"/><g fill="none" stroke="#e5c58e" stroke-width="5"><rect x="28" y="28" width="27" height="27" rx="5"/><rect x="28" y="73" width="27" height="27" rx="5"/><rect x="73" y="73" width="27" height="27" rx="5"/></g><path d="M86 27v28M72 41h28" stroke="#e6eee7" stroke-width="5" stroke-linecap="round"/></svg>','                    <span v-if="item.version" class="mgd-kit-catalog__version">v{{ item.version }}</span>',"                  </div>",'                  <div class="mgd-kit-catalog__card-body">',`                    <div class="mgd-kit-catalog__card-heading"><h4>{{ item.name }}</h4><span class="mgd-kit-catalog__price">{{ item.tier === 'free' ? 'Kostenlos' : 'Kostenpflichtig' }}</span></div>`,'                    <p class="mgd-kit-catalog__description">{{ item.description }}</p>','                    <ul v-if="features(item).length" class="mgd-kit-catalog__features"><li v-for="(f, j) in features(item)" :key="j">{{ f }}</li></ul>',`                    <div class="mgd-kit-catalog__card-status"><span :class="'mgd-kit-catalog__status mgd-kit-catalog__status--' + stateOf(item)">{{ statusLabel(item) }}</span></div>`,'                    <div class="mgd-kit-catalog__actions">',`                      <a class="mgd-kit-catalog__details" :href="detailsUrl(item)" target="_blank" rel="noopener noreferrer" aria-haspopup="dialog" :aria-label="'Details zu ' + item.name" @click="openDetails(item, $event)">Details <span aria-hidden="true">\u2197</span></a>`,`                      <a class="mgd-kit-catalog__button" :href="detailsUrl(item)" target="_blank" rel="noopener noreferrer" :aria-label="item.name + ' ansehen (neuer Tab)'">Ansehen</a>`,"                    </div>","                  </div>","                </article>","              </div>",'              <div v-if="filtered.length === 0" class="mgd-kit-catalog__empty"><span aria-hidden="true">\u2315</span>','                <h4>{{ recommendations.length ? "Noch nicht das Richtige dabei?" : "Hier ist gerade nichts gelistet." }}</h4>','                <p>{{ recommendations.length ? "Versuche einen anderen Suchbegriff oder setze die Filter zur\xFCck." : "Bitte schau sp\xE4ter noch einmal vorbei." }}</p>','                <button v-if="recommendations.length" class="mgd-kit-catalog__button" type="button" @click="resetFilters">Filter zur\xFCcksetzen</button>',"              </div>","            </section>",'            <footer class="mgd-kit-catalog__footer"><p><strong>Michael Gahn DESIGN</strong><span>Werkzeuge mit einem klaren Zweck.</span></p>','              <p>{{ recCentral ? "Katalog von theme.michael-gahn.de" : "Ersatzkatalog aktiv \xB7 Zentraler Feed derzeit nicht erreichbar" }}<template v-if="recChecked"><br><span>Gepr\xFCft: {{ recChecked }} \xB7 Zwischenspeicherung bis zu 15 Minuten</span></template></p></footer>',`            <dialog ref="dialog" class="mgd-kit-catalog__dialog" :aria-labelledby="uid + '-dialog-title'" @close="onDialogClose" @click="onDialogClick">`,'              <div class="mgd-kit-catalog__dialog-header"><p>MGD PLUGIN-KOLLEKTION</p><button ref="dialogClose" type="button" aria-label="Details schlie\xDFen" @click="closeDetails">\xD7</button></div>',`              <h2 :id="uid + '-dialog-title'">{{ dialogItem ? dialogItem.name : "Plugin-Details" }}</h2>`,'              <div v-if="dialogItem">',"                <p>{{ dialogItem.description }}</p>",'                <ul v-if="features(dialogItem).length"><li v-for="(f, j) in features(dialogItem)" :key="j">{{ f }}</li></ul>','                <p>{{ dialogItem.tier === "free" ? "Kostenlos erh\xE4ltlich." : "Kostenpflichtige Erweiterung. Informationen und Konditionen findest du beim Anbieter." }}</p>','                <a class="mgd-kit-catalog__button" :href="detailsUrl(dialogItem)" target="_blank" rel="noopener noreferrer">Mehr erfahren <span class="mgd-kit-catalog__sr">(neuer Tab)</span></a>',"              </div>","            </dialog>","          </div>","        </sw-card>",'        <sw-card v-if="status.install" title="Installation melden">','          <sw-switch-field :label="status.install.label" :value="status.install.enabled" :disabled="isSaving" @update:value="setInstall" @change="setInstall"></sw-switch-field>','          <p class="mgd-kit-small">{{ status.install.hint }}</p>','          <p class="mgd-kit-small" role="status">{{ status.install.status }}</p>',"        </sw-card>",'        <sw-card v-if="status.license" title="Lizenz">','          <label :for="licenseId">Lizenzschl\xFCssel</label>','          <input :id="licenseId" v-model="licenseToken" type="password" autocomplete="off" spellcheck="false">','          <p v-if="status.license.hasToken" class="mgd-kit-small">Eingetragen ist ein Schl\xFCssel, der auf {{ status.license.hint }} endet.</p>','          <p role="status">{{ status.license.status }}</p>','          <p v-if="licenseMessage" role="status">{{ licenseMessage }}</p>',`          <sw-button variant="primary" :disabled="isSaving" @click="saveLicense('save')">Speichern</sw-button>`,`          <sw-button v-if="status.license.hasToken" :disabled="isSaving" @click="saveLicense('remove')">Entfernen</sw-button>`,'          <p class="mgd-kit-small">Zur Pr\xFCfung gehen nur der Lizenzschl\xFCssel und die Domain deines Shops an theme.michael-gahn.de.</p>',"        </sw-card>","      </template>","    </sw-card-view>","  </template>","</sw-page>"].join(`
`);function ce(e){return typeof e=="string"&&e.length<500&&!/[\s"'<>\\]/.test(e)&&W.some(function(o){return e.indexOf(o)===0})}function q(e){return String(e||"").toLocaleLowerCase("de").normalize("NFD").replace(/[\u0300-\u036f]/g,"").replace(/ß/g,"ss")}function tt(e){var o=new Date(e*1e3),l=function(g){return(g<10?"0":"")+g};return l(o.getUTCDate())+"."+l(o.getUTCMonth()+1)+"."+o.getUTCFullYear()+" \xB7 "+l(o.getUTCHours())+":"+l(o.getUTCMinutes())}function ge(e){var o=e&&typeof e.slug=="string"&&/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(e.slug)?e.slug:"";return"https://theme.michael-gahn.de/plugins/"+o}function V(e){return typeof e=="string"&&M.test(e)?e:""}function X(e){return typeof e=="string"&&e.length<1e6&&B.test(e)}function it(e){if(!(!e||e.getElementById(p))){var o=e.createElement("style");o.id=p,o.textContent=Qe+(z.indexOf("{{")===0?"":z),e.head.appendChild(o)}}var pe={template:et,inject:["loginService","acl"],data:function(){return{isLoading:!0,loadError:!1,isUpdating:!1,isSaving:!1,status:{},recommendations:[],recStale:!1,recCentral:!0,recChecked:"",media:{},search:"",price:"all",category:"all",pickedTags:[],dialogItem:null,trigger:null,message:"",messageKey:"",messageOk:!1,fallback:null,licenseToken:"",licenseMessage:""}},computed:{title:function(){return"MGD AI Kennzeichnung"},updates:function(){return this.status.updates||{}},steps:function(){var e=this.fallback&&Array.isArray(this.fallback.steps)?this.fallback.steps:this.status.fallbackSteps;return Array.isArray(e)?e:[]},downloadLink:function(){return this.fallback&&this.fallback.url?this.fallback.url:this.updates.downloadUrl},licenseId:function(){return d+"-license"},uid:function(){return d+"-cat"},heroItems:function(){return this.recommendations.slice(0,3)},allTags:function(){var e=[];return(this.recommendations||[]).forEach(function(o){(Array.isArray(o.tags)?o.tags:[]).forEach(function(l){typeof l=="string"&&l&&e.indexOf(l)<0&&e.push(l)})}),e},categories:function(){var e=[];return this.recommendations.forEach(function(o){typeof o.category=="string"&&o.category&&e.indexOf(o.category)<0&&e.push(o.category)}),e},filtered:function(){var e=this,o=q(e.search).trim().split(/\s+/).filter(Boolean);return e.recommendations.filter(function(l){var g=Array.isArray(l.tags)?l.tags:[],w=q([l.name,l.description,l.category].concat(e.features(l),g).join(" ")),k=e.pickedTags.every(function(S){return g.indexOf(S)>=0});return k&&(e.category==="all"||l.category===e.category)&&(e.price==="all"||l.tier===e.price)&&o.every(function(S){return w.indexOf(S)>=0})})},countLabel:function(){var e=this.filtered.length;return e+" "+(e===1?"Plugin":"Plugins")}},created:function(){it(i.document),this.load()},methods:{isLink:ce,detailsUrl:ge,setRecommendations:function(e){var o=Array.isArray(e.items)?e.items:[];this.recommendations=o.filter(function(l){return l&&typeof l.slug=="string"&&/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(l.slug)&&typeof l.name=="string"}),this.recStale=e.source==="stale"||e.source==="fallback",this.recCentral=e.source==="fresh"||e.source==="cache"||e.source==="stale",this.recChecked=typeof e.checkedAt=="number"&&e.checkedAt>0?tt(e.checkedAt):""},loadMedia:function(){var e=this,o=[];return e.recommendations.forEach(function(l){[l.icon,l.image].forEach(function(g){V(g)&&o.indexOf(g)<0&&o.push(g)})}),o.reduce(function(l,g){return l.then(function(){return e.http().get(u+"/media/"+g,e.opts()).then(function(w){var k=w&&w.data?w.data.data:"";if(X(k)){var S=Object.assign({},e.media);S[g]=k,e.media=S}}).catch(function(){})})},Promise.resolve())},mediaSrc:function(e){var o=V(e);return o&&X(this.media[o])?this.media[o]:""},features:function(e){return e&&Array.isArray(e.features)?e.features.filter(function(o){return typeof o=="string"}).slice(0,3):[]},accentStyle:function(e){return e&&typeof e.accent=="string"&&C.test(e.accent)?{"--mgd-kit-cat-accent":e.accent}:{}},stateOf:function(e){return e&&(e.state==="installed"||e.state==="update")?e.state:"available"},statusLabel:function(e){return e&&e.active===!0?"Aktiv":e&&e.installed===!0?"Installiert \xB7 inaktiv":"F\xFCr deinen Shop"},resetFilters:function(){this.search="",this.price="all",this.category="all",this.pickedTags=[]},toggleTag:function(e){var o=this.pickedTags.indexOf(e);this.pickedTags=o>=0?this.pickedTags.filter(function(l){return l!==e}):this.pickedTags.concat([e])},openDetails:function(e,o){var l=this.$refs?this.$refs.dialog:null;!l||typeof l.showModal!="function"||(o&&(o.preventDefault(),this.trigger=o.currentTarget||null),this.dialogItem=e,l.showModal(),this.$refs.dialogClose&&this.$refs.dialogClose.focus())},closeDetails:function(){this.$refs&&this.$refs.dialog&&this.$refs.dialog.close()},onDialogClick:function(e){e&&this.$refs&&e.target===this.$refs.dialog&&this.closeDetails()},onDialogClose:function(){this.trigger&&typeof this.trigger.focus=="function"&&this.trigger.focus()},http:function(){return Shopware.Application.getContainer("init").httpClient},opts:function(){return{headers:{Authorization:"Bearer "+this.loginService.getToken(),"Content-Type":"application/json"}}},load:function(){var e=this;return e.isLoading=!0,e.http().get(u+"/status",e.opts()).then(function(o){return e.status=o&&o.data?o.data:{},e.loadError=!1,e.http().get(u+"/recommendations",e.opts()).then(function(l){return e.setRecommendations(l&&l.data?l.data:{}),e.loadMedia()}).catch(function(){e.recommendations=[],e.recStale=!0,e.recCentral=!1})}).catch(function(){e.loadError=!0}).then(function(){e.isLoading=!1})},runUpdate:function(){var e=this;return e.isUpdating||!O.every(function(o){return e.acl.can(o)})?null:(e.isUpdating=!0,e.message="",e.fallback=null,e.http().post(u+"/update",{},e.opts()).then(function(o){var l=o&&o.data?o.data:{};return l.ok===!0&&l.step==="uploaded"?(e.show(l),e.http().post(u+"/update/finish",{},e.opts()).then(function(g){e.show(g&&g.data?g.data:{})}).catch(function(g){if(g&&g.response&&g.response.status===404)return e.showKey("finishMissing"),null;throw g})):(e.show(l),null)}).catch(function(o){e.show(o&&o.response&&o.response.data?o.response.data:{})}).then(function(){e.isUpdating=!1}))},showKey:function(e){this.messageKey=e,this.messageOk=!1,this.message=I[e]||"",this.fallback=null},show:function(e){this.messageKey="",this.messageOk=e.ok===!0,this.message=typeof e.message=="string"?e.message:"Das Update hat nicht geklappt.",this.fallback=e.ok===!0?null:e.fallback||{url:this.updates.downloadUrl,steps:this.status.fallbackSteps}},setInstall:function(e){var o=this;return typeof e!="boolean"||!o.status.install||e===o.status.install.enabled||o.isSaving?null:(o.isSaving=!0,o.http().post(u+"/install-report",{enabled:e},o.opts()).then(function(l){l&&l.data&&l.data.install&&(o.status.install=l.data.install)}).catch(function(){o.status.install=Object.assign({},o.status.install,{status:"Das Speichern hat nicht geklappt."})}).then(function(){o.isSaving=!1}))},saveLicense:function(e){var o=this;return o.isSaving=!0,o.http().post(u+"/license",{action:e,token:e==="save"?o.licenseToken:""},o.opts()).then(function(l){var g=l&&l.data?l.data:{};o.licenseMessage=g.message||"",g.license&&(o.status.license=g.license)}).catch(function(l){var g=l&&l.response&&l.response.data?l.response.data:{};o.licenseMessage=g.message||"Das hat nicht geklappt."}).then(function(){o.licenseToken="",o.isSaving=!1})}}};(function(){"use strict";if(window.MGDColor)return;let e={pick:"Farbe w\xE4hlen",dialog:"Farbw\xE4hler",area:"S\xE4ttigung und Helligkeit",areaValue:"S\xE4ttigung %s %, Helligkeit %v %",hue:"Farbton",hex:"HEX",copy:"Kopieren",copied:"Kopiert",quick:"Schnellfarben",recent:"Zuletzt benutzt",mine:"Meine Farben",save:"Farbe speichern",remove:"%c entfernen",empty:"Noch keine Farben gespeichert.",done:"Fertig",cancel:"Abbrechen",clear:"Leeren",invalid:"Bitte eine Farbe wie #1A2B3C eingeben.",none:"keine"},o=["#000000","#FFFFFF","#1E2C2C","#D6C49A","#B73843","#2271B1","#00A32A","#DBA617","#8C5BD6","#F0F0F1"],l=24,g=8,w=/^#[0-9A-F]{6}$/,k=t=>{let a=String(t||"").trim().toUpperCase();return a.startsWith("#")||(a="#"+a),/^#[0-9A-F]{3}$/.test(a)&&(a="#"+a[1]+a[1]+a[2]+a[2]+a[3]+a[3]),w.test(a)?a:null},S=t=>[1,3,5].map(a=>parseInt(t.slice(a,a+2),16)),he=(t,a,s)=>"#"+[t,a,s].map(c=>Math.round(c).toString(16).padStart(2,"0")).join("").toUpperCase(),fe=t=>{let[a,s,c]=S(t).map(ut=>ut/255),m=Math.max(a,s,c),y=Math.min(a,s,c),_=m-y,Ee=0;return _&&(Ee=m===a?(s-c)/_%6:m===s?(c-a)/_+2:(a-s)/_+4),{h:(Ee*60+360)%360,s:m?_/m:0,v:m}},Z=({h:t,s:a,v:s})=>{let c=m=>{let y=(m+t/60)%6;return(s-s*a*Math.max(0,Math.min(y,4-y,1)))*255};return he(c(5),c(3),c(1))},Y=(t,a,s)=>Math.min(s,Math.max(a,t)),be=(t,a)=>Object.keys(a).reduce((s,c)=>s.split("%"+c).join(a[c]),t),h=(t,a,s)=>{let c=document.createElement(t);return a&&(c.className=a),Object.entries(s||{}).forEach(([m,y])=>c.setAttribute(m,y)),c},$=(t,a)=>(Array.isArray(t)?t:[]).map(k).filter((s,c,m)=>s&&m.indexOf(s)===c).slice(0,a),J={load:async()=>{try{return JSON.parse(localStorage.getItem("mgd-color")||"{}")}catch{return{}}},save:async t=>{try{localStorage.setItem("mgd-color",JSON.stringify(t))}catch{}}},v=null,dt=async()=>{if(v)return v;let t={};try{t=await J.load()||{}}catch{t={}}return v={mine:$(t.mine,l),recent:$(t.recent,g)},v},Q=()=>{try{J.save({mine:v.mine,recent:v.recent})}catch{}},f=null,r={},x=null,b={h:0,s:0,v:0},L="",ke=null,ct=()=>{f=h("div","mgd-color",{role:"dialog","aria-label":e.dialog,hidden:""}),r.area=h("div","mgd-color__area",{role:"slider",tabindex:"0","aria-label":e.area,"aria-valuemin":"0","aria-valuemax":"100"}),r.thumb=h("span","mgd-color__thumb",{"aria-hidden":"true"}),r.area.append(r.thumb),r.hue=h("input","mgd-color__hue",{type:"range",min:"0",max:"360",step:"1","aria-label":e.hue});let t=h("div","mgd-color__row");r.preview=h("span","mgd-color__preview",{"aria-hidden":"true"});let a="mgd-color-hex-"+Math.random().toString(36).slice(2,8),s=h("label","mgd-color__label",{for:a});s.textContent=e.hex,r.hex=h("input","mgd-color__hex",{id:a,type:"text",maxlength:"7",spellcheck:"false",autocomplete:"off",inputmode:"text"}),r.copy=h("button","mgd-color__btn",{type:"button"}),r.copy.textContent=e.copy,t.append(r.preview,s,r.hex,r.copy),r.msg=h("p","mgd-color__msg",{role:"status","aria-live":"polite"}),r.quick=ee(e.quick),r.recent=ee(e.recent),r.mine=ee(e.mine),r.save=h("button","mgd-color__btn mgd-color__save",{type:"button"}),r.save.textContent=e.save,r.mine.box.after(r.save);let c=h("div","mgd-color__foot");r.clear=h("button","mgd-color__btn mgd-color__clear",{type:"button"}),r.clear.textContent=e.clear,r.cancel=h("button","mgd-color__btn",{type:"button"}),r.cancel.textContent=e.cancel,r.done=h("button","mgd-color__btn mgd-color__btn--primary",{type:"button"}),r.done.textContent=e.done,c.append(r.clear,r.cancel,r.done),f.append(r.area,r.hue,t,r.msg,r.quick.wrap,r.recent.wrap,r.mine.wrap,c),document.body.append(f),gt()};function ee(t){let a=h("div","mgd-color__group"),s=h("p","mgd-color__title");s.textContent=t;let c=h("div","mgd-color__swatches",{role:"list"});return a.append(s,c),{wrap:a,box:c}}let A=(t,a,s)=>{if(t.box.replaceChildren(),!a.length&&s){let c=h("p","mgd-color__empty");c.textContent=e.empty,t.box.append(c)}a.forEach(c=>{let m=h("span","mgd-color__item",{role:"listitem"}),y=h("button","mgd-color__chip",{type:"button","aria-label":c,title:c,"data-c":c});if(y.style.background=c,m.append(y),s){let _=h("button","mgd-color__del",{type:"button","aria-label":be(e.remove,{c}),"data-del":c});_.textContent="\xD7",m.append(_)}t.box.append(m)}),!s&&!a.length?t.wrap.hidden=!0:t.wrap.hidden=!1},D=(t,a)=>{let s=Z(b);r.area.style.setProperty("--mgd-hue","hsl("+b.h+" 100% 50%)"),r.thumb.style.left=b.s*100+"%",r.thumb.style.top=(1-b.v)*100+"%",r.thumb.style.background=s,r.area.setAttribute("aria-valuenow",String(Math.round(b.s*100))),r.area.setAttribute("aria-valuetext",be(e.areaValue,{s:Math.round(b.s*100),v:Math.round(b.v*100)})),t!=="hue"&&(r.hue.value=String(Math.round(b.h))),t!=="hex"&&(r.hex.value=s),r.preview.style.background=s,a||ie(s)};function te(t,a){t.classList.toggle("mgd-color__swatch--empty",!a),t.style.background=a||"",t.setAttribute("aria-label",e.pick+": "+(a||e.none))}let ie=t=>{x&&(x.input.value=t,te(x.btn,t),x.input.dispatchEvent(new Event("input",{bubbles:!0})))},ve=(t,a)=>{let s=k(t);if(!s)return!1;let c=b.h;return b=fe(s),b.s===0&&(b.h=c),D(a),!0},xe=t=>{let a=r.area.getBoundingClientRect();b.s=Y((t.clientX-a.left)/a.width,0,1),b.v=1-Y((t.clientY-a.top)/a.height,0,1),D()};function gt(){r.area.addEventListener("pointerdown",t=>{r.area.setPointerCapture(t.pointerId),xe(t),r.area.focus()}),r.area.addEventListener("pointermove",t=>{r.area.hasPointerCapture(t.pointerId)&&xe(t)}),r.area.addEventListener("keydown",t=>{let a=t.shiftKey?.1:.01,s={ArrowLeft:["s",-a],ArrowRight:["s",a],ArrowUp:["v",a],ArrowDown:["v",-a]}[t.key];s&&(t.preventDefault(),b[s[0]]=Y(b[s[0]]+s[1],0,1),D())}),r.hue.addEventListener("input",()=>{b.h=Number(r.hue.value),D("hue")}),r.hex.addEventListener("input",()=>{let t=k(r.hex.value);r.msg.textContent=t||r.hex.value.length<4?"":e.invalid,t&&ve(t,"hex")}),r.copy.addEventListener("click",async()=>{let t=Z(b);try{await navigator.clipboard.writeText(t)}catch{r.hex.select(),document.execCommand&&document.execCommand("copy")}r.msg.textContent=e.copied+": "+t}),f.addEventListener("click",t=>{let a=t.target.closest("[data-del]");if(a){v.mine=v.mine.filter(c=>c!==a.dataset.del),Q(),A(r.mine,v.mine,!0),r.save.focus();return}let s=t.target.closest("[data-c]");s&&ve(s.dataset.c)}),r.save.addEventListener("click",()=>{let t=Z(b);v.mine=$([t,...v.mine],l),Q(),A(r.mine,v.mine,!0)}),r.clear.addEventListener("click",()=>{ie(""),L="",T(!0)}),r.cancel.addEventListener("click",()=>T(!1)),r.done.addEventListener("click",()=>T(!0)),f.addEventListener("keydown",t=>{t.key==="Escape"&&(t.preventDefault(),T(!1)),t.key==="Tab"&&pt(t)}),document.addEventListener("pointerdown",t=>{!f.hidden&&!f.contains(t.target)&&(!x||t.target!==x.btn)&&T(!0)}),window.addEventListener("resize",()=>{f.hidden||_e()})}function pt(t){let a=[...f.querySelectorAll("button, input, [tabindex='0']")].filter(m=>!m.closest("[hidden]"));if(!a.length)return;let s=a[0],c=a[a.length-1];t.shiftKey&&document.activeElement===s?(t.preventDefault(),c.focus()):!t.shiftKey&&document.activeElement===c&&(t.preventDefault(),s.focus())}let _e=()=>{if(f.classList.toggle("mgd-color--sheet",window.innerWidth<600),window.innerWidth<600){f.style.left=f.style.top="";return}let t=x.btn.getBoundingClientRect(),a=f.offsetWidth,s=f.offsetHeight,c=t.left+window.scrollX,m=t.bottom+window.scrollY+6;c+a>window.scrollX+document.documentElement.clientWidth-8&&(c=window.scrollX+document.documentElement.clientWidth-a-8),t.bottom+s+6>window.innerHeight&&t.top-s-6>0?m=t.top+window.scrollY-s-6:t.bottom+s+6>window.innerHeight&&(m=window.scrollY+Math.max(8,window.innerHeight-s-8)),f.style.left=Math.max(8,c)+"px",f.style.top=Math.max(8,m)+"px"},we=async t=>{f||ct(),await dt(),x=t,ke=t.btn,L=k(t.input.value)||"",b=fe(L||"#000000"),r.msg.textContent="",A(r.quick,o,!1),A(r.recent,v.recent,!1),A(r.mine,v.mine,!0),f.hidden=!1,t.btn.setAttribute("aria-expanded","true"),D(void 0,!0),_e(),r.area.focus()},T=t=>{if(!x||f.hidden)return;if(!t)ie(L);else{let s=k(x.input.value);s&&s!==L&&(v.recent=$([s,...v.recent],g),Q())}x.input.dispatchEvent(new Event("change",{bubbles:!0})),x.btn.setAttribute("aria-expanded","false"),f.hidden=!0;let a=ke;x=null,a&&a.focus()},ye=t=>{if(!t||t.dataset.mgdColorReady)return;t.dataset.mgdColorReady="1";let a=t.type==="color"?t.getAttribute("value")||"":t.value,s=k(a)||"";t.type==="color"&&(t.type="text"),t.value=s,t.classList.add("mgd-color__input"),t.setAttribute("maxlength","7"),t.setAttribute("spellcheck","false");let c=h("span","mgd-color__field");t.before(c);let m=h("button","mgd-color__swatch",{type:"button","aria-haspopup":"dialog","aria-expanded":"false"});te(m,s),c.append(m,t);let y={input:t,btn:m};m.addEventListener("click",()=>x===y&&!f.hidden?T(!0):we(y)),t.addEventListener("input",()=>{let _=k(t.value);(_||t.value==="")&&te(m,_||"")}),t.addEventListener("blur",()=>{let _=k(t.value);_&&(t.value=_)})},mt=(t,a)=>{if(!t||!a||a.dataset.mgdColorReady)return;a.dataset.mgdColorReady="1",a.setAttribute("aria-haspopup","dialog");let s={input:t,btn:a};a.addEventListener("click",c=>{c.preventDefault(),c.stopImmediatePropagation(),x===s&&f&&!f.hidden?T(!0):we(s)},!0)},Se="input[type=color], input.wp-color-picker, input[data-mgd-color]",ne=t=>(t||document).querySelectorAll(Se).forEach(ye);window.MGDColor={texts:e,attach:ye,bind:mt,init:ne,addSelector:t=>{typeof t=="string"&&t.trim()!==""&&(Se+=", "+t)},norm:k,setStore:t=>{t&&typeof t.load=="function"&&typeof t.save=="function"&&(J=t,v=null)},setTexts:t=>Object.assign(e,t||{})},ne(),document.readyState==="loading"&&document.addEventListener("DOMContentLoaded",()=>ne())})();var me=`/* MGD Farbw\xE4hler - Hell/Dunkel per prefers-color-scheme, ohne externe Schriften */
.mgd-color {
  --mgd-c-bg: #ffffff;
  --mgd-c-fg: #1d2327;
  --mgd-c-muted: #50575e;
  --mgd-c-line: #dcdcde;
  --mgd-c-soft: #f6f7f7;
  --mgd-c-accent: #1e2c2c;
  --mgd-c-accent-fg: #ffffff;
  --mgd-c-focus: #2271b1;
  position: absolute;
  z-index: 100100;
  width: 296px;
  max-height: calc(100vh - 16px);
  overflow: auto;
  box-sizing: border-box;
  padding: 14px;
  border: 1px solid var(--mgd-c-line);
  border-radius: 14px;
  background: var(--mgd-c-bg);
  color: var(--mgd-c-fg);
  box-shadow: 0 12px 32px rgba(0, 0, 0, .18), 0 2px 6px rgba(0, 0, 0, .08);
  font: 13px/1.4 -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}
@media (prefers-color-scheme: dark) {
  .mgd-color {
    --mgd-c-bg: #1e2124;
    --mgd-c-fg: #f0f0f1;
    --mgd-c-muted: #a7aaad;
    --mgd-c-line: #3c434a;
    --mgd-c-soft: #2c3338;
    --mgd-c-accent: #d6c49a;
    --mgd-c-accent-fg: #1e2c2c;
    --mgd-c-focus: #72aee6;
  }
  .mgd-color__chip, .mgd-color__preview { box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .28); }
}
.mgd-color[hidden] { display: none; }
.mgd-color *, .mgd-color *::before, .mgd-color *::after { box-sizing: border-box; }
.mgd-color :focus-visible { outline: 3px solid var(--mgd-c-focus); outline-offset: 2px; }

/* Farbfl\xE4che */
.mgd-color__area {
  position: relative;
  height: 160px;
  border-radius: 10px;
  background: linear-gradient(to top, #000, transparent), linear-gradient(to right, #fff, var(--mgd-hue, red));
  cursor: crosshair;
  touch-action: none;
}
.mgd-color__thumb {
  position: absolute;
  width: 20px;
  height: 20px;
  margin: -10px 0 0 -10px;
  border: 3px solid #fff;
  border-radius: 50%;
  box-shadow: 0 0 0 1px rgba(0, 0, 0, .35), 0 2px 4px rgba(0, 0, 0, .3);
  pointer-events: none;
}

/* Farbton-Regler */
.mgd-color__hue {
  -webkit-appearance: none;
  appearance: none;
  display: block;
  width: 100%;
  height: 14px;
  margin: 14px 0 12px;
  border-radius: 7px;
  background: linear-gradient(to right, #f00, #ff0, #0f0, #0ff, #00f, #f0f, #f00);
  cursor: pointer;
}
.mgd-color__hue::-webkit-slider-thumb { -webkit-appearance: none; width: 20px; height: 20px; border: 3px solid #fff; border-radius: 50%; background: transparent; box-shadow: 0 0 0 1px rgba(0, 0, 0, .35); }
.mgd-color__hue::-moz-range-thumb { width: 14px; height: 14px; border: 3px solid #fff; border-radius: 50%; background: transparent; box-shadow: 0 0 0 1px rgba(0, 0, 0, .35); }

.mgd-color__row { display: flex; align-items: center; gap: 8px; }
.mgd-color__preview { flex: 0 0 32px; height: 32px; border-radius: 8px; box-shadow: inset 0 0 0 1px rgba(0, 0, 0, .15); }
.mgd-color__label { font-weight: 600; color: var(--mgd-c-muted); }
.mgd-color__hex {
  flex: 1 1 auto;
  min-width: 0;
  height: 32px;
  padding: 0 8px;
  border: 1px solid var(--mgd-c-line);
  border-radius: 8px;
  background: var(--mgd-c-soft);
  color: var(--mgd-c-fg);
  font: 600 13px/1 ui-monospace, SFMono-Regular, Menlo, monospace;
  text-transform: uppercase;
}
.mgd-color__btn {
  min-height: 32px;
  padding: 0 12px;
  border: 1px solid var(--mgd-c-line);
  border-radius: 8px;
  background: var(--mgd-c-soft);
  color: var(--mgd-c-fg);
  font: inherit;
  font-weight: 600;
  cursor: pointer;
}
.mgd-color__btn:hover { border-color: var(--mgd-c-muted); }
.mgd-color__btn--primary { border-color: var(--mgd-c-accent); background: var(--mgd-c-accent); color: var(--mgd-c-accent-fg); }
.mgd-color__msg { min-height: 1.4em; margin: 6px 0 0; color: var(--mgd-c-muted); font-size: 12px; }

.mgd-color__group { margin-top: 8px; }
.mgd-color__title { margin: 0 0 6px; color: var(--mgd-c-muted); font-size: 11px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
.mgd-color__swatches { display: flex; flex-wrap: wrap; gap: 6px; }
.mgd-color__item { position: relative; display: inline-flex; }
.mgd-color__chip {
  width: 28px;
  height: 28px;
  padding: 0;
  border: 0;
  border-radius: 8px;
  box-shadow: inset 0 0 0 1px rgba(0, 0, 0, .18);
  cursor: pointer;
}
.mgd-color__del {
  position: absolute;
  top: -6px;
  right: -6px;
  width: 18px;
  height: 18px;
  padding: 0;
  border: 1px solid var(--mgd-c-line);
  border-radius: 50%;
  background: var(--mgd-c-bg);
  color: var(--mgd-c-fg);
  font-size: 12px;
  line-height: 14px;
  cursor: pointer;
  opacity: 0;
}
.mgd-color__item:hover .mgd-color__del, .mgd-color__del:focus-visible { opacity: 1; }
@media (hover: none) { .mgd-color__del { opacity: 1; } }
.mgd-color__empty { margin: 0; color: var(--mgd-c-muted); font-size: 12px; }
.mgd-color__save { width: 100%; margin-top: 8px; }
.mgd-color__clear { margin-right: auto; }
.mgd-color__foot { display: flex; justify-content: flex-end; gap: 8px; margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--mgd-c-line); }

/* schmal: Blatt von unten, gro\xDFe Ziele */
.mgd-color--sheet {
  position: fixed;
  right: 0;
  bottom: 0;
  left: 0;
  width: auto;
  max-height: 88vh;
  overflow: auto;
  border-radius: 16px 16px 0 0;
}
.mgd-color--sheet .mgd-color__area { height: 200px; }
.mgd-color--sheet .mgd-color__chip { width: 36px; height: 36px; }
.mgd-color--sheet .mgd-color__btn { min-height: 44px; }

/* Feld im Formular */
.mgd-color__field { display: inline-flex; align-items: center; gap: 6px; vertical-align: middle; }
.mgd-color__swatch {
  width: 32px;
  height: 32px;
  padding: 0;
  border: 1px solid rgba(0, 0, 0, .25);
  border-radius: 8px;
  box-shadow: inset 0 0 0 2px #fff;
  cursor: pointer;
}
.mgd-color__swatch--empty { background: repeating-conic-gradient(#c3c4c7 0 25%, #fff 0 50%) 0 0 / 10px 10px !important; }
.mgd-color__input { width: 7.5em; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; text-transform: uppercase; }
@media (prefers-reduced-motion: no-preference) { .mgd-color { transition: opacity .12s ease; } }
`,nt="MGDAIImageLabels",at=!1;function ot(){var e=function(){return Shopware.Application.getContainer("init").httpClient},o=function(){return{headers:{Authorization:"Bearer "+Shopware.Service("loginService").getToken(),"Content-Type":"application/json"}}},l=function(){try{return JSON.parse(i.localStorage.getItem("mgd-color-recent")||"[]")}catch{return[]}};return{load:function(){return e().get(u+"/colors",o()).then(function(g){return{mine:g&&g.data&&g.data.mine||[],recent:l()}}).catch(function(){return{mine:[],recent:l()}})},save:function(g){try{i.localStorage.setItem("mgd-color-recent",JSON.stringify(g.recent||[]))}catch{}return e().post(u+"/colors",{mine:g.mine||[]},o()).catch(function(){})}}}function ue(e){try{var o=i.MGDColor;if(!o||!e||!e.$el||!e.$el.querySelector)return;var l=String(e.$attrs&&e.$attrs.name||e.name||""),g=at&&!!(e.$el.closest&&e.$el.closest(".sw-theme-manager-detail"));if(l.indexOf(nt+".config.")!==0&&!g)return;if(!document.getElementById("mgd-color-style")&&me.indexOf("{{")!==0){var w=document.createElement("style");w.id="mgd-color-style",w.textContent=me,document.head.appendChild(w)}o.setStore(ot());var k=e.$el.querySelector("input"),S=e.$el.querySelector(".sw-colorpicker__previewWrapper, .sw-colorpicker__preview-wrapper, .sw-colorpicker__preview");k&&S&&o.bind(k,S)}catch{}}function rt(){Shopware.Component.override&&Shopware.Component.override("sw-colorpicker",{mounted:function(){var e=this;e.$nextTick(function(){ue(e)})}})}var lt={PRODUCT:n,colorBind:ue,MODULE:d,TEXTS:I,CATALOG_CSS:z,isLink:ce,detailsUrl:ge,mediaName:V,isDataUri:X,normalize:q,component:pe};if(typeof G<"u"&&G.exports&&(G.exports=lt),!(typeof Shopware>"u")){Shopware.Component.register(d+"-page",pe),rt();var st=!0;Shopware.Module.register(d,{type:"plugin",name:d,title:"MGD AI Kennzeichnung",description:"Updates, weitere Plugins und Einstellungen",color:"#1f2d3d",icon:"regular-cog",routes:{index:{component:d+"-page",path:"index",meta:{parentPath:"sw.settings.index.plugins",privilege:"system_config:read"}}},settingsItem:st?[{name:d,to:d.replace(/-/g,".")+".index",label:"MGD AI Kennzeichnung",group:"plugins",icon:"regular-cog",privilege:"system_config:read"}]:[]})}})(typeof window<"u"?window:Ye)});var ze=`<sw-media-collapse v-if="images.length || busy" :title="$t('mgd-ai-image-labels.bulk.title')" :expand-on-loading="true">
    <template #content>
        <div class="mgd-ai-media-bulk">
            <p>{{ $t('mgd-ai-image-labels.bulk.selection', { count: images.length }) }}</p>
            <p>{{ $t('mgd-ai-image-labels.bulk.help') }}</p>
            <fieldset :disabled="busy || !allowed">
                <legend class="mgd-ai-media-bulk__legend">{{ $t('mgd-ai-image-labels.bulk.title') }}</legend>
                <label v-for="field in fields" :key="field">
                    <span>{{ $t('mgd-ai-image-labels.bulk.' + field) }}</span>
                    <select v-model="values[field]">
                        <option value="">{{ $t('mgd-ai-image-labels.bulk.unchanged') }}</option>
                        <option v-for="value in options[field]" :key="value" :value="value">{{ label(field, value) }}</option>
                    </select>
                </label>
                <button type="button" :disabled="busy || !allowed || !changed || !images.length" @click="apply">
                    {{ $t('mgd-ai-image-labels.bulk.apply', { count: images.length }) }}
                </button>
            </fieldset>
            <p v-if="!allowed">{{ $t('mgd-ai-image-labels.bulk.permission') }}</p>
            <p role="status" aria-live="polite">{{ progress || outcome }}</p>
        </div>
    </template>
</sw-media-collapse>
`;var R={status:["none","generated","partially-generated","modified","deepfake"],position:["top-left","top-right","bottom-left","bottom-right"],theme:["auto","light","dark"]};function j(i){return[...new Map(i.filter(n=>n.getEntityName?.()==="media"&&n.mediaType?.name==="IMAGE").map(n=>[n.id,n])).values()]}function ae(i){let n={};for(let[d,u]of Object.entries(R)){let p=i[d];if(!(p===""||p===void 0)){if(!u.includes(p))throw new Error("Invalid label value");n[`mgd_ai_${d}`]=p}}return n}async function Ie({items:i,values:n,repository:d,context:u,canEdit:p,onProgress:W=()=>{}}){if(!p())throw new Error("Missing media permission");let O=ae(n);if(!Object.keys(O).length)throw new Error("Empty label change");let M=j(i),B={...u},C={saved:[],failed:[]};for(let z of M){try{if(!p())throw new Error("Missing media permission");let I=await d.get(z.id,B);if(!j([I]).length)throw new Error("Image no longer available");I.customFields={...I.customFields??{},...O},await d.save(I,B),C.saved.push(z.id)}catch{C.failed.push(z.id)}W(C.saved.length+C.failed.length,M.length)}return C}Shopware.Component.register("mgd-ai-media-bulk",{template:ze,inject:["repositoryFactory","acl"],props:{items:{type:Array,required:!0}},data(){return{values:{status:"",position:"",theme:""},busy:!1,progress:"",outcome:""}},computed:{images(){return j(this.items)},allowed(){return this.acl.can("media.editor")},changed(){return Object.keys(ae(this.values)).length>0},fields(){return Object.keys(R)},options(){return R},selectionKey(){return this.images.map(i=>i.id).sort().join(",")}},watch:{selectionKey(){this.busy||(this.outcome="",this.values={status:"",position:"",theme:""})}},methods:{label(i,n){let d=n.replace(/-([a-z])/g,(u,p)=>p.toUpperCase());return this.$t(`mgd-ai-image-labels.preview.${i}.${d}`)},async apply(){if(!(this.busy||!this.allowed||!this.changed||!this.images.length)){this.busy=!0,this.outcome="",this.progress="";try{let i=await Ie({items:this.items,values:this.values,repository:this.repositoryFactory.create("media"),context:Shopware.Context.api,canEdit:()=>this.acl.can("media.editor"),onProgress:(n,d)=>{this.progress=`${n} / ${d}`}});this.outcome=this.$t("mgd-ai-image-labels.bulk.result",{saved:i.saved.length,failed:i.failed.length}),this.values={status:"",position:"",theme:""}}catch{this.outcome=this.$t("mgd-ai-image-labels.bulk.error")}finally{this.busy=!1,this.progress=""}}}}});var Te=`{% block sw_media_quickinfo_multiple_file_names %}
    <mgd-ai-media-bulk v-if="editable" :items="items" />
    {% parent %}
{% endblock %}
`;Shopware.Component.override("sw-media-quickinfo-multiple",{template:Te});var Pe={"mgd-ai-image-labels":{preview:{title:"Vorschau der KI-Bildkennzeichnung",helpText:"Die Vorschau wird nur lokal aktualisiert. Speichern Sie \xC4nderungen mit der vorhandenen Shopware-Speicheraktion der Custom Fields.",imageAlt:"Lokale Vorschau des ausgew\xE4hlten Bildmediums",missingImage:"F\xFCr dieses Bild ist noch keine lokale Vorschau verf\xFCgbar.",announcement:"Vorschau: Status {status}, Position {position}, Darstellung {theme}.",status:{none:"KEINE SICHTBARE KENNZEICHNUNG",generated:"KI-GENERIERT",partiallyGenerated:"TEILWEISE KI-GENERIERT",modified:"MIT KI BEARBEITET",deepfake:"KI-DEEPFAKE"},position:{topLeft:"oben links",topRight:"oben rechts",bottomLeft:"unten links",bottomRight:"unten rechts"},theme:{auto:"automatisch",light:"hell",dark:"dunkel"}},philosophy:{label:"Unsere Haltung zu KI-Bildern",preview:"Transparente Informationen zur Erstellung und Bearbeitung von Bildern.",contentLabel:"Inhalt",defaultContent:"<h2>Unser Umgang mit KI-Bildern</h2><p>Wir kennzeichnen Bilder transparent, wenn sie vollst\xE4ndig oder teilweise mit k\xFCnstlicher Intelligenz erstellt oder bearbeitet wurden.</p><p>So k\xF6nnen Sie Inhalte bewusst einordnen. Die Kennzeichnung ver\xE4ndert weder Produktinformationen noch Ihre Privatsph\xE4re.</p>"},settings:{title:"MGD KI-Bildkennzeichnung",description:"Erstellen Sie einmalig ein unverkn\xFCpftes Erlebniswelten-Layout mit Ihrer zweisprachigen AI-Philosophie.",cardTitle:"AI-Philosophie als Erlebniswelt",noPublishNotice:"Die Aktion ver\xF6ffentlicht oder verkn\xFCpft nichts. Pr\xFCfen, bearbeiten und ver\xF6ffentlichen Sie das Layout anschlie\xDFend bewusst in den Erlebniswelten.",action:"Erlebniswelt vorbereiten",missingPermission:"F\xFCr diese Aktion fehlen Rechte f\xFCr Systemkonfiguration, CMS-Seiten, Sektionen, Bl\xF6cke oder Elemente.",successCreated:"Die Erlebniswelt wurde sicher vorbereitet.",successExisting:"Die bereits vorhandene plugin-eigene Erlebniswelt wurde gefunden.",error:"Die Erlebniswelt konnte nicht vorbereitet werden. Bitte pr\xFCfen Sie Berechtigung und Systemprotokoll.",openEditor:"In Erlebniswelten \xF6ffnen"},cms:{background:{label:"Gekennzeichnetes Hintergrundbild",preview:"Lokales Hintergrundbild mit KI-Kennzeichnung",contentPlaceholder:"Inhalte des Erlebniswelten-Blocks",fields:{media:"Bildmedium",minHeight:"Mindesth\xF6he",horizontalPosition:"Horizontale Bildposition",verticalPosition:"Vertikale Bildposition",decorative:"Bild ist rein dekorativ",fallbackColor:"Sichere Hintergrundfarbe",altText:"Alternativtext",altTextHelp:"Beschreiben Sie knapp den Inhalt oder Zweck des Bildes. Lassen Sie das Feld nur bei ausdr\xFCcklich dekorativen Bildern leer."},validation:{imageOnly:"Bitte w\xE4hlen Sie ausschlie\xDFlich ein Bildmedium. Dokumente, Videos und andere Dateien sind nicht zul\xE4ssig.",missingAltText:"F\xFCr ein nicht dekoratives Bild fehlt ein Alternativtext. Ohne Beschreibung k\xF6nnen blinde Nutzer den Bildinhalt nicht erfassen."},options:{minHeight:{"240":"Kompakt (240 px)","320":"Standard (320 px)","480":"Gro\xDF (480 px)","640":"Sehr gro\xDF (640 px)"},horizontalPosition:{left:"Links",center:"Mitte",right:"Rechts"},verticalPosition:{top:"Oben",center:"Mitte",bottom:"Unten"},fallbackColor:{"neutral-light":"Neutral hell","neutral-dark":"Neutral dunkel",brand:"Blau"}}}},bulk:{title:"KI-Kennzeichnung",selection:"{count} Bilder ausgew\xE4hlt",help:"Nur ausgew\xE4hlte Bilder werden ge\xE4ndert. Ordner und andere Dateien bleiben unver\xE4ndert. Die \xC4nderungen gelten f\xFCr die oben gew\xE4hlte Sprache.",status:"Kennzeichnung",position:"Position",theme:"Darstellung",unchanged:"Unver\xE4ndert lassen",apply:"Auf {count} Bilder anwenden",permission:"Zum \xC4ndern ist das Recht \u201EMedien bearbeiten\u201C erforderlich.",result:"{saved} Bilder gespeichert, {failed} fehlgeschlagen. Bei Fehlern die betroffenen Bilder pr\xFCfen und erneut ausw\xE4hlen.",error:"Die \xC4nderungen konnten nicht gespeichert werden. Bitte Berechtigungen und Verbindung pr\xFCfen."}}};var Le={"mgd-ai-image-labels":{preview:{title:"AI image label preview",helpText:"The preview updates locally only. Save changes with the existing Shopware custom-field save action.",imageAlt:"Local preview of the selected image medium",missingImage:"No local preview is available for this image yet.",announcement:"Preview: status {status}, position {position}, appearance {theme}.",status:{none:"NO VISIBLE LABEL",generated:"AI GENERATED",partiallyGenerated:"AI PARTIALLY GENERATED",modified:"AI MODIFIED",deepfake:"AI DEEPFAKE"},position:{topLeft:"top left",topRight:"top right",bottomLeft:"bottom left",bottomRight:"bottom right"},theme:{auto:"automatic",light:"light",dark:"dark"}},philosophy:{label:"Our approach to AI imagery",preview:"Transparent information about how images are created and edited.",contentLabel:"Content",defaultContent:"<h2>Our approach to AI imagery</h2><p>We label images transparently when they were created or edited wholly or partly with artificial intelligence.</p><p>This helps you assess content consciously. The label changes neither product information nor your privacy.</p>"},settings:{title:"MGD AI Image Labels",description:"Create an unassigned Shopping Experiences layout containing your bilingual AI philosophy once.",cardTitle:"AI philosophy Shopping Experience",noPublishNotice:"This action publishes or assigns nothing. Review, edit and publish the layout consciously in Shopping Experiences afterwards.",action:"Prepare Shopping Experience",missingPermission:"This action requires permissions for system configuration, CMS pages, sections, blocks and elements.",successCreated:"The Shopping Experience was prepared safely.",successExisting:"The existing plugin-owned Shopping Experience was found.",error:"The Shopping Experience could not be prepared. Please check your permission and the system log.",openEditor:"Open in Shopping Experiences"},cms:{background:{label:"Labeled background image",preview:"Local background image with AI label",contentPlaceholder:"Shopping Experiences block content",fields:{media:"Image medium",minHeight:"Minimum height",horizontalPosition:"Horizontal image position",verticalPosition:"Vertical image position",decorative:"Image is purely decorative",fallbackColor:"Safe background colour",altText:"Alternative text",altTextHelp:"Briefly describe the image content or purpose. Leave this field empty only for explicitly decorative images."},validation:{imageOnly:"Please select an image medium only. Documents, videos and other files are not permitted.",missingAltText:"A non-decorative image is missing alternative text. Without a description, blind users cannot understand the image content."},options:{minHeight:{"240":"Compact (240 px)","320":"Standard (320 px)","480":"Large (480 px)","640":"Extra large (640 px)"},horizontalPosition:{left:"Left",center:"Centre",right:"Right"},verticalPosition:{top:"Top",center:"Centre",bottom:"Bottom"},fallbackColor:{"neutral-light":"Neutral light","neutral-dark":"Neutral dark",brand:"Blue"}}}},bulk:{title:"AI labels",selection:"{count} images selected",help:"Only selected images are changed. Folders and other files remain unchanged. Changes apply to the language selected above.",status:"Label",position:"Position",theme:"Appearance",unchanged:"Leave unchanged",apply:"Apply to {count} images",permission:"The media editing permission is required.",result:"{saved} images saved, {failed} failed. If errors occur, check the affected images and select them again.",error:"Changes could not be saved. Please check permissions and connection."}}};var Ae=`{% block mgd_ai_media_preview %}
<section
    class="mgd-ai-media-preview"
    :aria-label="$t('mgd-ai-image-labels.preview.title')"
>
    <h3 class="mgd-ai-media-preview__title">
        {{ $t('mgd-ai-image-labels.preview.title') }}
    </h3>

    <p class="mgd-ai-media-preview__help">
        {{ $t('mgd-ai-image-labels.preview.helpText') }}
    </p>

    <div
        v-if="item.url"
        class="mgd-ai-media-preview__canvas"
    >
        <img
            class="mgd-ai-media-preview__image"
            :src="item.url"
            :alt="$t('mgd-ai-image-labels.preview.imageAlt')"
        >

        <span
            v-if="previewState.visible"
            class="mgd-ai-media-preview__badge"
            :class="[
                previewPresentation.positionClass,
                previewPresentation.themeClass,
            ]"
            role="note"
        >
            {{ $t(previewPresentation.labelSnippet) }}
        </span>
    </div>

    <p
        v-else
        class="mgd-ai-media-preview__missing-image"
    >
        {{ $t('mgd-ai-image-labels.preview.missingImage') }}
    </p>

    <p
        class="mgd-ai-media-preview__status"
        aria-live="polite"
        aria-atomic="true"
    >
        {{ $t('mgd-ai-image-labels.preview.announcement', {
            status: $t(previewPresentation.labelSnippet),
            position: $t(previewPresentation.positionSnippet),
            theme: $t(previewPresentation.themeSnippet),
        }) }}
    </p>
</section>
{% endblock %}
`;var It=Object.freeze(["none","generated","partially-generated","modified","deepfake"]),Tt=Object.freeze(["top-left","top-right","bottom-left","bottom-right"]),Pt=Object.freeze(["auto","light","dark"]),Lt=Object.freeze({"top-left":"is--top-left","top-right":"is--top-right","bottom-left":"is--bottom-left","bottom-right":"is--bottom-right"}),At=Object.freeze({auto:"is--auto",light:"is--light",dark:"is--dark"}),Dt=Object.freeze({none:"mgd-ai-image-labels.preview.status.none",generated:"mgd-ai-image-labels.preview.status.generated","partially-generated":"mgd-ai-image-labels.preview.status.partiallyGenerated",modified:"mgd-ai-image-labels.preview.status.modified",deepfake:"mgd-ai-image-labels.preview.status.deepfake"}),Ot=Object.freeze({"top-left":"mgd-ai-image-labels.preview.position.topLeft","top-right":"mgd-ai-image-labels.preview.position.topRight","bottom-left":"mgd-ai-image-labels.preview.position.bottomLeft","bottom-right":"mgd-ai-image-labels.preview.position.bottomRight"}),Mt=Object.freeze({auto:"mgd-ai-image-labels.preview.theme.auto",light:"mgd-ai-image-labels.preview.theme.light",dark:"mgd-ai-image-labels.preview.theme.dark"});function oe(i={}){let n=typeof i=="object"&&i!==null?i:{},d=It.includes(n.status)?n.status:"none",u=Tt.includes(n.position)?n.position:"bottom-right",p=Pt.includes(n.theme)?n.theme:"auto";return{status:d,position:u,theme:p,visible:d!=="none"}}function De(i={}){let n=oe(i);return{positionClass:Lt[n.position],themeClass:At[n.theme],labelSnippet:Dt[n.status],positionSnippet:Ot[n.position],themeSnippet:Mt[n.theme]}}Shopware.Component.register("mgd-ai-media-preview",{template:Ae,props:{item:{type:Object,required:!0}},computed:{previewInput(){let i=this.item.customFields??{};return{status:i.mgd_ai_status,position:i.mgd_ai_position,theme:i.mgd_ai_theme}},previewState(){return oe(this.previewInput)},previewPresentation(){return De(this.previewState)}}});var Oe=`{% block sw_media_quickinfo_custom_field_sets %}
    {% parent %}

    <mgd-ai-media-preview
        v-if="item.mediaType && item.mediaType.name === 'IMAGE'"
        :item="item"
    />
{% endblock %}
`;Shopware.Component.override("sw-media-quickinfo",{template:Oe});var Me=`<div class="mgd-ai-background-image" :class="presentationClasses">
    <img v-if="mediaSource" class="mgd-ai-background-image__media" :src="mediaSource" alt="">
    <div class="mgd-ai-background-image__content">
        {{ $tc('mgd-ai-image-labels.cms.background.contentPlaceholder') }}
    </div>
</div>
`;var E=Object.freeze({minHeight:Object.freeze(["240px","320px","480px","640px"]),horizontalPosition:Object.freeze(["left","center","right"]),verticalPosition:Object.freeze(["top","center","bottom"]),fallbackColor:Object.freeze(["neutral-light","neutral-dark","brand"])}),U=Object.freeze({minHeight:"320px",horizontalPosition:"center",verticalPosition:"center",fallbackColor:"neutral-light",decorative:!1,altText:""});function re(i){return i===!0}function F(i){return typeof i!="string"?"":[...i.replace(/<(script|style)\b[^>]*>[\s\S]*?<\/\1\s*>/giu,"").replace(/<[^>]*>/gu,"").replace(/[\u0000-\u001F\u007F]+/gu," ").trim()].slice(0,512).join("")}function Be(i,n){let d=typeof n?.translated=="object"&&n.translated!==null?n.translated:{};for(let u of[i,d.alt,d.title]){let p=F(u);if(p!=="")return p}return""}function le(i){if(typeof i!="object"||i===null||typeof i.mimeType!="string"||!i.mimeType.startsWith("image/"))return!1;let n=i.mediaType;return typeof n=="object"&&n!==null&&typeof n.name=="string"&&n.name==="IMAGE"}function N(i,n,d){return typeof i=="string"&&n.includes(i)?i:d}function P(i={}){let n=typeof i=="object"&&i!==null?i:{};return{minHeight:N(n.minHeight,E.minHeight,U.minHeight),horizontalPosition:N(n.horizontalPosition,E.horizontalPosition,U.horizontalPosition),verticalPosition:N(n.verticalPosition,E.verticalPosition,U.verticalPosition),fallbackColor:N(n.fallbackColor,E.fallbackColor,U.fallbackColor),decorative:re(n.decorative),altText:F(n.altText)}}var{Mixin:jt}=Shopware,$e={template:Me,mixins:[jt.getByName("cms-element")],computed:{presentation(){return P({minHeight:this.element?.config?.minHeight?.value,horizontalPosition:this.element?.config?.horizontalPosition?.value,verticalPosition:this.element?.config?.verticalPosition?.value,fallbackColor:this.element?.config?.fallbackColor?.value,decorative:this.element?.config?.decorative?.value,altText:this.element?.config?.altText?.value})},presentationClasses(){return[`mgd-ai-background-image--height-${this.presentation.minHeight.replace("px","")}`,`mgd-ai-background-image--horizontal-${this.presentation.horizontalPosition}`,`mgd-ai-background-image--vertical-${this.presentation.verticalPosition}`,`mgd-ai-background-image--fallback-${this.presentation.fallbackColor}`]},mediaSource(){return this.element?.data?.media?.url??null}},created(){this.initElementConfig("mgd-ai-background-image"),this.initElementData("mgd-ai-background-image")}};var je=`<div class="sw-cms-el-config-mgd-ai-background-image">
    <sw-media-upload-v2
        variant="regular"
        :upload-tag="uploadTag"
        :source="previewSource"
        :allow-multi-select="false"
        file-accept="image/*"
        :caption="$tc('mgd-ai-image-labels.cms.background.fields.media')"
        @media-upload-sidebar-open="mediaModalIsOpen = true"
        @media-upload-remove-image="onImageRemove"
    />
    <sw-upload-listener :upload-tag="uploadTag" auto-upload @media-upload-finish="onImageUpload" />
    <sw-media-modal-v2
        v-if="mediaModalIsOpen"
        :allow-multi-select="false"
        file-accept="image/*"
        @media-modal-selection-change="onSelectionChanges"
        @modal-close="mediaModalIsOpen = false"
    />

    <mt-banner v-if="selectionError" variant="error">
        {{ $tc('mgd-ai-image-labels.cms.background.validation.imageOnly') }}
    </mt-banner>

    <mt-select v-model:model-value="element.config.minHeight.value" :label="$tc('mgd-ai-image-labels.cms.background.fields.minHeight')" :options="minHeightOptions" @update:model-value="emitUpdate" />
    <mt-select v-model:model-value="element.config.horizontalPosition.value" :label="$tc('mgd-ai-image-labels.cms.background.fields.horizontalPosition')" :options="horizontalOptions" @update:model-value="emitUpdate" />
    <mt-select v-model:model-value="element.config.verticalPosition.value" :label="$tc('mgd-ai-image-labels.cms.background.fields.verticalPosition')" :options="verticalOptions" @update:model-value="emitUpdate" />
    <mt-select v-model:model-value="element.config.fallbackColor.value" :label="$tc('mgd-ai-image-labels.cms.background.fields.fallbackColor')" :options="fallbackOptions" @update:model-value="emitUpdate" />
    <mt-switch :checked="element.config.decorative.value" :label="$tc('mgd-ai-image-labels.cms.background.fields.decorative')" @change="onChangeDecorative" />
    <mt-text-field
        v-model:model-value="element.config.altText.value"
        :label="$tc('mgd-ai-image-labels.cms.background.fields.altText')"
        :help-text="$tc('mgd-ai-image-labels.cms.background.fields.altTextHelp')"
        :disabled="element.config.decorative.value === true"
        @update:model-value="onChangeAltText"
    />
    <mt-banner v-if="missingAltText" variant="warning">
        {{ $tc('mgd-ai-image-labels.cms.background.validation.missingAltText') }}
    </mt-banner>
</div>
`;var{Mixin:Ut}=Shopware,Re={template:je,compatConfig:Shopware.compatConfig,inject:["repositoryFactory"],emits:["element-update"],mixins:[Ut.getByName("cms-element")],data(){return{mediaModalIsOpen:!1,selectionError:!1}},computed:{mediaRepository(){return this.repositoryFactory.create("media")},uploadTag(){return`mgd-ai-background-image-${this.element.id}`},previewSource(){return this.element?.data?.media??this.element?.config?.media?.value??null},minHeightOptions(){return this.options("minHeight",E.minHeight)},horizontalOptions(){return this.options("horizontalPosition",E.horizontalPosition)},verticalOptions(){return this.options("verticalPosition",E.verticalPosition)},fallbackOptions(){return this.options("fallbackColor",E.fallbackColor)},missingAltText(){return this.element?.config?.decorative?.value!==!0&&Be(this.element?.config?.altText?.value,this.element?.data?.media)===""}},created(){this.initElementConfig("mgd-ai-background-image"),this.normalizeElementConfig()},methods:{options(i,n){return n.map(d=>({value:d,label:this.$tc(`mgd-ai-image-labels.cms.background.options.${i}.${d.replace("px","")}`)}))},normalizeElementConfig(){let i=P({minHeight:this.element.config.minHeight.value,horizontalPosition:this.element.config.horizontalPosition.value,verticalPosition:this.element.config.verticalPosition.value,fallbackColor:this.element.config.fallbackColor.value,decorative:this.element.config.decorative.value,altText:this.element.config.altText.value});for(let[n,d]of Object.entries(i))this.element.config[n].value=d},emitUpdate(){this.normalizeElementConfig(),this.$emit("element-update",this.element)},async onImageUpload({targetId:i}){let n=await this.mediaRepository.get(i,Shopware.Context.api);if(!le(n)){this.rejectMedia();return}this.setMedia(n)},onSelectionChanges(i){let n=Array.isArray(i)?i[0]:null;if(!n?.id||!le(n)){this.rejectMedia();return}this.setMedia(n)},setMedia(i){this.element.config.media.value=i.id,this.element.config.media.source="static",this.updateElementData(i),this.selectionError=!1,this.mediaModalIsOpen=!1,this.emitUpdate()},onImageRemove(){this.element.config.media.value=null,this.updateElementData(),this.selectionError=!1,this.emitUpdate()},rejectMedia(){this.selectionError=!0},onChangeDecorative(i){this.element.config.decorative.value=re(i),this.emitUpdate()},onChangeAltText(i){this.element.config.altText.value=F(i),this.emitUpdate()},updateElementData(i=null){let n=i===null?null:i.id;if(!this.element.data){this.isCompatEnabled("INSTANCE_SET")?this.$set(this.element,"data",{mediaId:n,media:i}):this.element.data={mediaId:n,media:i};return}this.isCompatEnabled("INSTANCE_SET")?(this.$set(this.element.data,"mediaId",n),this.$set(this.element.data,"media",i)):(this.element.data.mediaId=n,this.element.data.media=i)}}};var Ue=`<div class="mgd-ai-background-image-preview" :class="previewClass">
    <span>{{ $tc('mgd-ai-image-labels.cms.background.preview') }}</span>
</div>
`;var Ne={template:Ue,computed:{previewClass(){return`mgd-ai-background-image--height-${P().minHeight.replace("px","")}`}}};Shopware.Component.register("sw-cms-el-mgd-ai-background-image",$e);Shopware.Component.register("sw-cms-el-config-mgd-ai-background-image",Re);Shopware.Component.register("sw-cms-el-preview-mgd-ai-background-image",Ne);Shopware.Service("cmsService").registerCmsElement({name:"mgd-ai-background-image",label:"mgd-ai-image-labels.cms.background.label",component:"sw-cms-el-mgd-ai-background-image",configComponent:"sw-cms-el-config-mgd-ai-background-image",previewComponent:"sw-cms-el-preview-mgd-ai-background-image",defaultConfig:{media:{source:"static",value:null,required:!0},minHeight:{source:"static",value:"320px"},horizontalPosition:{source:"static",value:"center"},verticalPosition:{source:"static",value:"center"},decorative:{source:"static",value:!1},fallbackColor:{source:"static",value:"neutral-light"},altText:{source:"static",value:""}}});var Fe=`{% block sw_cms_element_mgd_ai_philosophy %}
<section class="mgd-ai-philosophy-admin">
    <h3>{{ $tc('mgd-ai-image-labels.philosophy.label') }}</h3>
    <p>{{ plainContent || $tc('mgd-ai-image-labels.philosophy.preview') }}</p>
</section>
{% endblock %}
`;var He=Object.freeze({"de-DE":"<h2>Unser Umgang mit KI-Bildern</h2><p>Wir kennzeichnen Bilder transparent, wenn sie vollst\xE4ndig oder teilweise mit k\xFCnstlicher Intelligenz erstellt oder bearbeitet wurden.</p><p>So k\xF6nnen Sie Inhalte bewusst einordnen. Die Kennzeichnung ver\xE4ndert weder Produktinformationen noch Ihre Privatsph\xE4re.</p>","en-GB":"<h2>Our approach to AI imagery</h2><p>We label images transparently when they were created or edited wholly or partly with artificial intelligence.</p><p>This helps you assess content consciously. The label changes neither product information nor your privacy.</p>"});function se(i){try{let n=i()?.api?.languageId;return typeof n=="string"&&n.trim()!==""?n.trim():null}catch{return null}}function Ht(i){try{let n=i?.Store,d=n?.get;if(typeof d!="function")return null;let u=n.list;if(typeof u=="function"){let p=u.call(n);if(!Array.isArray(p)||!p.includes("context"))return null}return se(()=>d.call(n,"context"))}catch{return null}}function Kt(i=globalThis.Shopware){return Ht(i)??se(()=>i?.State?.get?.("context"))??se(()=>i?.Context)??null}function Gt(i){return typeof i?.locale?.code=="string"&&i.locale.code.trim()!==""?i.locale.code:"en-GB"}function Wt(i){let n=0;return{async load(d){let u=++n,p=null;try{p=typeof d=="string"&&d!==""?await i(d):null}catch{p=null}return u===n?Gt(p):null},cancel(){++n}}}var H={inject:["repositoryFactory"],data(){return{contentLocale:"en-GB",contentLanguageLocaleLoader:null}},computed:{contentLanguageId(){return Kt(Shopware)},languageRepository(){return this.repositoryFactory.create("language")}},watch:{contentLanguageId(i){this.refreshContentLocale(i)}},created(){this.contentLanguageLocaleLoader=Wt(async i=>{let n=new Shopware.Data.Criteria(1,1);return n.addAssociation("locale"),this.languageRepository.get(i,Shopware.Context.api,n)}),this.refreshContentLocale(this.contentLanguageId)},beforeDestroy(){this.contentLanguageLocaleLoader?.cancel()},beforeUnmount(){this.contentLanguageLocaleLoader?.cancel()},methods:{async refreshContentLocale(i){let n=await this.contentLanguageLocaleLoader.load(i);n!==null&&(this.contentLocale=n)}}};function K(i,n){return typeof i=="string"&&i.trim()!==""?i:typeof n=="string"&&/^de(?:[-_]|$)/i.test(n.trim())?He["de-DE"]:He["en-GB"]}var{Mixin:qt}=Shopware,Ke={template:Fe,mixins:[qt.getByName("cms-element"),H],computed:{plainContent(){return K(this.element?.config?.content?.value,this.contentLocale).replace(/<[^>]*>/g," ").replace(/\s+/g," ").trim()}},created(){this.initElementConfig("mgd-ai-philosophy")}};var Ge=`{% block sw_cms_element_config_mgd_ai_philosophy %}
<sw-cms-mapping-field
    v-model:config="element.config.content"
    :label="$tc('mgd-ai-image-labels.philosophy.contentLabel')"
    value-types="string"
>
    <sw-text-editor
        :value="resolvedContent"
        :allow-inline-data-mapping="true"
        :sanitize-info-warn="true"
        sanitize-input
        enable-transparent-background
        @update:value="onInput"
    />
</sw-cms-mapping-field>
{% endblock %}
`;var{Mixin:Xt}=Shopware,We={template:Ge,compatConfig:Shopware.compatConfig,emits:["element-update"],mixins:[Xt.getByName("cms-element"),H],computed:{resolvedContent(){return K(this.element?.config?.content?.value,this.contentLocale)}},created(){this.initElementConfig("mgd-ai-philosophy")},methods:{onInput(i){this.element.config.content.value=typeof i=="string"?i:"",this.$emit("element-update",this.element)}}};var qe=`{% block sw_cms_element_preview_mgd_ai_philosophy %}
<div class="sw-cms-el-preview-mgd-ai-philosophy">
    <strong>{{ $tc('mgd-ai-image-labels.philosophy.label') }}</strong>
    <p>{{ $tc('mgd-ai-image-labels.philosophy.preview') }}</p>
</div>
{% endblock %}
`;var Ve={template:qe};Shopware.Component.register("sw-cms-el-mgd-ai-philosophy",Ke);Shopware.Component.register("sw-cms-el-config-mgd-ai-philosophy",We);Shopware.Component.register("sw-cms-el-preview-mgd-ai-philosophy",Ve);Shopware.Service("cmsService").registerCmsElement({name:"mgd-ai-philosophy",label:"mgd-ai-image-labels.philosophy.label",component:"sw-cms-el-mgd-ai-philosophy",configComponent:"sw-cms-el-config-mgd-ai-philosophy",previewComponent:"sw-cms-el-preview-mgd-ai-philosophy",defaultConfig:{content:{source:"static",value:null}}});var Xe=`{% block mgd_ai_settings_index %}
<sw-page class="mgd-ai-settings-index">
    <template #smart-bar-header>
        <h2>{{ $tc('mgd-ai-image-labels.settings.title') }}</h2>
    </template>

    <template #content>
        <sw-card-view>
            <sw-card :title="$tc('mgd-ai-image-labels.settings.cardTitle')">
                <p>{{ $tc('mgd-ai-image-labels.settings.description') }}</p>
                <sw-alert variant="info">{{ $tc('mgd-ai-image-labels.settings.noPublishNotice') }}</sw-alert>
                <sw-button-process
                    v-if="canPrepare"
                    variant="primary"
                    :is-loading="isLoading"
                    :disabled="isLoading"
                    @click="preparePage"
                >
                    {{ $tc('mgd-ai-image-labels.settings.action') }}
                </sw-button-process>
                <sw-alert v-else variant="warning">{{ $tc('mgd-ai-image-labels.settings.missingPermission') }}</sw-alert>
                <router-link v-if="cmsPageRoute" :to="cmsPageRoute">
                    {{ $tc('mgd-ai-image-labels.settings.openEditor') }}
                </router-link>
            </sw-card>
        </sw-card-view>
    </template>
</sw-page>
{% endblock %}
`;var Jt="/_action/mgd-ai-image-labels/philosophy-page",de="Authentifizierter Administrationszugriff ist nicht verf\xFCgbar.",Qt="Die Philosophie-Seite konnte nicht vorbereitet werden.";async function Ze(i,n){if(typeof i?.post!="function"||typeof n?.getBasicHeaders!="function")throw new TypeError(de);let d;try{d=n.getBasicHeaders()}catch{throw new TypeError(de)}if(d===null||typeof d!="object"||Array.isArray(d))throw new TypeError(de);try{return await i.post(Jt,{},{headers:d})}catch{throw new Error(Qt)}}var{Mixin:ei}=Shopware,ti=/^[0-9a-f]{32}$/i;Shopware.Component.register("mgd-ai-settings-index",{template:Xe,inject:["acl","systemConfigApiService"],mixins:[ei.getByName("notification")],data(){return{isLoading:!1,cmsPageId:null}},computed:{canPrepare(){return["system_config:update","cms_page:create","cms_section:create","cms_block:create","cms_slot:create"].every(i=>this.acl.can(i))},cmsPageRoute(){return this.cmsPageId?{name:"sw.cms.detail",params:{id:this.cmsPageId}}:null}},methods:{async preparePage(){if(!(!this.canPrepare||this.isLoading)){this.isLoading=!0,this.cmsPageId=null;try{let i=Shopware.Application.getContainer("init").httpClient,n=await Ze(i,this.systemConfigApiService),d=n?.data?.cmsPageId;if(typeof n?.data?.created!="boolean"||typeof d!="string"||!ti.test(d))throw new TypeError("Unerwartete Antwortstruktur");this.cmsPageId=d,this.createNotificationSuccess({message:this.$tc(n.data.created?"mgd-ai-image-labels.settings.successCreated":"mgd-ai-image-labels.settings.successExisting")})}catch{this.createNotificationError({message:this.$tc("mgd-ai-image-labels.settings.error")})}finally{this.isLoading=!1}}}}});Shopware.Module.register("mgd-ai-settings",{type:"plugin",name:"MGD AI Image Labels",title:"mgd-ai-image-labels.settings.title",description:"mgd-ai-image-labels.settings.description",color:"#0870ff",icon:"regular-sparkles",routes:{index:{component:"mgd-ai-settings-index",path:"index",meta:{parentPath:"sw.settings.index",privilege:"system_config:update"}}},settingsItem:{group:"plugins",to:"mgd.ai.settings.index",icon:"regular-sparkles",privilege:"system_config:update"}});var _n=wt(Je());Shopware.Locale.extend("de-DE",Pe);Shopware.Locale.extend("en-GB",Le);})();
