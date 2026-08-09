import assert from 'node:assert/strict';
import test from 'node:test';

import {
    getPreviewPresentation,
    normalizePreviewState,
} from '../../src/Resources/app/administration/src/service/preview-state.js';

test('manipulierte Werte ergeben eine unsichtbare Vorschau mit sicheren Standards', () => {
    assert.deepEqual(
        normalizePreviewState({
            status: '<script>',
            position: 'center',
            theme: 'url(javascript:alert(1))',
        }),
        {
            status: 'none',
            position: 'bottom-right',
            theme: 'auto',
            visible: false,
        },
    );
});

test('alle erlaubten Statuswerte werden unverändert normalisiert', () => {
    const statuses = ['none', 'generated', 'partially-generated', 'modified', 'deepfake'];

    for (const status of statuses) {
        const result = normalizePreviewState({ status });

        assert.equal(result.status, status);
        assert.equal(result.visible, status !== 'none');
    }
});

test('alle erlaubten Positionen werden unverändert normalisiert', () => {
    const positions = ['top-left', 'top-right', 'bottom-left', 'bottom-right'];

    for (const position of positions) {
        assert.equal(normalizePreviewState({ position }).position, position);
    }
});

test('alle erlaubten Themes werden unverändert normalisiert', () => {
    const themes = ['auto', 'light', 'dark'];

    for (const theme of themes) {
        assert.equal(normalizePreviewState({ theme }).theme, theme);
    }
});

test('fehlende oder nicht-objektartige Eingaben bleiben sicher', () => {
    const expected = {
        status: 'none',
        position: 'bottom-right',
        theme: 'auto',
        visible: false,
    };

    assert.deepEqual(normalizePreviewState(), expected);
    assert.deepEqual(normalizePreviewState(null), expected);
    assert.deepEqual(normalizePreviewState('generated'), expected);
});

test('die Darstellung besteht ausschließlich aus fest zugeordneten Klassen und Snippet-Schlüsseln', () => {
    assert.deepEqual(
        getPreviewPresentation({
            status: 'deepfake',
            position: 'top-left',
            theme: 'dark',
        }),
        {
            positionClass: 'is--top-left',
            themeClass: 'is--dark',
            labelSnippet: 'mgd-ai-image-labels.preview.status.deepfake',
        },
    );

    assert.deepEqual(
        getPreviewPresentation({
            status: 'url()',
            position: 'center',
            theme: '<style>',
        }),
        {
            positionClass: 'is--bottom-right',
            themeClass: 'is--auto',
            labelSnippet: 'mgd-ai-image-labels.preview.status.none',
        },
    );
});
