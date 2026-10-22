import test from 'node:test';
import assert from 'node:assert/strict';
import { selectedImages, buildBulkPatch, saveBulkLabels } from '../../src/Resources/app/administration/src/service/bulk-labels.js';
const image = (id) => ({ id, getEntityName: () => 'media', mediaType: { name: 'IMAGE' } });

test('Auswahl enthält nur eindeutige Bilder, keine Ordner oder Videos', () => {
    assert.deepEqual(selectedImages([image('a'), image('a'), { ...image('b'), mediaType: { name: 'VIDEO' } }, { ...image('c'), getEntityName: () => 'media_folder' }]).map(i => i.id), ['a']);
});
test('Unveränderte Felder fehlen im Patch; ungültige Werte werden abgelehnt', () => {
    assert.deepEqual(buildBulkPatch({ status: 'none', position: '', theme: '' }), { mgd_ai_status: 'none' });
    assert.throws(() => buildBulkPatch({ status: '<script>' }));
});
test('Frische Fremdfelder bleiben erhalten; Sprache und Auswahl sind während des Laufs fest', async () => {
    const items = [image('a'), image('b')];
    const context = { languageId: 'de' };
    const calls = [];
    const repository = {
        get: async (id, ctx) => {
            calls.push([id, ctx.languageId]);
            context.languageId = 'en'; items.pop();
            return { ...image(id), customFields: { foreign: 'keep', mgd_ai_theme: 'dark' } };
        },
        save: async (entity) => {
            assert.deepEqual(entity.customFields, { foreign: 'keep', mgd_ai_theme: 'dark', mgd_ai_status: 'generated' });
        },
    };
    const result = await saveBulkLabels({ items, values: { status: 'generated' }, repository, context, canEdit: () => true });
    assert.deepEqual(result, { saved: ['a', 'b'], failed: [] });
    assert.deepEqual(calls, [['a', 'de'], ['b', 'de']]);
});
test('Ein Fehler bricht weitere Bilder nicht ab und Fortschritt bleibt korrekt', async () => {
    const progress = [];
    const repository = { get: async id => image(id), save: async entity => { if (entity.id === 'b') throw new Error('private server detail'); } };
    const result = await saveBulkLabels({ items: ['a','b','c'].map(image), values: { theme: 'light' }, repository, context: {}, canEdit: () => true, onProgress: done => progress.push(done) });
    assert.deepEqual(result, { saved: ['a','c'], failed: ['b'] });
    assert.deepEqual(progress, [1,2,3]);
});
test('Fehlende Rechte und leere Änderungen erzeugen keinen Request', async () => {
    const repository = { get: () => assert.fail('Unexpected request') };
    await assert.rejects(saveBulkLabels({ items: [image('a')], values: { status: 'none' }, repository, canEdit: () => false }));
    await assert.rejects(saveBulkLabels({ items: [image('a')], values: {}, repository, canEdit: () => true }));
});
test('Inzwischen geänderter Medientyp wird nicht gespeichert', async () => {
    const repository = { get: async () => ({ ...image('a'), mediaType: { name: 'VIDEO' } }), save: () => assert.fail('Unexpected save') };
    assert.deepEqual(await saveBulkLabels({ items: [image('a')], values: { status: 'none' }, repository, canEdit: () => true }), { saved: [], failed: ['a'] });
});
