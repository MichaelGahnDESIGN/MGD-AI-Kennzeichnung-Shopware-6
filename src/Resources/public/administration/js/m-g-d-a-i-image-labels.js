(()=>{var ve=Object.create;var R=Object.defineProperty;var xe=Object.getOwnPropertyDescriptor;var _e=Object.getOwnPropertyNames;var we=Object.getPrototypeOf,ye=Object.prototype.hasOwnProperty;var Se=(t,i)=>()=>{try{return i||t((i={exports:{}}).exports,i),i.exports}catch(n){throw i=0,n}};var Ee=(t,i,n,s)=>{if(i&&typeof i=="object"||typeof i=="function")for(let r of _e(i))!ye.call(t,r)&&r!==n&&R(t,r,{get:()=>i[r],enumerable:!(s=xe(i,r))||s.enumerable});return t};var Ce=(t,i,n)=>(n=t!=null?ve(we(t)):{},Ee(i||!t||!t.__esModule?R(n,"default",{value:t,enumerable:!0}):n,t));var pe=Se((me,E)=>{(function(t){"use strict";var i="mgd-ai-kennzeichnung-shopware",n="mgd-kit-"+i,s="/_action/mgd-kit/"+i,r=n+"-style",C=["https://theme.michael-gahn.de/","https://github.com/MichaelGahnDESIGN/"],h=["system.plugin_maintain","system.plugin_upload"],f=/^[a-f0-9]{32}\.webp$/,b=/^data:image\/webp;base64,[A-Za-z0-9+\/]+={0,2}$/,c=/^#[0-9a-fA-F]{6}$/,g=`/* Weitere Plugins JTL + Shopware - Kopie aus Platform/WordPress/assets/kit-catalog.css, gleiche Klassen  */
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
`,m={finishMissing:"Die neuen Dateien sind eingespielt. Bitte klicke unter Erweiterungen bei diesem Plugin auf Aktualisieren."},ue=[".mgd-kit-sw p{margin:0 0 10px;line-height:1.5}",".mgd-kit-sw .mgd-kit-note{padding:12px;border-left:4px solid #b45309;background:#fffbeb;color:#1f2d3d}",".mgd-kit-sw .mgd-kit-error{padding:12px;border-left:4px solid #b91c1c;background:#fef2f2;color:#1f2d3d}",".mgd-kit-sw .mgd-kit-ok{padding:12px;border-left:4px solid #15803d;background:#f0fdf4;color:#1f2d3d}",".mgd-kit-sw ol{margin:0 0 10px 20px}",".mgd-kit-sw .mgd-kit-small{font-size:13px;color:#3d4c5c}",".mgd-kit-sw label{display:block;margin:0 0 6px;font-weight:600}",".mgd-kit-sw input[type=password]{width:100%;max-width:420px;min-height:44px;padding:8px;border:1px solid #6b7c8d;border-radius:4px}",".mgd-kit-sw span.mgd-kit-catalog__hero-icon{display:block;width:94px;height:94px;overflow:hidden}",".mgd-kit-sw span.mgd-kit-catalog__hero-icon>*{display:block;width:100%;height:100%}",".mgd-kit-sw a:focus-visible,.mgd-kit-sw input:focus-visible{outline:3px solid #1d4ed8;outline-offset:2px}"].join(""),he=['<sw-page class="mgd-kit-sw">',"  <template #smart-bar-header><h2>{{ title }}</h2></template>","  <template #content>","    <sw-card-view>",'      <p v-if="isLoading" role="status">Wird geladen \u2026</p>','      <p v-else-if="loadError" class="mgd-kit-error" role="alert">Die Seite konnte gerade nicht geladen werden. Bitte lade sie neu.</p>',"      <template v-else>",'        <sw-card title="Info">',"          <p>Plugin: {{ status.name }}</p>",'          <p>Version: {{ updates.installed || "unbekannt" }}</p>',"        </sw-card>",'        <sw-card title="Updates">','          <p v-if="!updates.available">Die Update-Pr\xFCfung hat gerade nicht geklappt. Versuche es sp\xE4ter noch einmal.</p>','          <p v-else-if="!updates.newer">Du hast die aktuelle Version.</p>',"          <template v-else>","            <p>Version {{ updates.newest }} ist verf\xFCgbar.</p>",'            <p v-if="updates.changelog" class="mgd-kit-small">{{ updates.changelog }}</p>','            <sw-button v-if="status.canUpdate && !fallback" variant="primary" :disabled="isUpdating" :isLoading="isUpdating" @click="runUpdate">Jetzt aktualisieren</sw-button>','            <p v-if="!status.canUpdate && !fallback" class="mgd-kit-small">Den Knopf \u201EJetzt aktualisieren\u201C sehen nur Admins mit dem Recht, Erweiterungen hochzuladen und zu verwalten.</p>',"          </template>",'          <p v-if="updates.notice" class="mgd-kit-note">{{ updates.notice.text }} <a v-if="isLink(updates.notice.url)" :href="updates.notice.url" target="_blank" rel="noopener noreferrer">Zur aktuellen Version</a></p>',`          <p v-if="message" :class="messageKey === 'finishMissing' ? 'mgd-kit-note' : (messageOk ? 'mgd-kit-ok' : 'mgd-kit-error')" role="status">{{ message }}</p>`,'          <template v-if="fallback || (updates.newer && !status.canUpdate)">',"            <p>So aktualisierst du von Hand:</p>",'            <ol><li v-for="(step, i) in steps" :key="i">{{ step }}</li></ol>','            <p v-if="isLink(downloadLink)"><a :href="downloadLink" target="_blank" rel="noopener noreferrer">ZIP-Datei herunterladen</a></p>',"          </template>","        </sw-card>",'        <sw-card title="Weitere Plugins">','          <div class="mgd-kit-catalog">','            <header class="mgd-kit-catalog__hero"><div class="mgd-kit-catalog__hero-copy"><p class="mgd-kit-catalog__eyebrow"><span class="mgd-kit-catalog__brand">MGD</span> SHOPWARE \xB7 PLUGIN-KOLLEKTION</p>',"              <h3>Mehr aus deinem<br><em>Shop machen.</em></h3><p>Durchdachte Erweiterungen von Michael Gahn DESIGN.<br>Entdecke die passenden Werkzeuge f\xFCr dein Shopware.</p></div>",'              <div class="mgd-kit-catalog__hero-art" aria-hidden="true"><span class="mgd-kit-catalog__orbit"></span>',`                <span v-for="(item, i) in heroItems" :key="item.slug" :class="'mgd-kit-catalog__hero-icon mgd-kit-catalog__hero-icon--' + i"><img v-if="mediaSrc(item.icon)" :src="mediaSrc(item.icon)" width="94" height="94" alt=""><svg v-else viewBox="0 0 128 128" width="94" height="94" aria-hidden="true" focusable="false"><rect width="128" height="128" rx="27" fill="#263b37"/><g fill="none" stroke="#e5c58e" stroke-width="5"><rect x="28" y="28" width="27" height="27" rx="5"/><rect x="28" y="73" width="27" height="27" rx="5"/><rect x="73" y="73" width="27" height="27" rx="5"/></g><path d="M86 27v28M72 41h28" stroke="#e6eee7" stroke-width="5" stroke-linecap="round"/></svg></span>`,'                <span class="mgd-kit-catalog__art-caption">GEMACHT F\xDCR DEIN SHOPWARE</span></div></header>','            <div class="mgd-kit-catalog__collection-bar"><p><strong>{{ recommendations.length }} Erweiterungen</strong><span>Installation nur von Hand</span></p></div>','            <p v-if="recStale" class="mgd-kit-catalog__stale">Die Liste konnte gerade nicht aktualisiert werden. Du siehst den letzten bekannten Stand.</p>','            <section class="mgd-kit-catalog__browser" aria-label="Plugins entdecken">','              <div v-if="recommendations.length" class="mgd-kit-catalog__toolbar">',`                <div class="mgd-kit-catalog__search"><label :for="uid + '-search'"><span aria-hidden="true">\u2315</span><span class="mgd-kit-catalog__sr">Plugins durchsuchen</span></label><input :id="uid + '-search'" v-model="search" type="search" placeholder="Was m\xF6chtest du verbessern?" autocomplete="off"></div>`,'                <label class="mgd-kit-catalog__select">Preis<select v-model="price"><option value="all">Alle Preise</option><option value="free">Kostenlos</option><option value="paid">Kostenpflichtig</option></select></label>',"              </div>",'              <div class="mgd-kit-catalog__browse-heading">',`                <div v-if="recommendations.length" class="mgd-kit-catalog__categories" role="group" aria-label="Kategorie filtern"><button type="button" :aria-pressed="String(category === 'all')" @click="category = 'all'">Alle entdecken</button><button v-for="c in categories" :key="c" type="button" :aria-pressed="String(category === c)" @click="category = c">{{ c }}</button></div>`,'                <p class="mgd-kit-catalog__count" aria-live="polite" aria-atomic="true">{{ countLabel }}</p>',"              </div>",'              <div class="mgd-kit-catalog__grid">','                <article v-for="item in filtered" :key="item.slug" class="mgd-kit-catalog__card" :style="accentStyle(item)">','                  <div class="mgd-kit-catalog__card-art">',`                    <img v-if="mediaSrc(item.image)" class="mgd-kit-catalog__card-background" :src="mediaSrc(item.image)" width="1080" height="360" :alt="item.imageAlt || ''" :aria-hidden="item.imageAlt ? null : 'true'">`,'                    <span class="mgd-kit-catalog__category">{{ item.category }}</span>','                    <img v-if="mediaSrc(item.icon)" class="mgd-kit-catalog__card-icon" :src="mediaSrc(item.icon)" width="78" height="78" alt=""><svg v-else class="mgd-kit-catalog__card-icon" viewBox="0 0 128 128" width="78" height="78" aria-hidden="true" focusable="false"><rect width="128" height="128" rx="27" fill="#263b37"/><g fill="none" stroke="#e5c58e" stroke-width="5"><rect x="28" y="28" width="27" height="27" rx="5"/><rect x="28" y="73" width="27" height="27" rx="5"/><rect x="73" y="73" width="27" height="27" rx="5"/></g><path d="M86 27v28M72 41h28" stroke="#e6eee7" stroke-width="5" stroke-linecap="round"/></svg>','                    <span v-if="item.version" class="mgd-kit-catalog__version">v{{ item.version }}</span>',"                  </div>",'                  <div class="mgd-kit-catalog__card-body">',`                    <div class="mgd-kit-catalog__card-heading"><h4>{{ item.name }}</h4><span class="mgd-kit-catalog__price">{{ item.tier === 'free' ? 'Kostenlos' : 'Kostenpflichtig' }}</span></div>`,'                    <p class="mgd-kit-catalog__description">{{ item.description }}</p>','                    <ul v-if="features(item).length" class="mgd-kit-catalog__features"><li v-for="(f, j) in features(item)" :key="j">{{ f }}</li></ul>',`                    <div class="mgd-kit-catalog__card-status"><span :class="'mgd-kit-catalog__status mgd-kit-catalog__status--' + stateOf(item)">{{ statusLabel(item) }}</span></div>`,'                    <div class="mgd-kit-catalog__actions">',`                      <a class="mgd-kit-catalog__details" :href="detailsUrl(item)" target="_blank" rel="noopener noreferrer" aria-haspopup="dialog" :aria-label="'Details zu ' + item.name" @click="openDetails(item, $event)">Details <span aria-hidden="true">\u2197</span></a>`,`                      <a class="mgd-kit-catalog__button" :href="detailsUrl(item)" target="_blank" rel="noopener noreferrer" :aria-label="item.name + ' ansehen (neuer Tab)'">Ansehen</a>`,"                    </div>","                  </div>","                </article>","              </div>",'              <div v-if="filtered.length === 0" class="mgd-kit-catalog__empty"><span aria-hidden="true">\u2315</span>','                <h4>{{ recommendations.length ? "Noch nicht das Richtige dabei?" : "Hier ist gerade nichts gelistet." }}</h4>','                <p>{{ recommendations.length ? "Versuche einen anderen Suchbegriff oder setze die Filter zur\xFCck." : "Bitte schau sp\xE4ter noch einmal vorbei." }}</p>','                <button v-if="recommendations.length" class="mgd-kit-catalog__button" type="button" @click="resetFilters">Filter zur\xFCcksetzen</button>',"              </div>","            </section>",'            <footer class="mgd-kit-catalog__footer"><p><strong>Michael Gahn DESIGN</strong><span>Werkzeuge mit einem klaren Zweck.</span></p>','              <p>{{ recCentral ? "Katalog von theme.michael-gahn.de" : "Ersatzkatalog aktiv \xB7 Zentraler Feed derzeit nicht erreichbar" }}<template v-if="recChecked"><br><span>Gepr\xFCft: {{ recChecked }} \xB7 Zwischenspeicherung bis zu 15 Minuten</span></template></p></footer>',`            <dialog ref="dialog" class="mgd-kit-catalog__dialog" :aria-labelledby="uid + '-dialog-title'" @close="onDialogClose" @click="onDialogClick">`,'              <div class="mgd-kit-catalog__dialog-header"><p>MGD PLUGIN-KOLLEKTION</p><button ref="dialogClose" type="button" aria-label="Details schlie\xDFen" @click="closeDetails">\xD7</button></div>',`              <h2 :id="uid + '-dialog-title'">{{ dialogItem ? dialogItem.name : "Plugin-Details" }}</h2>`,'              <div v-if="dialogItem">',"                <p>{{ dialogItem.description }}</p>",'                <ul v-if="features(dialogItem).length"><li v-for="(f, j) in features(dialogItem)" :key="j">{{ f }}</li></ul>','                <p>{{ dialogItem.tier === "free" ? "Kostenlos erh\xE4ltlich." : "Kostenpflichtige Erweiterung. Informationen und Konditionen findest du beim Anbieter." }}</p>','                <a class="mgd-kit-catalog__button" :href="detailsUrl(dialogItem)" target="_blank" rel="noopener noreferrer">Mehr erfahren <span class="mgd-kit-catalog__sr">(neuer Tab)</span></a>',"              </div>","            </dialog>","          </div>","        </sw-card>",'        <sw-card v-if="status.install" title="Installation melden">','          <sw-switch-field :label="status.install.label" :value="status.install.enabled" :disabled="isSaving" @update:value="setInstall" @change="setInstall"></sw-switch-field>','          <p class="mgd-kit-small">{{ status.install.hint }}</p>','          <p class="mgd-kit-small" role="status">{{ status.install.status }}</p>',"        </sw-card>",'        <sw-card v-if="status.license" title="Lizenz">','          <label :for="licenseId">Lizenzschl\xFCssel</label>','          <input :id="licenseId" v-model="licenseToken" type="password" autocomplete="off" spellcheck="false">','          <p v-if="status.license.hasToken" class="mgd-kit-small">Eingetragen ist ein Schl\xFCssel, der auf {{ status.license.hint }} endet.</p>','          <p role="status">{{ status.license.status }}</p>','          <p v-if="licenseMessage" role="status">{{ licenseMessage }}</p>',`          <sw-button variant="primary" :disabled="isSaving" @click="saveLicense('save')">Speichern</sw-button>`,`          <sw-button v-if="status.license.hasToken" :disabled="isSaving" @click="saveLicense('remove')">Entfernen</sw-button>`,'          <p class="mgd-kit-small">Zur Pr\xFCfung gehen nur der Lizenzschl\xFCssel und die Domain deines Shops an theme.michael-gahn.de.</p>',"        </sw-card>","      </template>","    </sw-card-view>","  </template>","</sw-page>"].join(`
`);function $(e){return typeof e=="string"&&e.length<500&&!/[\s"'<>\\]/.test(e)&&C.some(function(a){return e.indexOf(a)===0})}function z(e){return String(e||"").toLocaleLowerCase("de").normalize("NFD").replace(/[\u0300-\u036f]/g,"").replace(/ß/g,"ss")}function fe(e){var a=new Date(e*1e3),o=function(l){return(l<10?"0":"")+l};return o(a.getUTCDate())+"."+o(a.getUTCMonth()+1)+"."+a.getUTCFullYear()+" \xB7 "+o(a.getUTCHours())+":"+o(a.getUTCMinutes())}function M(e){var a=e&&typeof e.slug=="string"&&/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(e.slug)?e.slug:"";return"https://theme.michael-gahn.de/plugins/"+a}function I(e){return typeof e=="string"&&f.test(e)?e:""}function P(e){return typeof e=="string"&&e.length<1e6&&b.test(e)}function be(e){if(!(!e||e.getElementById(r))){var a=e.createElement("style");a.id=r,a.textContent=ue+(g.indexOf("{{")===0?"":g),e.head.appendChild(a)}}var j={template:he,inject:["loginService","acl"],data:function(){return{isLoading:!0,loadError:!1,isUpdating:!1,isSaving:!1,status:{},recommendations:[],recStale:!1,recCentral:!0,recChecked:"",media:{},search:"",price:"all",category:"all",dialogItem:null,trigger:null,message:"",messageKey:"",messageOk:!1,fallback:null,licenseToken:"",licenseMessage:""}},computed:{title:function(){return"MGD AI Kennzeichnung"},updates:function(){return this.status.updates||{}},steps:function(){var e=this.fallback&&Array.isArray(this.fallback.steps)?this.fallback.steps:this.status.fallbackSteps;return Array.isArray(e)?e:[]},downloadLink:function(){return this.fallback&&this.fallback.url?this.fallback.url:this.updates.downloadUrl},licenseId:function(){return n+"-license"},uid:function(){return n+"-cat"},heroItems:function(){return this.recommendations.slice(0,3)},categories:function(){var e=[];return this.recommendations.forEach(function(a){typeof a.category=="string"&&a.category&&e.indexOf(a.category)<0&&e.push(a.category)}),e},filtered:function(){var e=this,a=z(e.search).trim().split(/\s+/).filter(Boolean);return e.recommendations.filter(function(o){var l=z([o.name,o.description,o.category].concat(e.features(o)).join(" "));return(e.category==="all"||o.category===e.category)&&(e.price==="all"||o.tier===e.price)&&a.every(function(u){return l.indexOf(u)>=0})})},countLabel:function(){var e=this.filtered.length;return e+" "+(e===1?"Plugin":"Plugins")}},created:function(){be(t.document),this.load()},methods:{isLink:$,detailsUrl:M,setRecommendations:function(e){var a=Array.isArray(e.items)?e.items:[];this.recommendations=a.filter(function(o){return o&&typeof o.slug=="string"&&/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(o.slug)&&typeof o.name=="string"}),this.recStale=e.source==="stale"||e.source==="fallback",this.recCentral=e.source==="fresh"||e.source==="cache"||e.source==="stale",this.recChecked=typeof e.checkedAt=="number"&&e.checkedAt>0?fe(e.checkedAt):""},loadMedia:function(){var e=this,a=[];return e.recommendations.forEach(function(o){[o.icon,o.image].forEach(function(l){I(l)&&a.indexOf(l)<0&&a.push(l)})}),a.reduce(function(o,l){return o.then(function(){return e.http().get(s+"/media/"+l,e.opts()).then(function(u){var U=u&&u.data?u.data.data:"";if(P(U)){var N=Object.assign({},e.media);N[l]=U,e.media=N}}).catch(function(){})})},Promise.resolve())},mediaSrc:function(e){var a=I(e);return a&&P(this.media[a])?this.media[a]:""},features:function(e){return e&&Array.isArray(e.features)?e.features.filter(function(a){return typeof a=="string"}).slice(0,3):[]},accentStyle:function(e){return e&&typeof e.accent=="string"&&c.test(e.accent)?{"--mgd-kit-cat-accent":e.accent}:{}},stateOf:function(e){return e&&(e.state==="installed"||e.state==="update")?e.state:"available"},statusLabel:function(e){return e&&e.active===!0?"Aktiv":e&&e.installed===!0?"Installiert \xB7 inaktiv":"F\xFCr deinen Shop"},resetFilters:function(){this.search="",this.price="all",this.category="all"},openDetails:function(e,a){var o=this.$refs?this.$refs.dialog:null;!o||typeof o.showModal!="function"||(a&&(a.preventDefault(),this.trigger=a.currentTarget||null),this.dialogItem=e,o.showModal(),this.$refs.dialogClose&&this.$refs.dialogClose.focus())},closeDetails:function(){this.$refs&&this.$refs.dialog&&this.$refs.dialog.close()},onDialogClick:function(e){e&&this.$refs&&e.target===this.$refs.dialog&&this.closeDetails()},onDialogClose:function(){this.trigger&&typeof this.trigger.focus=="function"&&this.trigger.focus()},http:function(){return Shopware.Application.getContainer("init").httpClient},opts:function(){return{headers:{Authorization:"Bearer "+this.loginService.getToken(),"Content-Type":"application/json"}}},load:function(){var e=this;return e.isLoading=!0,e.http().get(s+"/status",e.opts()).then(function(a){return e.status=a&&a.data?a.data:{},e.loadError=!1,e.http().get(s+"/recommendations",e.opts()).then(function(o){return e.setRecommendations(o&&o.data?o.data:{}),e.loadMedia()}).catch(function(){e.recommendations=[],e.recStale=!0,e.recCentral=!1})}).catch(function(){e.loadError=!0}).then(function(){e.isLoading=!1})},runUpdate:function(){var e=this;return e.isUpdating||!h.every(function(a){return e.acl.can(a)})?null:(e.isUpdating=!0,e.message="",e.fallback=null,e.http().post(s+"/update",{},e.opts()).then(function(a){var o=a&&a.data?a.data:{};return o.ok===!0&&o.step==="uploaded"?(e.show(o),e.http().post(s+"/update/finish",{},e.opts()).then(function(l){e.show(l&&l.data?l.data:{})}).catch(function(l){if(l&&l.response&&l.response.status===404)return e.showKey("finishMissing"),null;throw l})):(e.show(o),null)}).catch(function(a){e.show(a&&a.response&&a.response.data?a.response.data:{})}).then(function(){e.isUpdating=!1}))},showKey:function(e){this.messageKey=e,this.messageOk=!1,this.message=m[e]||"",this.fallback=null},show:function(e){this.messageKey="",this.messageOk=e.ok===!0,this.message=typeof e.message=="string"?e.message:"Das Update hat nicht geklappt.",this.fallback=e.ok===!0?null:e.fallback||{url:this.updates.downloadUrl,steps:this.status.fallbackSteps}},setInstall:function(e){var a=this;return typeof e!="boolean"||!a.status.install||e===a.status.install.enabled||a.isSaving?null:(a.isSaving=!0,a.http().post(s+"/install-report",{enabled:e},a.opts()).then(function(o){o&&o.data&&o.data.install&&(a.status.install=o.data.install)}).catch(function(){a.status.install=Object.assign({},a.status.install,{status:"Das Speichern hat nicht geklappt."})}).then(function(){a.isSaving=!1}))},saveLicense:function(e){var a=this;return a.isSaving=!0,a.http().post(s+"/license",{action:e,token:e==="save"?a.licenseToken:""},a.opts()).then(function(o){var l=o&&o.data?o.data:{};a.licenseMessage=l.message||"",l.license&&(a.status.license=l.license)}).catch(function(o){var l=o&&o.response&&o.response.data?o.response.data:{};a.licenseMessage=l.message||"Das hat nicht geklappt."}).then(function(){a.licenseToken="",a.isSaving=!1})}}},ke={PRODUCT:i,MODULE:n,TEXTS:m,CATALOG_CSS:g,isLink:$,detailsUrl:M,mediaName:I,isDataUri:P,normalize:z,component:j};typeof E<"u"&&E.exports&&(E.exports=ke),!(typeof Shopware>"u")&&(Shopware.Component.register(n+"-page",j),Shopware.Module.register(n,{type:"plugin",name:n,title:"MGD AI Kennzeichnung",description:"Updates, weitere Plugins und Einstellungen",color:"#1f2d3d",icon:"regular-cog",routes:{index:{component:n+"-page",path:"index",meta:{parentPath:"sw.settings.index.plugins",privilege:"system_config:read"}}},settingsItem:[{name:n,to:n.replace(/-/g,".")+".index",label:"MGD AI Kennzeichnung",group:"plugins",icon:"regular-cog",privilege:"system_config:read"}]}))})(typeof window<"u"?window:me)});var H=`<sw-media-collapse v-if="images.length || busy" :title="$t('mgd-ai-image-labels.bulk.title')" :expand-on-loading="true">
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
`;var v={status:["none","generated","partially-generated","modified","deepfake"],position:["top-left","top-right","bottom-left","bottom-right"],theme:["auto","light","dark"]};function k(t){return[...new Map(t.filter(i=>i.getEntityName?.()==="media"&&i.mediaType?.name==="IMAGE").map(i=>[i.id,i])).values()]}function T(t){let i={};for(let[n,s]of Object.entries(v)){let r=t[n];if(!(r===""||r===void 0)){if(!s.includes(r))throw new Error("Invalid label value");i[`mgd_ai_${n}`]=r}}return i}async function K({items:t,values:i,repository:n,context:s,canEdit:r,onProgress:C=()=>{}}){if(!r())throw new Error("Missing media permission");let h=T(i);if(!Object.keys(h).length)throw new Error("Empty label change");let f=k(t),b={...s},c={saved:[],failed:[]};for(let g of f){try{if(!r())throw new Error("Missing media permission");let m=await n.get(g.id,b);if(!k([m]).length)throw new Error("Image no longer available");m.customFields={...m.customFields??{},...h},await n.save(m,b),c.saved.push(g.id)}catch{c.failed.push(g.id)}C(c.saved.length+c.failed.length,f.length)}return c}Shopware.Component.register("mgd-ai-media-bulk",{template:H,inject:["repositoryFactory","acl"],props:{items:{type:Array,required:!0}},data(){return{values:{status:"",position:"",theme:""},busy:!1,progress:"",outcome:""}},computed:{images(){return k(this.items)},allowed(){return this.acl.can("media.editor")},changed(){return Object.keys(T(this.values)).length>0},fields(){return Object.keys(v)},options(){return v},selectionKey(){return this.images.map(t=>t.id).sort().join(",")}},watch:{selectionKey(){this.busy||(this.outcome="",this.values={status:"",position:"",theme:""})}},methods:{label(t,i){let n=i.replace(/-([a-z])/g,(s,r)=>r.toUpperCase());return this.$t(`mgd-ai-image-labels.preview.${t}.${n}`)},async apply(){if(!(this.busy||!this.allowed||!this.changed||!this.images.length)){this.busy=!0,this.outcome="",this.progress="";try{let t=await K({items:this.items,values:this.values,repository:this.repositoryFactory.create("media"),context:Shopware.Context.api,canEdit:()=>this.acl.can("media.editor"),onProgress:(i,n)=>{this.progress=`${i} / ${n}`}});this.outcome=this.$t("mgd-ai-image-labels.bulk.result",{saved:t.saved.length,failed:t.failed.length}),this.values={status:"",position:"",theme:""}}catch{this.outcome=this.$t("mgd-ai-image-labels.bulk.error")}finally{this.busy=!1,this.progress=""}}}}});var F=`{% block sw_media_quickinfo_multiple_file_names %}
    <mgd-ai-media-bulk v-if="editable" :items="items" />
    {% parent %}
{% endblock %}
`;Shopware.Component.override("sw-media-quickinfo-multiple",{template:F});var G={"mgd-ai-image-labels":{preview:{title:"Vorschau der KI-Bildkennzeichnung",helpText:"Die Vorschau wird nur lokal aktualisiert. Speichern Sie \xC4nderungen mit der vorhandenen Shopware-Speicheraktion der Custom Fields.",imageAlt:"Lokale Vorschau des ausgew\xE4hlten Bildmediums",missingImage:"F\xFCr dieses Bild ist noch keine lokale Vorschau verf\xFCgbar.",announcement:"Vorschau: Status {status}, Position {position}, Darstellung {theme}.",status:{none:"KEINE SICHTBARE KENNZEICHNUNG",generated:"KI-GENERIERT",partiallyGenerated:"TEILWEISE KI-GENERIERT",modified:"MIT KI BEARBEITET",deepfake:"KI-DEEPFAKE"},position:{topLeft:"oben links",topRight:"oben rechts",bottomLeft:"unten links",bottomRight:"unten rechts"},theme:{auto:"automatisch",light:"hell",dark:"dunkel"}},philosophy:{label:"Unsere Haltung zu KI-Bildern",preview:"Transparente Informationen zur Erstellung und Bearbeitung von Bildern.",contentLabel:"Inhalt",defaultContent:"<h2>Unser Umgang mit KI-Bildern</h2><p>Wir kennzeichnen Bilder transparent, wenn sie vollst\xE4ndig oder teilweise mit k\xFCnstlicher Intelligenz erstellt oder bearbeitet wurden.</p><p>So k\xF6nnen Sie Inhalte bewusst einordnen. Die Kennzeichnung ver\xE4ndert weder Produktinformationen noch Ihre Privatsph\xE4re.</p>"},settings:{title:"MGD KI-Bildkennzeichnung",description:"Erstellen Sie einmalig ein unverkn\xFCpftes Erlebniswelten-Layout mit Ihrer zweisprachigen AI-Philosophie.",cardTitle:"AI-Philosophie als Erlebniswelt",noPublishNotice:"Die Aktion ver\xF6ffentlicht oder verkn\xFCpft nichts. Pr\xFCfen, bearbeiten und ver\xF6ffentlichen Sie das Layout anschlie\xDFend bewusst in den Erlebniswelten.",action:"Erlebniswelt vorbereiten",missingPermission:"F\xFCr diese Aktion fehlen Rechte f\xFCr Systemkonfiguration, CMS-Seiten, Sektionen, Bl\xF6cke oder Elemente.",successCreated:"Die Erlebniswelt wurde sicher vorbereitet.",successExisting:"Die bereits vorhandene plugin-eigene Erlebniswelt wurde gefunden.",error:"Die Erlebniswelt konnte nicht vorbereitet werden. Bitte pr\xFCfen Sie Berechtigung und Systemprotokoll.",openEditor:"In Erlebniswelten \xF6ffnen"},cms:{background:{label:"Gekennzeichnetes Hintergrundbild",preview:"Lokales Hintergrundbild mit KI-Kennzeichnung",contentPlaceholder:"Inhalte des Erlebniswelten-Blocks",fields:{media:"Bildmedium",minHeight:"Mindesth\xF6he",horizontalPosition:"Horizontale Bildposition",verticalPosition:"Vertikale Bildposition",decorative:"Bild ist rein dekorativ",fallbackColor:"Sichere Hintergrundfarbe",altText:"Alternativtext",altTextHelp:"Beschreiben Sie knapp den Inhalt oder Zweck des Bildes. Lassen Sie das Feld nur bei ausdr\xFCcklich dekorativen Bildern leer."},validation:{imageOnly:"Bitte w\xE4hlen Sie ausschlie\xDFlich ein Bildmedium. Dokumente, Videos und andere Dateien sind nicht zul\xE4ssig.",missingAltText:"F\xFCr ein nicht dekoratives Bild fehlt ein Alternativtext. Ohne Beschreibung k\xF6nnen blinde Nutzer den Bildinhalt nicht erfassen."},options:{minHeight:{"240":"Kompakt (240 px)","320":"Standard (320 px)","480":"Gro\xDF (480 px)","640":"Sehr gro\xDF (640 px)"},horizontalPosition:{left:"Links",center:"Mitte",right:"Rechts"},verticalPosition:{top:"Oben",center:"Mitte",bottom:"Unten"},fallbackColor:{"neutral-light":"Neutral hell","neutral-dark":"Neutral dunkel",brand:"Blau"}}}},bulk:{title:"KI-Kennzeichnung",selection:"{count} Bilder ausgew\xE4hlt",help:"Nur ausgew\xE4hlte Bilder werden ge\xE4ndert. Ordner und andere Dateien bleiben unver\xE4ndert. Die \xC4nderungen gelten f\xFCr die oben gew\xE4hlte Sprache.",status:"Kennzeichnung",position:"Position",theme:"Darstellung",unchanged:"Unver\xE4ndert lassen",apply:"Auf {count} Bilder anwenden",permission:"Zum \xC4ndern ist das Recht \u201EMedien bearbeiten\u201C erforderlich.",result:"{saved} Bilder gespeichert, {failed} fehlgeschlagen. Bei Fehlern die betroffenen Bilder pr\xFCfen und erneut ausw\xE4hlen.",error:"Die \xC4nderungen konnten nicht gespeichert werden. Bitte Berechtigungen und Verbindung pr\xFCfen."}}};var W={"mgd-ai-image-labels":{preview:{title:"AI image label preview",helpText:"The preview updates locally only. Save changes with the existing Shopware custom-field save action.",imageAlt:"Local preview of the selected image medium",missingImage:"No local preview is available for this image yet.",announcement:"Preview: status {status}, position {position}, appearance {theme}.",status:{none:"NO VISIBLE LABEL",generated:"AI GENERATED",partiallyGenerated:"AI PARTIALLY GENERATED",modified:"AI MODIFIED",deepfake:"AI DEEPFAKE"},position:{topLeft:"top left",topRight:"top right",bottomLeft:"bottom left",bottomRight:"bottom right"},theme:{auto:"automatic",light:"light",dark:"dark"}},philosophy:{label:"Our approach to AI imagery",preview:"Transparent information about how images are created and edited.",contentLabel:"Content",defaultContent:"<h2>Our approach to AI imagery</h2><p>We label images transparently when they were created or edited wholly or partly with artificial intelligence.</p><p>This helps you assess content consciously. The label changes neither product information nor your privacy.</p>"},settings:{title:"MGD AI Image Labels",description:"Create an unassigned Shopping Experiences layout containing your bilingual AI philosophy once.",cardTitle:"AI philosophy Shopping Experience",noPublishNotice:"This action publishes or assigns nothing. Review, edit and publish the layout consciously in Shopping Experiences afterwards.",action:"Prepare Shopping Experience",missingPermission:"This action requires permissions for system configuration, CMS pages, sections, blocks and elements.",successCreated:"The Shopping Experience was prepared safely.",successExisting:"The existing plugin-owned Shopping Experience was found.",error:"The Shopping Experience could not be prepared. Please check your permission and the system log.",openEditor:"Open in Shopping Experiences"},cms:{background:{label:"Labeled background image",preview:"Local background image with AI label",contentPlaceholder:"Shopping Experiences block content",fields:{media:"Image medium",minHeight:"Minimum height",horizontalPosition:"Horizontal image position",verticalPosition:"Vertical image position",decorative:"Image is purely decorative",fallbackColor:"Safe background colour",altText:"Alternative text",altTextHelp:"Briefly describe the image content or purpose. Leave this field empty only for explicitly decorative images."},validation:{imageOnly:"Please select an image medium only. Documents, videos and other files are not permitted.",missingAltText:"A non-decorative image is missing alternative text. Without a description, blind users cannot understand the image content."},options:{minHeight:{"240":"Compact (240 px)","320":"Standard (320 px)","480":"Large (480 px)","640":"Extra large (640 px)"},horizontalPosition:{left:"Left",center:"Centre",right:"Right"},verticalPosition:{top:"Top",center:"Centre",bottom:"Bottom"},fallbackColor:{"neutral-light":"Neutral light","neutral-dark":"Neutral dark",brand:"Blue"}}}},bulk:{title:"AI labels",selection:"{count} images selected",help:"Only selected images are changed. Folders and other files remain unchanged. Changes apply to the language selected above.",status:"Label",position:"Position",theme:"Appearance",unchanged:"Leave unchanged",apply:"Apply to {count} images",permission:"The media editing permission is required.",result:"{saved} images saved, {failed} failed. If errors occur, check the affected images and select them again.",error:"Changes could not be saved. Please check permissions and connection."}}};var V=`{% block mgd_ai_media_preview %}
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
`;var Ae=Object.freeze(["none","generated","partially-generated","modified","deepfake"]),De=Object.freeze(["top-left","top-right","bottom-left","bottom-right"]),Oe=Object.freeze(["auto","light","dark"]),Be=Object.freeze({"top-left":"is--top-left","top-right":"is--top-right","bottom-left":"is--bottom-left","bottom-right":"is--bottom-right"}),$e=Object.freeze({auto:"is--auto",light:"is--light",dark:"is--dark"}),Me=Object.freeze({none:"mgd-ai-image-labels.preview.status.none",generated:"mgd-ai-image-labels.preview.status.generated","partially-generated":"mgd-ai-image-labels.preview.status.partiallyGenerated",modified:"mgd-ai-image-labels.preview.status.modified",deepfake:"mgd-ai-image-labels.preview.status.deepfake"}),je=Object.freeze({"top-left":"mgd-ai-image-labels.preview.position.topLeft","top-right":"mgd-ai-image-labels.preview.position.topRight","bottom-left":"mgd-ai-image-labels.preview.position.bottomLeft","bottom-right":"mgd-ai-image-labels.preview.position.bottomRight"}),Ue=Object.freeze({auto:"mgd-ai-image-labels.preview.theme.auto",light:"mgd-ai-image-labels.preview.theme.light",dark:"mgd-ai-image-labels.preview.theme.dark"});function L(t={}){let i=typeof t=="object"&&t!==null?t:{},n=Ae.includes(i.status)?i.status:"none",s=De.includes(i.position)?i.position:"bottom-right",r=Oe.includes(i.theme)?i.theme:"auto";return{status:n,position:s,theme:r,visible:n!=="none"}}function q(t={}){let i=L(t);return{positionClass:Be[i.position],themeClass:$e[i.theme],labelSnippet:Me[i.status],positionSnippet:je[i.position],themeSnippet:Ue[i.theme]}}Shopware.Component.register("mgd-ai-media-preview",{template:V,props:{item:{type:Object,required:!0}},computed:{previewInput(){let t=this.item.customFields??{};return{status:t.mgd_ai_status,position:t.mgd_ai_position,theme:t.mgd_ai_theme}},previewState(){return L(this.previewInput)},previewPresentation(){return q(this.previewState)}}});var Z=`{% block sw_media_quickinfo_custom_field_sets %}
    {% parent %}

    <mgd-ai-media-preview
        v-if="item.mediaType && item.mediaType.name === 'IMAGE'"
        :item="item"
    />
{% endblock %}
`;Shopware.Component.override("sw-media-quickinfo",{template:Z});var Y=`<div class="mgd-ai-background-image" :class="presentationClasses">
    <img v-if="mediaSource" class="mgd-ai-background-image__media" :src="mediaSource" alt="">
    <div class="mgd-ai-background-image__content">
        {{ $tc('mgd-ai-image-labels.cms.background.contentPlaceholder') }}
    </div>
</div>
`;var d=Object.freeze({minHeight:Object.freeze(["240px","320px","480px","640px"]),horizontalPosition:Object.freeze(["left","center","right"]),verticalPosition:Object.freeze(["top","center","bottom"]),fallbackColor:Object.freeze(["neutral-light","neutral-dark","brand"])}),x=Object.freeze({minHeight:"320px",horizontalPosition:"center",verticalPosition:"center",fallbackColor:"neutral-light",decorative:!1,altText:""});function A(t){return t===!0}function w(t){return typeof t!="string"?"":[...t.replace(/<(script|style)\b[^>]*>[\s\S]*?<\/\1\s*>/giu,"").replace(/<[^>]*>/gu,"").replace(/[\u0000-\u001F\u007F]+/gu," ").trim()].slice(0,512).join("")}function J(t,i){let n=typeof i?.translated=="object"&&i.translated!==null?i.translated:{};for(let s of[t,n.alt,n.title]){let r=w(s);if(r!=="")return r}return""}function D(t){if(typeof t!="object"||t===null||typeof t.mimeType!="string"||!t.mimeType.startsWith("image/"))return!1;let i=t.mediaType;return typeof i=="object"&&i!==null&&typeof i.name=="string"&&i.name==="IMAGE"}function _(t,i,n){return typeof t=="string"&&i.includes(t)?t:n}function p(t={}){let i=typeof t=="object"&&t!==null?t:{};return{minHeight:_(i.minHeight,d.minHeight,x.minHeight),horizontalPosition:_(i.horizontalPosition,d.horizontalPosition,x.horizontalPosition),verticalPosition:_(i.verticalPosition,d.verticalPosition,x.verticalPosition),fallbackColor:_(i.fallbackColor,d.fallbackColor,x.fallbackColor),decorative:A(i.decorative),altText:w(i.altText)}}var{Mixin:He}=Shopware,X={template:Y,mixins:[He.getByName("cms-element")],computed:{presentation(){return p({minHeight:this.element?.config?.minHeight?.value,horizontalPosition:this.element?.config?.horizontalPosition?.value,verticalPosition:this.element?.config?.verticalPosition?.value,fallbackColor:this.element?.config?.fallbackColor?.value,decorative:this.element?.config?.decorative?.value,altText:this.element?.config?.altText?.value})},presentationClasses(){return[`mgd-ai-background-image--height-${this.presentation.minHeight.replace("px","")}`,`mgd-ai-background-image--horizontal-${this.presentation.horizontalPosition}`,`mgd-ai-background-image--vertical-${this.presentation.verticalPosition}`,`mgd-ai-background-image--fallback-${this.presentation.fallbackColor}`]},mediaSource(){return this.element?.data?.media?.url??null}},created(){this.initElementConfig("mgd-ai-background-image"),this.initElementData("mgd-ai-background-image")}};var Q=`<div class="sw-cms-el-config-mgd-ai-background-image">
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
`;var{Mixin:Fe}=Shopware,ee={template:Q,compatConfig:Shopware.compatConfig,inject:["repositoryFactory"],emits:["element-update"],mixins:[Fe.getByName("cms-element")],data(){return{mediaModalIsOpen:!1,selectionError:!1}},computed:{mediaRepository(){return this.repositoryFactory.create("media")},uploadTag(){return`mgd-ai-background-image-${this.element.id}`},previewSource(){return this.element?.data?.media??this.element?.config?.media?.value??null},minHeightOptions(){return this.options("minHeight",d.minHeight)},horizontalOptions(){return this.options("horizontalPosition",d.horizontalPosition)},verticalOptions(){return this.options("verticalPosition",d.verticalPosition)},fallbackOptions(){return this.options("fallbackColor",d.fallbackColor)},missingAltText(){return this.element?.config?.decorative?.value!==!0&&J(this.element?.config?.altText?.value,this.element?.data?.media)===""}},created(){this.initElementConfig("mgd-ai-background-image"),this.normalizeElementConfig()},methods:{options(t,i){return i.map(n=>({value:n,label:this.$tc(`mgd-ai-image-labels.cms.background.options.${t}.${n.replace("px","")}`)}))},normalizeElementConfig(){let t=p({minHeight:this.element.config.minHeight.value,horizontalPosition:this.element.config.horizontalPosition.value,verticalPosition:this.element.config.verticalPosition.value,fallbackColor:this.element.config.fallbackColor.value,decorative:this.element.config.decorative.value,altText:this.element.config.altText.value});for(let[i,n]of Object.entries(t))this.element.config[i].value=n},emitUpdate(){this.normalizeElementConfig(),this.$emit("element-update",this.element)},async onImageUpload({targetId:t}){let i=await this.mediaRepository.get(t,Shopware.Context.api);if(!D(i)){this.rejectMedia();return}this.setMedia(i)},onSelectionChanges(t){let i=Array.isArray(t)?t[0]:null;if(!i?.id||!D(i)){this.rejectMedia();return}this.setMedia(i)},setMedia(t){this.element.config.media.value=t.id,this.element.config.media.source="static",this.updateElementData(t),this.selectionError=!1,this.mediaModalIsOpen=!1,this.emitUpdate()},onImageRemove(){this.element.config.media.value=null,this.updateElementData(),this.selectionError=!1,this.emitUpdate()},rejectMedia(){this.selectionError=!0},onChangeDecorative(t){this.element.config.decorative.value=A(t),this.emitUpdate()},onChangeAltText(t){this.element.config.altText.value=w(t),this.emitUpdate()},updateElementData(t=null){let i=t===null?null:t.id;if(!this.element.data){this.isCompatEnabled("INSTANCE_SET")?this.$set(this.element,"data",{mediaId:i,media:t}):this.element.data={mediaId:i,media:t};return}this.isCompatEnabled("INSTANCE_SET")?(this.$set(this.element.data,"mediaId",i),this.$set(this.element.data,"media",t)):(this.element.data.mediaId=i,this.element.data.media=t)}}};var te=`<div class="mgd-ai-background-image-preview" :class="previewClass">
    <span>{{ $tc('mgd-ai-image-labels.cms.background.preview') }}</span>
</div>
`;var ie={template:te,computed:{previewClass(){return`mgd-ai-background-image--height-${p().minHeight.replace("px","")}`}}};Shopware.Component.register("sw-cms-el-mgd-ai-background-image",X);Shopware.Component.register("sw-cms-el-config-mgd-ai-background-image",ee);Shopware.Component.register("sw-cms-el-preview-mgd-ai-background-image",ie);Shopware.Service("cmsService").registerCmsElement({name:"mgd-ai-background-image",label:"mgd-ai-image-labels.cms.background.label",component:"sw-cms-el-mgd-ai-background-image",configComponent:"sw-cms-el-config-mgd-ai-background-image",previewComponent:"sw-cms-el-preview-mgd-ai-background-image",defaultConfig:{media:{source:"static",value:null,required:!0},minHeight:{source:"static",value:"320px"},horizontalPosition:{source:"static",value:"center"},verticalPosition:{source:"static",value:"center"},decorative:{source:"static",value:!1},fallbackColor:{source:"static",value:"neutral-light"},altText:{source:"static",value:""}}});var ae=`{% block sw_cms_element_mgd_ai_philosophy %}
<section class="mgd-ai-philosophy-admin">
    <h3>{{ $tc('mgd-ai-image-labels.philosophy.label') }}</h3>
    <p>{{ plainContent || $tc('mgd-ai-image-labels.philosophy.preview') }}</p>
</section>
{% endblock %}
`;var ne=Object.freeze({"de-DE":"<h2>Unser Umgang mit KI-Bildern</h2><p>Wir kennzeichnen Bilder transparent, wenn sie vollst\xE4ndig oder teilweise mit k\xFCnstlicher Intelligenz erstellt oder bearbeitet wurden.</p><p>So k\xF6nnen Sie Inhalte bewusst einordnen. Die Kennzeichnung ver\xE4ndert weder Produktinformationen noch Ihre Privatsph\xE4re.</p>","en-GB":"<h2>Our approach to AI imagery</h2><p>We label images transparently when they were created or edited wholly or partly with artificial intelligence.</p><p>This helps you assess content consciously. The label changes neither product information nor your privacy.</p>"});function O(t){try{let i=t()?.api?.languageId;return typeof i=="string"&&i.trim()!==""?i.trim():null}catch{return null}}function Ve(t){try{let i=t?.Store,n=i?.get;if(typeof n!="function")return null;let s=i.list;if(typeof s=="function"){let r=s.call(i);if(!Array.isArray(r)||!r.includes("context"))return null}return O(()=>n.call(i,"context"))}catch{return null}}function qe(t=globalThis.Shopware){return Ve(t)??O(()=>t?.State?.get?.("context"))??O(()=>t?.Context)??null}function Ze(t){return typeof t?.locale?.code=="string"&&t.locale.code.trim()!==""?t.locale.code:"en-GB"}function Ye(t){let i=0;return{async load(n){let s=++i,r=null;try{r=typeof n=="string"&&n!==""?await t(n):null}catch{r=null}return s===i?Ze(r):null},cancel(){++i}}}var y={inject:["repositoryFactory"],data(){return{contentLocale:"en-GB",contentLanguageLocaleLoader:null}},computed:{contentLanguageId(){return qe(Shopware)},languageRepository(){return this.repositoryFactory.create("language")}},watch:{contentLanguageId(t){this.refreshContentLocale(t)}},created(){this.contentLanguageLocaleLoader=Ye(async t=>{let i=new Shopware.Data.Criteria(1,1);return i.addAssociation("locale"),this.languageRepository.get(t,Shopware.Context.api,i)}),this.refreshContentLocale(this.contentLanguageId)},beforeDestroy(){this.contentLanguageLocaleLoader?.cancel()},beforeUnmount(){this.contentLanguageLocaleLoader?.cancel()},methods:{async refreshContentLocale(t){let i=await this.contentLanguageLocaleLoader.load(t);i!==null&&(this.contentLocale=i)}}};function S(t,i){return typeof t=="string"&&t.trim()!==""?t:typeof i=="string"&&/^de(?:[-_]|$)/i.test(i.trim())?ne["de-DE"]:ne["en-GB"]}var{Mixin:Je}=Shopware,oe={template:ae,mixins:[Je.getByName("cms-element"),y],computed:{plainContent(){return S(this.element?.config?.content?.value,this.contentLocale).replace(/<[^>]*>/g," ").replace(/\s+/g," ").trim()}},created(){this.initElementConfig("mgd-ai-philosophy")}};var re=`{% block sw_cms_element_config_mgd_ai_philosophy %}
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
`;var{Mixin:Qe}=Shopware,le={template:re,compatConfig:Shopware.compatConfig,emits:["element-update"],mixins:[Qe.getByName("cms-element"),y],computed:{resolvedContent(){return S(this.element?.config?.content?.value,this.contentLocale)}},created(){this.initElementConfig("mgd-ai-philosophy")},methods:{onInput(t){this.element.config.content.value=typeof t=="string"?t:"",this.$emit("element-update",this.element)}}};var se=`{% block sw_cms_element_preview_mgd_ai_philosophy %}
<div class="sw-cms-el-preview-mgd-ai-philosophy">
    <strong>{{ $tc('mgd-ai-image-labels.philosophy.label') }}</strong>
    <p>{{ $tc('mgd-ai-image-labels.philosophy.preview') }}</p>
</div>
{% endblock %}
`;var de={template:se};Shopware.Component.register("sw-cms-el-mgd-ai-philosophy",oe);Shopware.Component.register("sw-cms-el-config-mgd-ai-philosophy",le);Shopware.Component.register("sw-cms-el-preview-mgd-ai-philosophy",de);Shopware.Service("cmsService").registerCmsElement({name:"mgd-ai-philosophy",label:"mgd-ai-image-labels.philosophy.label",component:"sw-cms-el-mgd-ai-philosophy",configComponent:"sw-cms-el-config-mgd-ai-philosophy",previewComponent:"sw-cms-el-preview-mgd-ai-philosophy",defaultConfig:{content:{source:"static",value:null}}});var ce=`{% block mgd_ai_settings_index %}
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
`;var it="/_action/mgd-ai-image-labels/philosophy-page",B="Authentifizierter Administrationszugriff ist nicht verf\xFCgbar.",at="Die Philosophie-Seite konnte nicht vorbereitet werden.";async function ge(t,i){if(typeof t?.post!="function"||typeof i?.getBasicHeaders!="function")throw new TypeError(B);let n;try{n=i.getBasicHeaders()}catch{throw new TypeError(B)}if(n===null||typeof n!="object"||Array.isArray(n))throw new TypeError(B);try{return await t.post(it,{},{headers:n})}catch{throw new Error(at)}}var{Mixin:nt}=Shopware,ot=/^[0-9a-f]{32}$/i;Shopware.Component.register("mgd-ai-settings-index",{template:ce,inject:["acl","systemConfigApiService"],mixins:[nt.getByName("notification")],data(){return{isLoading:!1,cmsPageId:null}},computed:{canPrepare(){return["system_config:update","cms_page:create","cms_section:create","cms_block:create","cms_slot:create"].every(t=>this.acl.can(t))},cmsPageRoute(){return this.cmsPageId?{name:"sw.cms.detail",params:{id:this.cmsPageId}}:null}},methods:{async preparePage(){if(!(!this.canPrepare||this.isLoading)){this.isLoading=!0,this.cmsPageId=null;try{let t=Shopware.Application.getContainer("init").httpClient,i=await ge(t,this.systemConfigApiService),n=i?.data?.cmsPageId;if(typeof i?.data?.created!="boolean"||typeof n!="string"||!ot.test(n))throw new TypeError("Unerwartete Antwortstruktur");this.cmsPageId=n,this.createNotificationSuccess({message:this.$tc(i.data.created?"mgd-ai-image-labels.settings.successCreated":"mgd-ai-image-labels.settings.successExisting")})}catch{this.createNotificationError({message:this.$tc("mgd-ai-image-labels.settings.error")})}finally{this.isLoading=!1}}}}});Shopware.Module.register("mgd-ai-settings",{type:"plugin",name:"MGD AI Image Labels",title:"mgd-ai-image-labels.settings.title",description:"mgd-ai-image-labels.settings.description",color:"#0870ff",icon:"regular-sparkles",routes:{index:{component:"mgd-ai-settings-index",path:"index",meta:{parentPath:"sw.settings.index",privilege:"system_config:update"}}},settingsItem:{group:"plugins",to:"mgd.ai.settings.index",icon:"regular-sparkles",privilege:"system_config:update"}});var Si=Ce(pe());Shopware.Locale.extend("de-DE",G);Shopware.Locale.extend("en-GB",W);})();
