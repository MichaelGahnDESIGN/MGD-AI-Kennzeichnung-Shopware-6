export const PHILOSOPHY_DEFAULT_CONTENT = Object.freeze({
    'de-DE': '<h2>Unser Umgang mit KI-Bildern</h2><p>Wir kennzeichnen Bilder transparent, wenn sie vollständig oder teilweise mit künstlicher Intelligenz erstellt oder bearbeitet wurden.</p><p>So können Sie Inhalte bewusst einordnen. Die Kennzeichnung verändert weder Produktinformationen noch Ihre Privatsphäre.</p>',
    'en-GB': '<h2>Our approach to AI imagery</h2><p>We label images transparently when they were created or edited wholly or partly with artificial intelligence.</p><p>This helps you assess content consciously. The label changes neither product information nor your privacy.</p>',
});

/** Liefert die aktive CMS-Inhaltssprache reaktiv aus Shopware 6.6 oder 6.7. */
export function activeContentLanguageId(shopware = globalThis.Shopware) {
    return shopware?.Store?.get?.('context')?.api?.languageId
        ?? shopware?.State?.get?.('context')?.api?.languageId
        ?? shopware?.Context?.api?.languageId
        ?? null;
}

/** Begrenzt unvollständige LanguageEntity-Antworten auf Locale oder Englisch. */
export function localeFromLanguage(language) {
    return typeof language?.locale?.code === 'string' && language.locale.code.trim() !== ''
        ? language.locale.code
        : 'en-GB';
}

/**
 * Akzeptiert nur die Antwort des jüngsten Sprachwechsels. Langsame ältere
 * Repository-Antworten können die aktuelle Vorschau dadurch nicht überschreiben.
 */
export function createContentLanguageLocaleLoader(loadLanguage) {
    let requestNumber = 0;

    return {
        async load(languageId) {
            const ownRequest = ++requestNumber;
            let language = null;
            try {
                language = typeof languageId === 'string' && languageId !== ''
                    ? await loadLanguage(languageId)
                    : null;
            } catch {
                language = null;
            }

            return ownRequest === requestNumber ? localeFromLanguage(language) : null;
        },
        cancel() {
            ++requestNumber;
        },
    };
}

/** Wiederverwendbarer 6.6-/6.7-Mixin für Config und Arbeitsflächen-Vorschau. */
export const philosophyContentLanguageMixin = {
    inject: ['repositoryFactory'],

    data() {
        return {
            contentLocale: 'en-GB',
            contentLanguageLocaleLoader: null,
        };
    },

    computed: {
        contentLanguageId() {
            return activeContentLanguageId(Shopware);
        },
        languageRepository() {
            return this.repositoryFactory.create('language');
        },
    },

    watch: {
        contentLanguageId(languageId) {
            this.refreshContentLocale(languageId);
        },
    },

    created() {
        this.contentLanguageLocaleLoader = createContentLanguageLocaleLoader(async (languageId) => {
            const criteria = new Shopware.Data.Criteria(1, 1);
            criteria.addAssociation('locale');

            return this.languageRepository.get(languageId, Shopware.Context.api, criteria);
        });
        this.refreshContentLocale(this.contentLanguageId);
    },

    beforeDestroy() {
        this.contentLanguageLocaleLoader?.cancel();
    },

    beforeUnmount() {
        this.contentLanguageLocaleLoader?.cancel();
    },

    methods: {
        async refreshContentLocale(languageId) {
            const locale = await this.contentLanguageLocaleLoader.load(languageId);
            if (locale !== null) {
                this.contentLocale = locale;
            }
        },
    },
};

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
