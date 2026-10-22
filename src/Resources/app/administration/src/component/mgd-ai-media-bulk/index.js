import template from './mgd-ai-media-bulk.html.twig';
import './mgd-ai-media-bulk.scss';
import { bulkOptions, buildBulkPatch, selectedImages, saveBulkLabels } from '../../service/bulk-labels.js';

Shopware.Component.register('mgd-ai-media-bulk', {
    template,
    inject: ['repositoryFactory', 'acl'],
    props: { items: { type: Array, required: true } },
    data() {
        return { values: { status: '', position: '', theme: '' }, busy: false, progress: '', outcome: '' };
    },
    computed: {
        images() { return selectedImages(this.items); },
        allowed() { return this.acl.can('media.editor'); },
        changed() { return Object.keys(buildBulkPatch(this.values)).length > 0; },
        fields() { return Object.keys(bulkOptions); },
        options() { return bulkOptions; },
        selectionKey() { return this.images.map((item) => item.id).sort().join(','); },
    },
    watch: {
        selectionKey() {
            if (!this.busy) {
                this.outcome = '';
                this.values = { status: '', position: '', theme: '' };
            }
        },
    },
    methods: {
        label(field, value) {
            const key = value.replace(/-([a-z])/g, (_, letter) => letter.toUpperCase());
            return this.$t(`mgd-ai-image-labels.preview.${field}.${key}`);
        },
        async apply() {
            if (this.busy || !this.allowed || !this.changed || !this.images.length) return;
            this.busy = true;
            this.outcome = '';
            this.progress = '';
            try {
                const result = await saveBulkLabels({
                    items: this.items,
                    values: this.values,
                    repository: this.repositoryFactory.create('media'),
                    context: Shopware.Context.api,
                    canEdit: () => this.acl.can('media.editor'),
                    onProgress: (done, total) => { this.progress = `${done} / ${total}`; },
                });
                this.outcome = this.$t('mgd-ai-image-labels.bulk.result', {
                    saved: result.saved.length, failed: result.failed.length,
                });
                this.values = { status: '', position: '', theme: '' };
            } catch {
                this.outcome = this.$t('mgd-ai-image-labels.bulk.error');
            } finally {
                this.busy = false;
                this.progress = '';
            }
        },
    },
});
