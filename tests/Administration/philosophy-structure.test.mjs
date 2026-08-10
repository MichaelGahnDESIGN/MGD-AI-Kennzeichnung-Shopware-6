import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import test from 'node:test';

const root = new URL('../../src/Resources/app/administration/src/', import.meta.url);

async function source(path) {
    return readFile(new URL(path, root), 'utf8');
}

test('CMS-Element trennt Registrierung, Komponente, Konfiguration und Vorschau', async () => {
    const index = await source('module/sw-cms/elements/mgd-ai-philosophy/index.js');
    assert.match(index, /translated:\s*true/);
    assert.match(index, /mgd-ai-philosophy/);
    await Promise.all(['component', 'config', 'preview'].map(async (folder) => {
        const code = await source(`module/sw-cms/elements/mgd-ai-philosophy/${folder}/index.js`);
        assert.match(code, /template/);
    }));
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
