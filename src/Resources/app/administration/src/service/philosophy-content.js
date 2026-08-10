export const PHILOSOPHY_DEFAULT_CONTENT = Object.freeze({
    'de-DE': '<h2>Unser Umgang mit KI-Bildern</h2><p>Wir kennzeichnen Bilder transparent, wenn sie vollständig oder teilweise mit künstlicher Intelligenz erstellt oder bearbeitet wurden.</p><p>So können Sie Inhalte bewusst einordnen. Die Kennzeichnung verändert weder Produktinformationen noch Ihre Privatsphäre.</p>',
    'en-GB': '<h2>Our approach to AI imagery</h2><p>We label images transparently when they were created or edited wholly or partly with artificial intelligence.</p><p>This helps you assess content consciously. The label changes neither product information nor your privacy.</p>',
});

/** Liefert die explizite reaktive Admin-Locale für Shopware 6.6 und 6.7. */
export function currentAdministrationLocale(shopware = globalThis.Shopware) {
    return shopware?.Store?.get?.('session')?.currentLocale
        ?? shopware?.State?.get?.('session')?.currentLocale
        ?? 'en-GB';
}

/**
 * Zeigt bei leerem Inhalt einen sprachabhängigen Standard, ohne ihn zu speichern.
 * Unbekannte oder manipulierte Locales fallen sicher auf Englisch zurück.
 */
export function resolvePhilosophyContent(content, locale) {
    if (typeof content === 'string' && content.trim() !== '') {
        return content;
    }

    return typeof locale === 'string' && /^de(?:[-_]|$)/i.test(locale.trim())
        ? PHILOSOPHY_DEFAULT_CONTENT['de-DE']
        : PHILOSOPHY_DEFAULT_CONTENT['en-GB'];
}
