import assert from 'node:assert/strict';
import { access, readFile } from 'node:fs/promises';
import test from 'node:test';

const root = new URL('../../src/Resources/app/administration/src/', import.meta.url);

async function read(relativePath) {
    return readFile(new URL(relativePath, root), 'utf8');
}

function keyPaths(value, prefix = '') {
    if (typeof value !== 'object' || value === null) return [];

    return Object.entries(value).flatMap(([key, child]) => {
        const path = prefix ? `${prefix}.${key}` : key;
        return [path, ...keyPaths(child, path)];
    });
}

test('das CMS-Element ist vollständig und über den Administrationseinstieg registriert', async () => {
    const main = await read('main.js');
    const index = await read('module/sw-cms/elements/mgd-ai-background-image/index.js');

    assert.match(main, /import '.\/module\/sw-cms\/elements\/mgd-ai-background-image';/);
    assert.match(index, /name:\s*'mgd-ai-background-image'/);
    assert.match(index, /component:\s*'sw-cms-el-mgd-ai-background-image'/);
    assert.match(index, /configComponent:\s*'sw-cms-el-config-mgd-ai-background-image'/);
    assert.match(index, /previewComponent:\s*'sw-cms-el-preview-mgd-ai-background-image'/);
    assert.doesNotMatch(index, /https?:\/\//);
});

test('die Konfiguration bietet nur geschlossene Auswahlen und lokale Medien', async () => {
    const config = await read('module/sw-cms/elements/mgd-ai-background-image/config/config.html.twig');
    const code = await read('module/sw-cms/elements/mgd-ai-background-image/config/index.js');
    const combined = `${config}\n${code}`;

    assert.match(config, /sw-media-upload-v2/);
    assert.match(config, /sw-media-modal-v2/);
    assert.match(config, /mt-select/);
    assert.match(config, /mt-switch/);
    assert.doesNotMatch(combined, /mt-text-field|v-html|:style=|https?:\/\//);
    assert.doesNotMatch(combined, /url\s*:/i);
});

test('Komponente und Vorschau verwenden nur feste Klassen statt freier Styles', async () => {
    const files = await Promise.all([
        read('module/sw-cms/elements/mgd-ai-background-image/component/index.js'),
        read('module/sw-cms/elements/mgd-ai-background-image/component/component.html.twig'),
        read('module/sw-cms/elements/mgd-ai-background-image/preview/index.js'),
        read('module/sw-cms/elements/mgd-ai-background-image/preview/preview.html.twig'),
    ]);
    const combined = files.join('\n');

    assert.doesNotMatch(combined, /v-html|:style=|https?:\/\//);
    assert.match(combined, /normalizeBackgroundConfig/);
    assert.match(combined, /mgd-ai-background-image--height-/);
});

test('alle relativen JavaScript-Importe zeigen auf vorhandene Dateien', async () => {
    for (const relativePath of [
        'module/sw-cms/elements/mgd-ai-background-image/component/index.js',
        'module/sw-cms/elements/mgd-ai-background-image/config/index.js',
        'module/sw-cms/elements/mgd-ai-background-image/preview/index.js',
    ]) {
        const sourceUrl = new URL(relativePath, root);
        const source = await read(relativePath);
        const imports = [...source.matchAll(/from\s+'(\.\.[^']+)'/g)].map((match) => match[1]);

        for (const importPath of imports) {
            await access(new URL(`${importPath}.js`, sourceUrl));
        }
    }
});

test('deutsche und englische CMS-Snippets besitzen exakt dieselbe Struktur', async () => {
    const german = JSON.parse(await read('snippet/de-DE.json'));
    const english = JSON.parse(await read('snippet/en-GB.json'));

    assert.deepEqual(keyPaths(german).sort(), keyPaths(english).sort());
    assert.equal(german['mgd-ai-image-labels'].cms.background.label, 'Gekennzeichnetes Hintergrundbild');
    assert.equal(english['mgd-ai-image-labels'].cms.background.label, 'Labeled background image');
});
