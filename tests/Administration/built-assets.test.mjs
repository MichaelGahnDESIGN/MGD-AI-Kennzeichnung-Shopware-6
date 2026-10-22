import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';
const root = new URL('../../src/Resources/public/administration/', import.meta.url);
test('das ausgelieferte Bundle registriert Bulk-Editor und native Mehrfachauswahl', () => {
    const manifest = JSON.parse(readFileSync(new URL('.vite/manifest.json', root)));
    const registrations = new Map();
    const overrides = new Map();
    const locales = new Map();
    const Shopware = {
        Component: { register: (name, value) => registrations.set(name, value), override: (name, value) => overrides.set(name, value) },
        Locale: { extend: (name, value) => locales.set(name, value) },
        Service: () => ({ registerCmsElement() {} }),
        Module: { register() {} },
        Mixin: { getByName: () => ({}) },
    };
    vm.runInNewContext(readFileSync(new URL(manifest['main.js'].file, root), 'utf8'), { Shopware, window: {}, console });
    assert.ok(registrations.has('mgd-ai-media-bulk'));
    assert.match(overrides.get('sw-media-quickinfo-multiple').template, /mgd-ai-media-bulk/);
    assert.equal(locales.get('de-DE')['mgd-ai-image-labels'].bulk.title, 'KI-Kennzeichnung');
    for (const css of manifest['main.js'].css) assert.ok(readFileSync(new URL(css, root)).length > 0);
});
