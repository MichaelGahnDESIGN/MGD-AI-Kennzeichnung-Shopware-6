/**
 * Geschlossene Positivlisten für die CMS-Hintergrunddarstellung.
 * Freie CSS-Werte werden weder gespeichert noch an Templates weitergereicht.
 */
export const BACKGROUND_OPTIONS = Object.freeze({
    minHeight: Object.freeze(['240px', '320px', '480px', '640px']),
    horizontalPosition: Object.freeze(['left', 'center', 'right']),
    verticalPosition: Object.freeze(['top', 'center', 'bottom']),
    fallbackColor: Object.freeze(['neutral-light', 'neutral-dark', 'brand']),
});

const DEFAULTS = Object.freeze({
    minHeight: '320px',
    horizontalPosition: 'center',
    verticalPosition: 'center',
    fallbackColor: 'neutral-light',
    decorative: false,
});

/** Nur ein echter Boolean darf die Bildsemantik verändern. */
export function normalizeBoolean(value) {
    return value === true;
}

function choice(value, allowed, fallback) {
    return typeof value === 'string' && allowed.includes(value) ? value : fallback;
}

/**
 * Normalisiert auch beschädigte oder manipulierte Administrationszustände.
 */
export function normalizeBackgroundConfig(value = {}) {
    const input = typeof value === 'object' && value !== null ? value : {};

    return {
        minHeight: choice(input.minHeight, BACKGROUND_OPTIONS.minHeight, DEFAULTS.minHeight),
        horizontalPosition: choice(
            input.horizontalPosition,
            BACKGROUND_OPTIONS.horizontalPosition,
            DEFAULTS.horizontalPosition,
        ),
        verticalPosition: choice(
            input.verticalPosition,
            BACKGROUND_OPTIONS.verticalPosition,
            DEFAULTS.verticalPosition,
        ),
        fallbackColor: choice(input.fallbackColor, BACKGROUND_OPTIONS.fallbackColor, DEFAULTS.fallbackColor),
        decorative: normalizeBoolean(input.decorative),
    };
}
