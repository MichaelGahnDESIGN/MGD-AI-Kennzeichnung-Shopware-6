import template from './mgd-ai-settings-index.html.twig';
import { preparePhilosophyPage } from '../../../../service/prepare-philosophy-page-api.js';

const { Mixin } = Shopware;
const UUID_PATTERN = /^[0-9a-f]{32}$/i;

/** Steuert ausschließlich die explizite Erstellung und sichere Navigation. */
Shopware.Component.register('mgd-ai-settings-index', {
    template,
    inject: ['acl', 'systemConfigApiService'],
    mixins: [Mixin.getByName('notification')],

    data() {
        return {
            isLoading: false,
            cmsPageId: null,
        };
    },

    computed: {
        canPrepare() {
            return [
                'system_config:update',
                'cms_page:create',
                'cms_section:create',
                'cms_block:create',
                'cms_slot:create',
            ].every((privilege) => this.acl.can(privilege));
        },
        cmsPageRoute() {
            return this.cmsPageId ? { name: 'sw.cms.detail', params: { id: this.cmsPageId } } : null;
        },
    },

    methods: {
        async preparePage() {
            if (!this.canPrepare || this.isLoading) {
                return;
            }

            this.isLoading = true;
            this.cmsPageId = null;
            try {
                const httpClient = Shopware.Application.getContainer('init').httpClient;
                const response = await preparePhilosophyPage(httpClient, this.systemConfigApiService);
                const id = response?.data?.cmsPageId;
                if (typeof response?.data?.created !== 'boolean' || typeof id !== 'string' || !UUID_PATTERN.test(id)) {
                    throw new TypeError('Unerwartete Antwortstruktur');
                }
                this.cmsPageId = id;
                this.createNotificationSuccess({
                    message: this.$tc(response.data.created
                        ? 'mgd-ai-image-labels.settings.successCreated'
                        : 'mgd-ai-image-labels.settings.successExisting'),
                });
            } catch {
                // Rohdaten des Servers bleiben aus Datenschutz- und XSS-Gründen unsichtbar.
                this.createNotificationError({
                    message: this.$tc('mgd-ai-image-labels.settings.error'),
                });
            } finally {
                this.isLoading = false;
            }
        },
    },
});
