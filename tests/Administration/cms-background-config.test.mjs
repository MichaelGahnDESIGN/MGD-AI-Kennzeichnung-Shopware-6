import assert from 'node:assert/strict';
import test from 'node:test';

import {
    normalizeBackgroundConfig,
    normalizeBoolean,
} from '../../src/Resources/app/administration/src/service/cms-background-config.js';

test('fehlende und manipulierte Werte werden auf feste sichere Werte begrenzt', () => {
    assert.deepEqual(normalizeBackgroundConfig({
        minHeight: '999px;background:url(javascript:alert(1))',
        horizontalPosition: '<script>',
        verticalPosition: 'outside',
        fallbackColor: 'url(https://example.invalid)',
        decorative: 'false',
    }), {
        minHeight: '320px',
        horizontalPosition: 'center',
        verticalPosition: 'center',
        fallbackColor: 'neutral-light',
        decorative: false,
    });
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
