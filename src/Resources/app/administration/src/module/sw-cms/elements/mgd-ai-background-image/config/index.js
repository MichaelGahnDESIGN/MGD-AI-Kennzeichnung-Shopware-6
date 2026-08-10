import template from './config.html.twig';

import {
    BACKGROUND_OPTIONS,
    normalizeBackgroundConfig,
} from '../../../../../service/cms-background-config';

const { Mixin } = Shopware;

/** Sichere Konfiguration mit Shopwares lokaler Medienauswahl. */
export default {
    template,
    inject: ['repositoryFactory'],
    emits: ['element-update'],
    mixins: [Mixin.getByName('cms-element')],

    data() {
        return { mediaModalIsOpen: false };
    },

    computed: {
        mediaRepository() {
            return this.repositoryFactory.create('media');
        },
        uploadTag() {
            return `mgd-ai-background-image-${this.element.id}`;
        },
        previewSource() {
            return this.element?.data?.media ?? this.element?.config?.media?.value ?? null;
        },
        minHeightOptions() {
            return this.options('minHeight', BACKGROUND_OPTIONS.minHeight);
        },
        horizontalOptions() {
            return this.options('horizontalPosition', BACKGROUND_OPTIONS.horizontalPosition);
        },
        verticalOptions() {
            return this.options('verticalPosition', BACKGROUND_OPTIONS.verticalPosition);
        },
        fallbackOptions() {
            return this.options('fallbackColor', BACKGROUND_OPTIONS.fallbackColor);
        },
    },

    created() {
        this.initElementConfig('mgd-ai-background-image');
        this.normalizeElementConfig();
    },

    methods: {
        options(group, values) {
            return values.map((value) => ({
                value,
                label: this.$tc(`mgd-ai-image-labels.cms.background.options.${group}.${value.replace('px', '')}`),
            }));
        },
        normalizeElementConfig() {
            const normalized = normalizeBackgroundConfig({
                minHeight: this.element.config.minHeight.value,
                horizontalPosition: this.element.config.horizontalPosition.value,
                verticalPosition: this.element.config.verticalPosition.value,
                fallbackColor: this.element.config.fallbackColor.value,
                decorative: this.element.config.decorative.value,
            });
            for (const [key, value] of Object.entries(normalized)) {
                this.element.config[key].value = value;
            }
        },
        emitUpdate() {
            this.normalizeElementConfig();
            this.$emit('element-update', this.element);
        },
        async onImageUpload({ targetId }) {
            const media = await this.mediaRepository.get(targetId, Shopware.Context.api);
            this.setMedia(media);
        },
        onSelectionChanges(selection) {
            const media = Array.isArray(selection) ? selection[0] : null;
            if (media?.id) this.setMedia(media);
        },
        setMedia(media) {
            this.element.config.media.value = media.id;
            this.element.config.media.source = 'static';
            this.element.data = { mediaId: media.id, media };
            this.mediaModalIsOpen = false;
            this.emitUpdate();
        },
        onImageRemove() {
            this.element.config.media.value = null;
            this.element.data = { mediaId: null, media: null };
            this.emitUpdate();
        },
    },
};
