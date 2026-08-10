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

test('Konfiguration nutzt nur den dokumentierten gemeinsamen Shopware-6.6-/6.7-Richtext-Vertrag', async () => {
    const config = await source('module/sw-cms/elements/mgd-ai-philosophy/config/config.html.twig');
    const configScript = await source('module/sw-cms/elements/mgd-ai-philosophy/config/index.js');
    assert.match(config, /<sw-cms-mapping-field/);
    assert.match(config, /<sw-text-editor/);
    assert.doesNotMatch(config, /sw-cms-inherit-wrapper/);
    assert.match(configScript, /compatConfig:\s*Shopware\.compatConfig/);

    // Die Fixture dokumentiert die bei der Implementierung manuell geprüften
    // Upstream-Dateien. Der echte Hash-/Buildvergleich bleibt Teil von Task 13.
    const contracts = JSON.parse(await readFile(new URL('./Fixtures/shopware-cms-text-contracts.json', import.meta.url), 'utf8'));
    for (const version of ['6.6.10.22', '6.7.13.0']) {
        assert.match(contracts[version].source, new RegExp(`github\\.com/shopware/shopware/blob/v${version}/`));
        assert.match(contracts[version].documentedSha256, /^[0-9a-f]{64}$/);
        assert.ok(contracts[version].components.includes('sw-cms-mapping-field'));
        assert.ok(contracts[version].components.includes('sw-text-editor'));
    }
});

test('manuelle Defaults folgen der CMS-Inhaltssprache statt der UI-Sprache', async () => {
    const {
        PHILOSOPHY_DEFAULT_CONTENT,
        activeContentLanguageId,
        createContentLanguageLocaleLoader,
        resolvePhilosophyContent,
    } = await import('../../src/Resources/app/administration/src/service/philosophy-content.js');
    assert.match(resolvePhilosophyContent('', 'de-DE'), /Unser Umgang mit KI-Bildern/);
    assert.match(resolvePhilosophyContent('', 'en-GB'), /Our approach to AI imagery/);
    assert.equal(resolvePhilosophyContent('', 'fr-FR'), resolvePhilosophyContent('', 'en-GB'));
    assert.equal(resolvePhilosophyContent([], 'de-DE'), resolvePhilosophyContent('', 'de-DE'));
    assert.equal(resolvePhilosophyContent('<p>Eigen</p>', 'en-GB'), '<p>Eigen</p>');
    assert.equal(activeContentLanguageId({ Store: { get: (name) => name === 'context'
        ? { api: { languageId: 'content-en' } }
        : { currentLocale: 'de-DE' } } }), 'content-en');
    assert.equal(activeContentLanguageId({ State: { get: (name) => name === 'context'
        ? { api: { languageId: 'content-de' } }
        : { currentLocale: 'en-GB' } } }), 'content-de');
    assert.equal(activeContentLanguageId({ Context: { api: { languageId: 'fallback-id' } } }), 'fallback-id');
    // UI Deutsch + Content Englisch sowie UI Englisch + Content Deutsch.
    assert.match(resolvePhilosophyContent('', 'en-GB'), /Our approach/);
    assert.match(resolvePhilosophyContent('', 'de-DE'), /Unser Umgang/);
    const de = JSON.parse(await source('snippet/de-DE.json'));
    const en = JSON.parse(await source('snippet/en-GB.json'));
    assert.equal(PHILOSOPHY_DEFAULT_CONTENT['de-DE'], de['mgd-ai-image-labels'].philosophy.defaultContent);
    assert.equal(PHILOSOPHY_DEFAULT_CONTENT['en-GB'], en['mgd-ai-image-labels'].philosophy.defaultContent);

    const config = await source('module/sw-cms/elements/mgd-ai-philosophy/config/index.js');
    const helper = await source('service/philosophy-content.js');
    const createdBlock = config.slice(config.indexOf('created()'), config.indexOf('methods:'));
    assert.doesNotMatch(createdBlock, /element\.config\.content\.value\s*=/);
    assert.match(config, /philosophyContentLanguageMixin/);
    assert.match(helper, /repositoryFactory/);
    assert.match(helper, /activeContentLanguageId/);
    assert.match(helper, /watch:\s*\{/);
    assert.match(helper, /create\('language'\)/);
    assert.match(helper, /addAssociation\('locale'\)/);
    assert.match(helper, /languageRepository\.get\(languageId/);
    assert.match(config, /resolvedContent/);

    let resolveEnglish;
    let resolveGerman;
    const requests = new Map([
        ['content-en', new Promise((resolve) => { resolveEnglish = resolve; })],
        ['content-de', new Promise((resolve) => { resolveGerman = resolve; })],
    ]);
    const loader = createContentLanguageLocaleLoader((id) => requests.get(id));
    const stale = loader.load('content-en');
    const current = loader.load('content-de');
    resolveGerman({ locale: { code: 'de-DE' } });
    assert.equal(await current, 'de-DE');
    resolveEnglish({ locale: { code: 'en-GB' } });
    assert.equal(await stale, null);
    assert.equal(await loader.load('unknown'), 'en-GB');
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
    for (const privilege of ['Systemkonfiguration', 'CMS-Seiten', 'Sektionen', 'Blöcke', 'Elemente']) {
        assert.match(de['mgd-ai-image-labels'].settings.missingPermission, new RegExp(privilege));
    }
    for (const privilege of ['system configuration', 'CMS pages', 'sections', 'blocks', 'elements']) {
        assert.match(en['mgd-ai-image-labels'].settings.missingPermission, new RegExp(privilege, 'i'));
    }
});
