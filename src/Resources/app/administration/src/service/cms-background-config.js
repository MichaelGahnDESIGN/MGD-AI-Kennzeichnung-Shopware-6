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
    altText: '',
});

/** Nur ein echter Boolean darf die Bildsemantik verändern. */
export function normalizeBoolean(value) {
    return value === true;
}

/** Redaktioneller Alternativtext bleibt reiner, begrenzter Text. */
export function normalizeEditorialAltText(value) {
    if (typeof value !== 'string') {
        return '';
    }

    const plainText = value
        .replace(/<(script|style)\b[^>]*>[\s\S]*?<\/\1\s*>/giu, '')
        .replace(/<[^>]*>/gu, '')
        .replace(/[\u0000-\u001F\u007F]+/gu, ' ')
        .trim();

    return [...plainText].slice(0, 512).join('');
}

/** Nutzt exakt dieselbe Alt-Text-Reihenfolge wie der serverseitige Resolver. */
export function resolveBackgroundAltText(editorialAltText, media) {
    const translated = typeof media?.translated === 'object' && media.translated !== null
        ? media.translated
        : {};

    for (const candidate of [editorialAltText, translated.alt, translated.title]) {
        const normalized = normalizeEditorialAltText(candidate);
        if (normalized !== '') {
            return normalized;
        }
    }

    return '';
}

/** MIME- und Shopware-Medientyp müssen einander als Bild bestätigen. */
export function isImageMedia(media) {
    if (typeof media !== 'object' || media === null || typeof media.mimeType !== 'string') {
        return false;
    }
    if (!media.mimeType.startsWith('image/')) {
        return false;
    }

    const mediaType = media.mediaType;
    return (
        typeof mediaType === 'object'
        && mediaType !== null
        && typeof mediaType.name === 'string'
        && mediaType.name === 'IMAGE'
    );
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
        altText: normalizeEditorialAltText(input.altText),
    };
}
