import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import test from 'node:test';

const root = new URL('../../src/Resources/app/administration/src/', import.meta.url);

async function source(path) {
    return readFile(new URL(path, root), 'utf8');
}

test('CMS-Element trennt Registrierung, Komponente, Konfiguration und Vorschau', async () => {
    const index = await source('module/sw-cms/elements/mgd-ai-philosophy/index.js');
    assert.match(index, /mgd-ai-philosophy/);
    await Promise.all(['component', 'config', 'preview'].map(async (folder) => {
        const code = await source(`module/sw-cms/elements/mgd-ai-philosophy/${folder}/index.js`);
        assert.match(code, /template/);
    }));
});

test('Konfiguration nutzt nur den gemeinsamen Shopware-6.6-/6.7-Richtext-Vertrag', async () => {
    const config = await source('module/sw-cms/elements/mgd-ai-philosophy/config/config.html.twig');
    const configScript = await source('module/sw-cms/elements/mgd-ai-philosophy/config/index.js');
    assert.match(config, /<sw-cms-mapping-field/);
    assert.match(config, /<sw-text-editor/);
    assert.doesNotMatch(config, /sw-cms-inherit-wrapper/);
    assert.match(configScript, /compatConfig:\s*Shopware\.compatConfig/);

    const contracts = JSON.parse(await readFile(new URL('./Fixtures/shopware-cms-text-contracts.json', import.meta.url), 'utf8'));
    for (const version of ['6.6.10.22', '6.7.13.0']) {
        assert.match(contracts[version].source, new RegExp(`github\\.com/shopware/shopware/blob/v${version}/`));
        assert.match(contracts[version].sha256, /^[0-9a-f]{64}$/);
        assert.ok(contracts[version].components.includes('sw-cms-mapping-field'));
        assert.ok(contracts[version].components.includes('sw-text-editor'));
    }
});

test('manuelle Defaults wechseln rein nach expliziter Locale und werden nicht automatisch persistiert', async () => {
    const {
        PHILOSOPHY_DEFAULT_CONTENT,
        currentAdministrationLocale,
        resolvePhilosophyContent,
    } = await import('../../src/Resources/app/administration/src/service/philosophy-content.js');
    assert.match(resolvePhilosophyContent('', 'de-DE'), /Unser Umgang mit KI-Bildern/);
    assert.match(resolvePhilosophyContent('', 'en-GB'), /Our approach to AI imagery/);
    assert.equal(resolvePhilosophyContent('', 'fr-FR'), resolvePhilosophyContent('', 'en-GB'));
    assert.equal(resolvePhilosophyContent([], 'de-DE'), resolvePhilosophyContent('', 'de-DE'));
    assert.equal(resolvePhilosophyContent('<p>Eigen</p>', 'en-GB'), '<p>Eigen</p>');
    assert.equal(currentAdministrationLocale({ Store: { get: () => ({ currentLocale: 'de-DE' }) } }), 'de-DE');
    assert.equal(currentAdministrationLocale({ State: { get: () => ({ currentLocale: 'en-GB' }) } }), 'en-GB');
    assert.equal(currentAdministrationLocale({}), 'en-GB');
    const de = JSON.parse(await source('snippet/de-DE.json'));
    const en = JSON.parse(await source('snippet/en-GB.json'));
    assert.equal(PHILOSOPHY_DEFAULT_CONTENT['de-DE'], de['mgd-ai-image-labels'].philosophy.defaultContent);
    assert.equal(PHILOSOPHY_DEFAULT_CONTENT['en-GB'], en['mgd-ai-image-labels'].philosophy.defaultContent);

    const config = await source('module/sw-cms/elements/mgd-ai-philosophy/config/index.js');
    const createdBlock = config.slice(config.indexOf('created()'), config.indexOf('methods:'));
    assert.doesNotMatch(createdBlock, /element\.config\.content\.value\s*=/);
    assert.match(config, /currentAdministrationLocale/);
    assert.match(config, /resolvedContent/);
});

test('Settings-Aktion ist ACL-geschützt und behandelt Laden, Erfolg und Fehler', async () => {
    const page = await source('module/mgd-ai-settings/page/mgd-ai-settings-index/index.js');
    assert.match(page, /system_config:update/);
    assert.match(page, /isLoading/);
    assert.match(page, /createNotificationSuccess/);
    assert.match(page, /createNotificationError/);
    assert.match(page, /sw\.cms\.detail/);
    assert.doesNotMatch(page, /error\.message|response\.data\.message|v-html/);
});

test('Einstiegspunkt importiert beide Funktionen und Snippets bleiben paarig', async () => {
    const main = await source('main.js');
    assert.match(main, /mgd-ai-philosophy/);
    assert.match(main, /mgd-ai-settings/);

    const de = JSON.parse(await source('snippet/de-DE.json'));
    const en = JSON.parse(await source('snippet/en-GB.json'));
    assert.deepEqual(Object.keys(de['mgd-ai-image-labels'].philosophy).sort(), Object.keys(en['mgd-ai-image-labels'].philosophy).sort());
    assert.deepEqual(Object.keys(de['mgd-ai-image-labels'].settings).sort(), Object.keys(en['mgd-ai-image-labels'].settings).sort());
});
