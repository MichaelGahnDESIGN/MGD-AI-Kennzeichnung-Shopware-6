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
    assert.match(index, /altText:\s*\{\s*source:\s*'static',\s*value:\s*''\s*}/);
    assert.doesNotMatch(index, /https?:\/\//);
});

test('die Konfiguration bietet nur geschlossene Auswahlen und lokale Medien', async () => {
    const config = await read('module/sw-cms/elements/mgd-ai-background-image/config/config.html.twig');
    const code = await read('module/sw-cms/elements/mgd-ai-background-image/config/index.js');
    const normalizer = await read('service/cms-background-config.js');
    const combined = `${config}\n${code}\n${normalizer}`;

    assert.match(config, /sw-media-upload-v2/);
    assert.match(config, /sw-media-modal-v2/);
    assert.match(config, /mt-select/);
    assert.match(config, /mt-switch/);
    assert.match(config, /:checked="element\.config\.decorative\.value"/);
    assert.match(config, /@change="onChangeDecorative"/);
    assert.doesNotMatch(config, /v-model(?::model-value)?="element\.config\.decorative\.value"|@update:model-value="onChangeDecorative"/);
    assert.match(config, /file-accept="image\/\*"/);
    assert.match(config, /v-model:model-value="element\.config\.altText\.value"/);
    assert.match(config, /missingAltText/);
    assert.match(config, /selectionError/);
    assert.doesNotMatch(combined, /v-html|:style=|https?:\/\//);
    assert.doesNotMatch(combined, /url\s*:/i);
    assert.match(code, /compatConfig:\s*Shopware\.compatConfig/);
    assert.match(code, /isCompatEnabled\('INSTANCE_SET'\)/);
    assert.match(code, /this\.\$set\(this\.element, 'data'/);
    assert.match(code, /this\.\$set\(this\.element\.data, 'media'/);
    assert.match(code, /isImageMedia\(media\)/);
    assert.match(code, /updateElementData\(media\)/);
    assert.match(normalizer, /mediaType\.name === 'IMAGE'/);
});

test('der Adapter bildet die offiziell geprüften Shopware-6.6- und 6.7-Reaktivitätsverträge ab', async () => {
    // Geprüfte Quellen: offizielle Tags v6.6.10.22 und v6.7.13.0,
    // sw-cms/elements/image/config. Der echte Build folgt in Task 13.
    const config = await read('module/sw-cms/elements/mgd-ai-background-image/config/config.html.twig');
    const code = await read('module/sw-cms/elements/mgd-ai-background-image/config/index.js');

    assert.match(code, /compatConfig:\s*Shopware\.compatConfig/);
    assert.match(code, /if \(this\.isCompatEnabled\('INSTANCE_SET'\)\)/);
    assert.match(code, /this\.\$set\(this\.element, 'data', \{ mediaId, media }\)/);
    assert.match(code, /this\.element\.data = \{ mediaId, media }/);
    assert.match(code, /this\.\$set\(this\.element\.data, 'mediaId', mediaId\)/);
    assert.match(code, /this\.element\.data\.mediaId = mediaId/);
    assert.match(config, /<mt-switch\s+:checked=/);
    assert.match(config, /@change="onChangeDecorative"/);
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
