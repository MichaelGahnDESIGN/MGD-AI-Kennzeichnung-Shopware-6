import assert from 'node:assert/strict';
import test from 'node:test';

import {
    PREPARE_PHILOSOPHY_PAGE_PATH,
    preparePhilosophyPage,
} from '../../src/Resources/app/administration/src/service/prepare-philosophy-page-api.js';

test('sendet die Philosophie-Aktion ausschließlich als authentifizierten POST', async () => {
    const authenticationHeaders = {
        Authorization: 'Bearer test-token',
        Accept: 'application/vnd.api+json',
        'Content-Type': 'application/json',
    };
    const calls = [];
    const httpClient = {
        post: async (...parameters) => {
            calls.push(parameters);
            return { data: { created: true, cmsPageId: 'a'.repeat(32) } };
        },
        get: () => assert.fail('Die Aktion darf nie als GET gesendet werden.'),
        put: () => assert.fail('Die Aktion darf nie als PUT gesendet werden.'),
    };
    const authenticatedApiService = {
        getBasicHeaders: () => authenticationHeaders,
    };

    const response = await preparePhilosophyPage(httpClient, authenticatedApiService);

    assert.deepEqual(response, { data: { created: true, cmsPageId: 'a'.repeat(32) } });
    assert.deepEqual(calls, [[
        PREPARE_PHILOSOPHY_PAGE_PATH,
        {},
        { headers: authenticationHeaders },
    ]]);
});

test('bricht ohne HTTP-Client kontrolliert vor jedem Request ab', async () => {
    let headerCalls = 0;

    await assert.rejects(
        preparePhilosophyPage(null, { getBasicHeaders: () => { headerCalls += 1; } }),
        { name: 'TypeError', message: 'Authentifizierter Administrationszugriff ist nicht verfügbar.' },
    );

    assert.equal(headerCalls, 0);
});

test('bricht ohne authentifizierten API-Dienst kontrolliert vor jedem Request ab', async () => {
    let requestCalls = 0;
    const httpClient = {
        post: () => { requestCalls += 1; },
    };

    await assert.rejects(
        preparePhilosophyPage(httpClient, null),
        { name: 'TypeError', message: 'Authentifizierter Administrationszugriff ist nicht verfügbar.' },
    );

    assert.equal(requestCalls, 0);
});

test('ersetzt rohe Serverfehler durch eine kontrollierte lokale Meldung', async () => {
    const rawServerMessage = 'SQLSTATE: geheimes Detail';
    const httpClient = {
        post: async () => { throw new Error(rawServerMessage); },
    };

    await assert.rejects(
        preparePhilosophyPage(httpClient, { getBasicHeaders: () => ({ Authorization: 'Bearer test' }) }),
        (error) => error instanceof Error
            && error.message === 'Die Philosophie-Seite konnte nicht vorbereitet werden.'
            && !error.message.includes(rawServerMessage),
    );
});
