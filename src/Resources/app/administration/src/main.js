/**
 * Einstiegspunkt der Shopware-Administration.
 *
 * Die Vorschau-Komponente wird vor der Medien-Erweiterung registriert, damit
 * Shopware sie beim Überschreiben der Seitenleiste sicher auflösen kann.
 */
import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

import './component/mgd-ai-media-preview';
import './extension/sw-media-quickinfo';
import './module/sw-cms/elements/mgd-ai-background-image';
import './module/sw-cms/elements/mgd-ai-philosophy';
import './module/mgd-ai-settings';

/**
 * Deutsch und Englisch gehören bereits zu Shopwares Locale-Registry. `extend`
 * ergänzt diese bestehenden Sprachen in 6.6 und 6.7, ohne eine vorhandene
 * Locale erneut zu registrieren oder andere Übersetzungen zu überschreiben.
 */
Shopware.Locale.extend('de-DE', deDE);
Shopware.Locale.extend('en-GB', enGB);
