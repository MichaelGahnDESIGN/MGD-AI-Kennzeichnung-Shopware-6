import template from './component.html.twig';
import './component.scss';

import { normalizeBackgroundConfig } from '../../../../../service/cms-background-config';

const { Mixin } = Shopware;

/** Vorschau im Erlebniswelten-Arbeitsbereich. */
export default {
    template,

    mixins: [Mixin.getByName('cms-element')],

    computed: {
        presentation() {
            return normalizeBackgroundConfig({
                minHeight: this.element?.config?.minHeight?.value,
                horizontalPosition: this.element?.config?.horizontalPosition?.value,
                verticalPosition: this.element?.config?.verticalPosition?.value,
                fallbackColor: this.element?.config?.fallbackColor?.value,
                decorative: this.element?.config?.decorative?.value,
                altText: this.element?.config?.altText?.value,
            });
        },

        presentationClasses() {
            return [
                `mgd-ai-background-image--height-${this.presentation.minHeight.replace('px', '')}`,
                `mgd-ai-background-image--horizontal-${this.presentation.horizontalPosition}`,
                `mgd-ai-background-image--vertical-${this.presentation.verticalPosition}`,
                `mgd-ai-background-image--fallback-${this.presentation.fallbackColor}`,
            ];
        },

        mediaSource() {
            return this.element?.data?.media?.url ?? null;
        },
    },

    created() {
        this.initElementConfig('mgd-ai-background-image');
        this.initElementData('mgd-ai-background-image');
    },
};
