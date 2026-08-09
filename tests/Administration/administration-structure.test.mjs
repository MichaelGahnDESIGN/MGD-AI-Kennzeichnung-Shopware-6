import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import test from 'node:test';

const administrationRoot = new URL('../../src/Resources/app/administration/src/', import.meta.url);

async function readAdministrationFile(relativePath) {
    return readFile(new URL(relativePath, administrationRoot), 'utf8');
}

test('die Medienerweiterung erhält Shopwares native Custom Fields und ergänzt nur Bildmedien', async () => {
    const template = await readAdministrationFile('extension/sw-media-quickinfo/sw-media-quickinfo.html.twig');

    const parentPosition = template.indexOf('parent()');
    const previewPosition = template.indexOf('<mgd-ai-media-preview');

    assert.match(template, /block sw_media_quickinfo_custom_field_sets/);
    assert.ok(parentPosition >= 0, 'Der native Inhalt muss über parent() erhalten bleiben.');
    assert.ok(previewPosition > parentPosition, 'Die Vorschau muss nach Shopwares nativen Feldern stehen.');
    assert.match(template, /item\.mediaType && item\.mediaType\.name === 'IMAGE'/);
});

test('die Vorschau nutzt nur das lokale Medienobjekt und keine freien HTML- oder Style-Bindings', async () => {
    const template = await readAdministrationFile('component/mgd-ai-media-preview/mgd-ai-media-preview.html.twig');

    assert.match(template, /aria-live="polite"/);
    assert.match(template, /:src="item\.url"/);
    assert.doesNotMatch(template, /v-html/);
    assert.doesNotMatch(template, /:style=/);
    assert.doesNotMatch(template, /https?:\/\//);
});

test('deutsche und englische Snippets besitzen dieselbe vollständige Struktur', async () => {
    const german = JSON.parse(await readAdministrationFile('snippet/de-DE.json'));
    const english = JSON.parse(await readAdministrationFile('snippet/en-GB.json'));

    assert.deepEqual(
        Object.keys(german['mgd-ai-image-labels'].preview.status),
        Object.keys(english['mgd-ai-image-labels'].preview.status),
    );
    assert.equal(german['mgd-ai-image-labels'].preview.status.generated, 'KI-GENERIERT');
    assert.equal(english['mgd-ai-image-labels'].preview.status.generated, 'AI GENERATED');
});
