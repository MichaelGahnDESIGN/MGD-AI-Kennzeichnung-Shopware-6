/**
 * Geschlossene Wertebereiche der Medienkennzeichnung.
 *
 * Diese Positivlisten entsprechen bewusst den PHP-Enums. Ungeprüfte
 * Custom-Field-Inhalte dürfen niemals direkt in CSS-Klassen oder sichtbare
 * Zustände gelangen.
 */
const ALLOWED_STATUSES = Object.freeze([
    'none',
    'generated',
    'partially-generated',
    'modified',
    'deepfake',
]);

const ALLOWED_POSITIONS = Object.freeze([
    'top-left',
    'top-right',
    'bottom-left',
    'bottom-right',
]);

const ALLOWED_THEMES = Object.freeze([
    'auto',
    'light',
    'dark',
]);

/** Feste Klassenzuordnung; kein gespeicherter Wert wird zu CSS zusammengesetzt. */
const POSITION_CLASSES = Object.freeze({
    'top-left': 'is--top-left',
    'top-right': 'is--top-right',
    'bottom-left': 'is--bottom-left',
    'bottom-right': 'is--bottom-right',
});

/** Feste Theme-Klassen für die lokale Administrationsvorschau. */
const THEME_CLASSES = Object.freeze({
    auto: 'is--auto',
    light: 'is--light',
    dark: 'is--dark',
});

/** Feste Übersetzungsschlüssel der erlaubten Statuswerte. */
const STATUS_SNIPPETS = Object.freeze({
    none: 'mgd-ai-image-labels.preview.status.none',
    generated: 'mgd-ai-image-labels.preview.status.generated',
    'partially-generated': 'mgd-ai-image-labels.preview.status.partiallyGenerated',
    modified: 'mgd-ai-image-labels.preview.status.modified',
    deepfake: 'mgd-ai-image-labels.preview.status.deepfake',
});

/** Feste Übersetzungsschlüssel der erlaubten Positionen. */
const POSITION_SNIPPETS = Object.freeze({
    'top-left': 'mgd-ai-image-labels.preview.position.topLeft',
    'top-right': 'mgd-ai-image-labels.preview.position.topRight',
    'bottom-left': 'mgd-ai-image-labels.preview.position.bottomLeft',
    'bottom-right': 'mgd-ai-image-labels.preview.position.bottomRight',
});

/** Feste Übersetzungsschlüssel der erlaubten Darstellungsvarianten. */
const THEME_SNIPPETS = Object.freeze({
    auto: 'mgd-ai-image-labels.preview.theme.auto',
    light: 'mgd-ai-image-labels.preview.theme.light',
    dark: 'mgd-ai-image-labels.preview.theme.dark',
});

/**
 * Normalisiert die nicht vertrauenswürdigen Werte der Medien-Custom-Fields.
 *
 * Die Funktion erzeugt weder HTML noch freie Style-Werte. Sie liefert nur
 * Werte aus fest eingebauten Positivlisten, damit die Vorschau anschließend
 * ausschließlich bekannte Klassen auswählen kann.
 *
 * @param {unknown} input Ungeprüfte Werte aus dem aktuellen Medienobjekt.
 * @returns {{status: string, position: string, theme: string, visible: boolean}}
 */
export function normalizePreviewState(input = {}) {
    const values = typeof input === 'object' && input !== null ? input : {};
    const status = ALLOWED_STATUSES.includes(values.status) ? values.status : 'none';
    const position = ALLOWED_POSITIONS.includes(values.position) ? values.position : 'bottom-right';
    const theme = ALLOWED_THEMES.includes(values.theme) ? values.theme : 'auto';

    return {
        status,
        position,
        theme,
        visible: status !== 'none',
    };
}

/**
 * Wählt ausschließlich vorab definierte Klassen und Übersetzungsschlüssel.
 *
 * Auch direkte Aufrufe dieser Funktion werden erneut normalisiert. Damit kann
 * keine spätere Komponente versehentlich ungeprüfte Custom-Field-Werte an die
 * Darstellung weiterreichen.
 *
 * @param {unknown} input Ungeprüfte Werte aus dem aktuellen Medienobjekt.
 * @returns {{
 *     positionClass: string,
 *     themeClass: string,
 *     labelSnippet: string,
 *     positionSnippet: string,
 *     themeSnippet: string
 * }}
 */
export function getPreviewPresentation(input = {}) {
    const state = normalizePreviewState(input);

    return {
        positionClass: POSITION_CLASSES[state.position],
        themeClass: THEME_CLASSES[state.theme],
        labelSnippet: STATUS_SNIPPETS[state.status],
        positionSnippet: POSITION_SNIPPETS[state.position],
        themeSnippet: THEME_SNIPPETS[state.theme],
    };
}
