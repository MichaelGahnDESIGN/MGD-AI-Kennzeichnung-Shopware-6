import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import test from 'node:test';

/** Shopware 6.7 führt für jedes Plugin-package.json zwingend `npm ci` aus. */
test('das lokale Testpaket besitzt eine passende reproduzierbare npm-Lockdatei', async () => {
    const packageJson = JSON.parse(await readFile(new URL('../../package.json', import.meta.url), 'utf8'));
    const packageLock = JSON.parse(await readFile(new URL('../../package-lock.json', import.meta.url), 'utf8'));

    assert.equal(packageLock.lockfileVersion, 3);
    assert.equal(packageLock.packages[''].name, packageJson.name);
    assert.deepEqual(packageLock.packages[''].dependencies ?? {}, {});
    assert.deepEqual(packageLock.packages[''].devDependencies ?? {}, {});
});
