import template from './sw-media-quickinfo.html.twig';

/**
 * Ergänzt die Medien-Seitenleiste rein darstellend. Die bestehende Shopware-
 * Komponente behält ihre native Feldanzeige, Speicheraktion und ACL-Prüfung.
 */
Shopware.Component.override('sw-media-quickinfo', {
    template,
});
