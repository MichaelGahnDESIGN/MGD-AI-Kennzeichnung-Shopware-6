import template from './preview.html.twig';
import './preview.scss';

import { normalizeBackgroundConfig } from '../../../../../service/cms-background-config.js';

/** Kompakte, medienfreie Elementvorschau in der CMS-Auswahl. */
export default {
    template,
    computed: {
        previewClass() {
            const presentation = normalizeBackgroundConfig();
            return `mgd-ai-background-image--height-${presentation.minHeight.replace('px', '')}`;
        },
    },
};
