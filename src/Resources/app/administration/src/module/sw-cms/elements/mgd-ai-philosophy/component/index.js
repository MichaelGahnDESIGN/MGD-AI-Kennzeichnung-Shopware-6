import template from './component.html.twig';
import {
    philosophyContentLanguageMixin,
    resolvePhilosophyContent,
} from '../../../../../service/philosophy-content';

const { Mixin } = Shopware;

/** Sichere Arbeitsflächen-Vorschau: Rich Text wird nur als Text dargestellt. */
export default {
    template,
    mixins: [Mixin.getByName('cms-element'), philosophyContentLanguageMixin],

    computed: {
        plainContent() {
            const value = resolvePhilosophyContent(
                this.element?.config?.content?.value,
                this.contentLocale,
            );
            return value.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
        },
    },

    created() {
        this.initElementConfig('mgd-ai-philosophy');
    },
};
