import './page/mgd-ai-settings-index';

/** Kleine Einstellungsseite für die bewusst manuell ausgelöste Seitenerstellung. */
Shopware.Module.register('mgd-ai-settings', {
    type: 'plugin',
    name: 'MGD AI Image Labels',
    title: 'mgd-ai-image-labels.settings.title',
    description: 'mgd-ai-image-labels.settings.description',
    color: '#0870ff',
    icon: 'regular-sparkles',
    routes: {
        index: {
            component: 'mgd-ai-settings-index',
            path: 'index',
            meta: {
                parentPath: 'sw.settings.index',
                privilege: 'system_config:update',
            },
        },
    },
    settingsItem: {
        group: 'plugins',
        to: 'mgd.ai.settings.index',
        icon: 'regular-sparkles',
        privilege: 'system_config:update',
    },
});
