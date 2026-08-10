import assert from 'node:assert/strict';
import test from 'node:test';

import {
    isImageMedia,
    normalizeBackgroundConfig,
    normalizeBoolean,
    normalizeEditorialAltText,
} from '../../src/Resources/app/administration/src/service/cms-background-config.js';

test('fehlende und manipulierte Werte werden auf feste sichere Werte begrenzt', () => {
    assert.deepEqual(normalizeBackgroundConfig({
        minHeight: '999px;background:url(javascript:alert(1))',
        horizontalPosition: '<script>',
        verticalPosition: 'outside',
        fallbackColor: 'url(https://example.invalid)',
        decorative: 'false',
        altText: { html: '<script>' },
    }), {
        minHeight: '320px',
        horizontalPosition: 'center',
        verticalPosition: 'center',
        fallbackColor: 'neutral-light',
        decorative: false,
        altText: '',
    });
});

test('redaktioneller Alt-Text akzeptiert nur Text und wird begrenzt', () => {
    assert.equal(normalizeEditorialAltText('  Ein sinnvoller Text  '), 'Ein sinnvoller Text');
    assert.equal(normalizeEditorialAltText(['Text']), '');
    assert.equal(normalizeEditorialAltText({ text: 'Text' }), '');
    assert.equal(normalizeEditorialAltText(42), '');
    assert.equal(normalizeEditorialAltText('x'.repeat(600)).length, 512);
});

test('Medienauswahl akzeptiert ausschließlich echte Bildmedien', () => {
    assert.equal(isImageMedia({ mimeType: 'image/png', mediaType: { name: 'IMAGE' } }), true);
    assert.equal(isImageMedia({ mimeType: 'image/svg+xml' }), false);
    assert.equal(isImageMedia({ mimeType: 'application/pdf', mediaType: { name: 'DOCUMENT' } }), false);
    assert.equal(isImageMedia({ mimeType: 'image/png', mediaType: { name: 'DOCUMENT' } }), false);
    assert.equal(isImageMedia({ mimeType: ['image/png'], mediaType: { name: 'IMAGE' } }), false);
    assert.equal(isImageMedia(null), false);
});

test('jede erlaubte Auswahl bleibt unverändert erhalten', () => {
    for (const minHeight of ['240px', '320px', '480px', '640px']) {
        assert.equal(normalizeBackgroundConfig({ minHeight }).minHeight, minHeight);
    }
    for (const horizontalPosition of ['left', 'center', 'right']) {
        assert.equal(normalizeBackgroundConfig({ horizontalPosition }).horizontalPosition, horizontalPosition);
    }
    for (const verticalPosition of ['top', 'center', 'bottom']) {
        assert.equal(normalizeBackgroundConfig({ verticalPosition }).verticalPosition, verticalPosition);
    }
    for (const fallbackColor of ['neutral-light', 'neutral-dark', 'brand']) {
        assert.equal(normalizeBackgroundConfig({ fallbackColor }).fallbackColor, fallbackColor);
    }
});

test('dekorativ akzeptiert ausschließlich echte boolesche Werte', () => {
    assert.equal(normalizeBoolean(true), true);
    assert.equal(normalizeBoolean(false), false);
    assert.equal(normalizeBoolean(1), false);
    assert.equal(normalizeBoolean('true'), false);
});
