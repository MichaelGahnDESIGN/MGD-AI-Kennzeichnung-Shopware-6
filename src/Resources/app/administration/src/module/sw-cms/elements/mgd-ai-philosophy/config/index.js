import template from './config.html.twig';

const { Mixin } = Shopware;

/** Übersetzbare Rich-Text-Konfiguration auf Shopwares CMS-Element-Mixin. */
export default {
    template,
    emits: ['element-update'],
    mixins: [Mixin.getByName('cms-element')],

    created() {
        this.initElementConfig('mgd-ai-philosophy');
        if (typeof this.element.config.content.value !== 'string' || this.element.config.content.value.trim() === '') {
            this.element.config.content.value = this.$tc('mgd-ai-image-labels.philosophy.defaultContent');
        }
    },

    methods: {
        onInput(content) {
            this.element.config.content.value = typeof content === 'string' ? content : '';
            this.$emit('element-update', this.element);
        },
    },
};
