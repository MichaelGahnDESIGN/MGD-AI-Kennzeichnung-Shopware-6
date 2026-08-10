import template from './mgd-ai-media-preview.html.twig';
import './mgd-ai-media-preview.scss';

import {
    getPreviewPresentation,
    normalizePreviewState,
} from '../../service/preview-state.js';

/**
 * Zeigt ausschließlich eine lokale Vorschau der bereits am Medium gebundenen
 * Custom Fields. Speichern und Berechtigungsprüfung verbleiben vollständig in
 * Shopwares nativem Custom-Field-Renderer.
 */
Shopware.Component.register('mgd-ai-media-preview', {
    template,

    props: {
        item: {
            type: Object,
            required: true,
        },
    },

    computed: {
        /**
         * Übernimmt nur die drei plugin-eigenen Felder. Alle übrigen Daten des
         * Medienobjekts bleiben unberührt und werden nicht weitergegeben.
         */
        previewInput() {
            const customFields = this.item.customFields ?? {};

            return {
                status: customFields.mgd_ai_status,
                position: customFields.mgd_ai_position,
                theme: customFields.mgd_ai_theme,
            };
        },

        /** Liefert den sicher normalisierten, reaktiven Vorschauzustand. */
        previewState() {
            return normalizePreviewState(this.previewInput);
        },

        /** Liefert nur feste Klassen und einen festen Snippet-Schlüssel. */
        previewPresentation() {
            return getPreviewPresentation(this.previewState);
        },
    },
});
