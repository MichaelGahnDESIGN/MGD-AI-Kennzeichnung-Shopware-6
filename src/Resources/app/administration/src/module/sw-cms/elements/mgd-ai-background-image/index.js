import component from './component';
import configComponent from './config';
import previewComponent from './preview';

Shopware.Component.register('sw-cms-el-mgd-ai-background-image', component);
Shopware.Component.register('sw-cms-el-config-mgd-ai-background-image', configComponent);
Shopware.Component.register('sw-cms-el-preview-mgd-ai-background-image', previewComponent);

/**
 * Registriert ein Element mit ausschließlich statischen, geschlossenen Werten.
 */
Shopware.Service('cmsService').registerCmsElement({
    name: 'mgd-ai-background-image',
    label: 'mgd-ai-image-labels.cms.background.label',
    component: 'sw-cms-el-mgd-ai-background-image',
    configComponent: 'sw-cms-el-config-mgd-ai-background-image',
    previewComponent: 'sw-cms-el-preview-mgd-ai-background-image',
    defaultConfig: {
        media: { source: 'static', value: null, required: true },
        minHeight: { source: 'static', value: '320px' },
        horizontalPosition: { source: 'static', value: 'center' },
        verticalPosition: { source: 'static', value: 'center' },
        decorative: { source: 'static', value: false },
        fallbackColor: { source: 'static', value: 'neutral-light' },
        altText: { source: 'static', value: '' },
    },
});
