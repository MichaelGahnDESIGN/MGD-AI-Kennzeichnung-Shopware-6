import template from './config.html.twig';
import {
    currentAdministrationLocale,
    resolvePhilosophyContent,
} from '../../../../../service/philosophy-content';

const { Mixin } = Shopware;

/** Übersetzbare Rich-Text-Konfiguration auf Shopwares CMS-Element-Mixin. */
export default {
    template,
    compatConfig: Shopware.compatConfig,
    emits: ['element-update'],
    mixins: [Mixin.getByName('cms-element')],

    computed: {
        currentLocale() {
            return currentAdministrationLocale(Shopware);
        },
        resolvedContent() {
            return resolvePhilosophyContent(this.element?.config?.content?.value, this.currentLocale);
        },
    },

    created() {
        this.initElementConfig('mgd-ai-philosophy');
    },

    methods: {
        onInput(content) {
            this.element.config.content.value = typeof content === 'string' ? content : '';
            this.$emit('element-update', this.element);
        },
    },
};
