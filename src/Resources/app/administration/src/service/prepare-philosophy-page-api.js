export const PREPARE_PHILOSOPHY_PAGE_PATH = '/_action/mgd-ai-image-labels/philosophy-page';

const ACCESS_ERROR = 'Authentifizierter Administrationszugriff ist nicht verfügbar.';
const REQUEST_ERROR = 'Die Philosophie-Seite konnte nicht vorbereitet werden.';

/**
 * Sendet die bewusst zustandsändernde Admin-Aktion als authentifizierten POST.
 *
 * Der rohe Shopware-HTTP-Client ergänzt bei direkten Aufrufen keinen Bearer-
 * Header. Deshalb erhält diese kleine, testbare Funktion zusätzlich einen
 * vorhandenen Shopware-API-Dienst. Dessen `getBasicHeaders()` erzeugt die
 * aktuellen Standard-Header einschließlich Anmeldung und Inhaltssprache.
 * Tokens werden hier weder ausgelesen noch gespeichert oder protokolliert.
 *
 * @param {object|null} httpClient Shopwares zentraler HTTP-Client.
 * @param {object|null} authenticatedApiService Ein angemeldeter Core-API-Dienst.
 *
 * @returns {Promise<object>} Die unveränderte HTTP-Antwort zur strengen Prüfung durch die Oberfläche.
 */
export async function preparePhilosophyPage(httpClient, authenticatedApiService) {
    if (typeof httpClient?.post !== 'function'
        || typeof authenticatedApiService?.getBasicHeaders !== 'function') {
        throw new TypeError(ACCESS_ERROR);
    }

    let headers;
    try {
        headers = authenticatedApiService.getBasicHeaders();
    } catch {
        throw new TypeError(ACCESS_ERROR);
    }

    if (headers === null || typeof headers !== 'object' || Array.isArray(headers)) {
        throw new TypeError(ACCESS_ERROR);
    }

    try {
        return await httpClient.post(PREPARE_PHILOSOPHY_PAGE_PATH, {}, { headers });
    } catch {
        // Interne Servertexte dürfen weder in Benachrichtigungen noch in der
        // Browserkonsole des Plugins als potenziell sensible Rohdaten landen.
        throw new Error(REQUEST_ERROR);
    }
}
