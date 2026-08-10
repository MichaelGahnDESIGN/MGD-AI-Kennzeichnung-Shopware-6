import component from './component';
import configComponent from './config';
import previewComponent from './preview';

Shopware.Component.register('sw-cms-el-mgd-ai-philosophy', component);
Shopware.Component.register('sw-cms-el-config-mgd-ai-philosophy', configComponent);
Shopware.Component.register('sw-cms-el-preview-mgd-ai-philosophy', previewComponent);

/**
 * `content` wird von Shopwares CmsSlotTranslationDefinition übersetzt gespeichert.
 * Der Creator liefert zusätzlich beide Sprachversionen beim ersten Anlegen.
 */
Shopware.Service('cmsService').registerCmsElement({
    name: 'mgd-ai-philosophy',
    label: 'mgd-ai-image-labels.philosophy.label',
    component: 'sw-cms-el-mgd-ai-philosophy',
    configComponent: 'sw-cms-el-config-mgd-ai-philosophy',
    previewComponent: 'sw-cms-el-preview-mgd-ai-philosophy',
    defaultConfig: {
        content: {
            source: 'static',
            value: '',
            translated: true,
        },
    },
});
