import assert from 'node:assert/strict';
import test from 'node:test';

import { activeContentLanguageId } from '../../src/Resources/app/administration/src/service/philosophy-content.js';

test('Shopware 6.6 liest den Vuex-Kontext ohne den nicht registrierten Pinia-Kontext aufzurufen', () => {
    let storeReads = 0;
    const shopware = {
        Store: {
            list: () => ['cmsPage'],
            get: () => {
                storeReads += 1;
                throw new Error('Store with id "context" not found');
            },
        },
        State: { get: () => ({ api: { languageId: 'content-de' } }) },
    };

    assert.equal(activeContentLanguageId(shopware), 'content-de');
    assert.equal(storeReads, 0);
});

test('Shopware 6.6 fängt eine fehlende oder werfende Store-API sicher ab', () => {
    assert.equal(activeContentLanguageId({
        State: { get: () => ({ api: { languageId: 'state-without-store' } }) },
    }), 'state-without-store');

    const throwingStore = {
        Store: { get: () => { throw new Error('Store with id "context" not found'); } },
        State: { get: () => ({ api: { languageId: 'state-after-store-error' } }) },
    };
    assert.doesNotThrow(() => activeContentLanguageId(throwingStore));
    assert.equal(activeContentLanguageId(throwingStore), 'state-after-store-error');
});

test('werfende Store- und Methoden-Getter lassen den Vuex-Fallback unverändert erreichbar', () => {
    const state = { get: () => ({ api: { languageId: 'safe-state' } }) };
    const throwingStoreProperty = {
        get Store() {
            throw new Error('Store getter failed');
        },
        State: state,
    };
    const throwingGetProperty = {
        Store: {
            get get() {
                throw new Error('get getter failed');
            },
        },
        State: state,
    };
    const throwingListProperty = {
        Store: {
            get: () => ({ api: { languageId: 'unreachable-store' } }),
            get list() {
                throw new Error('list getter failed');
            },
        },
        State: state,
    };

    for (const shopware of [throwingStoreProperty, throwingGetProperty, throwingListProperty]) {
        assert.doesNotThrow(() => activeContentLanguageId(shopware));
        assert.equal(activeContentLanguageId(shopware), 'safe-state');
    }
});

test('Shopware 6.7 bevorzugt den registrierten Pinia-Kontext vor veraltetem Vuex-Zustand', () => {
    assert.equal(activeContentLanguageId({
        Store: {
            list: () => ['context'],
            get: () => ({ api: { languageId: 'pinia-67' } }),
        },
        State: { get: () => ({ api: { languageId: 'stale-vuex-66' } }) },
    }), 'pinia-67');
});

test('ungültige Store-Werte fallen sicher auf State und globalen API-Kontext zurück', () => {
    assert.equal(activeContentLanguageId({
        Store: { get: () => ({ api: { languageId: '   ' } }) },
        State: { get: () => ({ api: { languageId: 'valid-state' } }) },
    }), 'valid-state');

    const throwingState = {
        Store: { get: () => ({ api: null }) },
        State: { get: () => { throw new Error('Vuex context not found'); } },
        Context: { api: { languageId: 'fallback-id' } },
    };
    assert.doesNotThrow(() => activeContentLanguageId(throwingState));
    assert.equal(activeContentLanguageId(throwingState), 'fallback-id');
});

test('vollständig fehlende oder fehlerhafte Sprachwerte ergeben null', () => {
    assert.equal(activeContentLanguageId({
        Store: { get: () => ({ api: { languageId: [] } }) },
        State: { get: () => ({ api: { languageId: {} } }) },
        Context: { api: { languageId: '' } },
    }), null);
    assert.equal(activeContentLanguageId(undefined), null);
});
