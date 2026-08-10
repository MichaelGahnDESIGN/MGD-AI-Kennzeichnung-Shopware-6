import assert from 'node:assert/strict';
import { readdir, readFile, stat } from 'node:fs/promises';
import { extname } from 'node:path';
import { spawnSync } from 'node:child_process';
import test from 'node:test';

const administrationRoot = new URL('../../src/Resources/app/administration/src/', import.meta.url);

async function readAdministrationFile(relativePath) {
    return readFile(new URL(relativePath, administrationRoot), 'utf8');
}

/**
 * Liest alle JavaScript-Dateien der Administration rekursiv ein. Der Test
 * bleibt dadurch vollständig, wenn später weitere Komponenten hinzukommen.
 */
async function collectJavaScriptSources(directory = administrationRoot) {
    const entries = await readdir(directory, { withFileTypes: true });
    const sources = [];

    for (const entry of entries) {
        const fileUrl = new URL(entry.name, directory);

        if (entry.isDirectory()) {
            fileUrl.pathname += '/';
            sources.push(...await collectJavaScriptSources(fileUrl));
        } else if (entry.isFile() && entry.name.endsWith('.js')) {
            sources.push({
                fileUrl,
                source: await readFile(fileUrl, 'utf8'),
            });
        }
    }

    return sources;
}

/**
 * Liest alle Administration-Templates rekursiv ein. Shopwares Administration
 * verwendet für die Template-Vererbung eine eigene TwigJS-Erweiterung; der
 * vollständige Bestand verhindert, dass ein späterer Override versehentlich
 * die gleichnamige Storefront-Syntax übernimmt.
 */
async function collectAdministrationTemplates(directory = administrationRoot) {
    const entries = await readdir(directory, { withFileTypes: true });
    const templates = [];

    for (const entry of entries) {
        const fileUrl = new URL(entry.name, directory);

        if (entry.isDirectory()) {
            fileUrl.pathname += '/';
            templates.push(...await collectAdministrationTemplates(fileUrl));
        } else if (entry.isFile() && entry.name.endsWith('.html.twig')) {
            templates.push({
                fileUrl,
                source: await readFile(fileUrl, 'utf8'),
            });
        }
    }

    return templates;
}

/**
 * Nutzt Nodes echten ECMAScript-Parser statt regulärer Ausdrücke. So werden
 * Kommentare oder Zeichenketten mit dem Wort `import` nicht fälschlich als
 * Modulabhängigkeit gewertet.
 */
function parseStaticImports(sources) {
    const parser = String.raw`
        import { SourceTextModule } from 'node:vm';

        let input = '';
        for await (const chunk of process.stdin) input += chunk;

        const sources = JSON.parse(input);
        const imports = sources.map(({ identifier, source }) => ({
            identifier,
            specifiers: new SourceTextModule(source, { identifier }).dependencySpecifiers,
        }));

        process.stdout.write(JSON.stringify(imports));
    `;
    const input = sources.map(({ fileUrl, source }) => ({
        identifier: fileUrl.href,
        source,
    }));
    const result = spawnSync(
        process.execPath,
        ['--experimental-vm-modules', '--input-type=module', '--eval', parser],
        { input: JSON.stringify(input), encoding: 'utf8' },
    );

    assert.equal(result.status, 0, result.stderr);

    return JSON.parse(result.stdout);
}

/**
 * Ermittelt rekursiv jeden Schlüsselpfad eines Snippet-Objekts. Dadurch fällt
 * auch ein fehlender Zwischenknoten oder einzelner Übersetzungstext auf.
 */
function collectSnippetKeyPaths(value, prefix = '') {
    if (typeof value !== 'object' || value === null) {
        return [];
    }

    return Object.entries(value).flatMap(([key, child]) => {
        const path = prefix === '' ? key : `${prefix}.${key}`;

        return [path, ...collectSnippetKeyPaths(child, path)];
    });
}

test('der Administrationseinstieg registriert deutsche und englische Snippets explizit', async () => {
    const main = await readAdministrationFile('main.js');

    assert.match(main, /import deDE from '.\/snippet\/de-DE\.json';/);
    assert.match(main, /import enGB from '.\/snippet\/en-GB\.json';/);
    assert.match(main, /Shopware\.Locale\.extend\('de-DE', deDE\);/);
    assert.match(main, /Shopware\.Locale\.extend\('en-GB', enGB\);/);
});

test('alle relativen Administration-Imports benennen eine vorhandene Datei mit Endung', async () => {
    const sources = await collectJavaScriptSources();
    const parsedImports = parseStaticImports(sources);

    for (const { identifier, specifiers } of parsedImports) {
        for (const specifier of specifiers.filter((value) => value.startsWith('.'))) {
            assert.notEqual(
                extname(specifier),
                '',
                `${identifier}: Relativer Import ohne Dateiendung: ${specifier}`,
            );

            const importedFile = new URL(specifier, identifier);
            const importedFileStat = await stat(importedFile).catch(() => null);

            assert.ok(
                importedFileStat?.isFile(),
                `${identifier}: Relative Importdatei fehlt: ${specifier}`,
            );
        }
    }
});

test('Administration-Overrides verwenden ausschließlich Shopwares TwigJS-Parent-Tag', async () => {
    const templates = await collectAdministrationTemplates();

    for (const { fileUrl, source } of templates) {
        assert.doesNotMatch(
            source,
            /\{\{\s*parent\s*\(\s*\)\s*\}\}/,
            `${fileUrl.href}: Die Storefront-Syntax {{ parent() }} ist in der Administration ungültig.`,
        );
    }
});

test('die Medienerweiterung erhält Shopwares native Custom Fields und ergänzt nur Bildmedien', async () => {
    const template = await readAdministrationFile('extension/sw-media-quickinfo/sw-media-quickinfo.html.twig');

    const parentTags = template.match(/\{%\s*parent\s*%\}/g) ?? [];
    const parentPosition = template.search(/\{%\s*parent\s*%\}/);
    const previewPosition = template.indexOf('<mgd-ai-media-preview');

    assert.match(template, /block sw_media_quickinfo_custom_field_sets/);
    assert.equal(parentTags.length, 1, 'Der native Inhalt muss genau einmal über {% parent %} erhalten bleiben.');
    assert.ok(previewPosition > parentPosition, 'Die Vorschau muss nach Shopwares nativen Feldern stehen.');
    assert.match(template, /item\.mediaType && item\.mediaType\.name === 'IMAGE'/);
});

test('die Vorschau nutzt nur das lokale Medienobjekt und keine freien HTML- oder Style-Bindings', async () => {
    // Der gemountete Vue-Test benötigt Shopwares vollständiges Test-Harness und
    // folgt deshalb mit den echten 6.6-/6.7-Administration-Builds in Task 13.
    // Dieser unabhängige Vertragstest schützt bis dahin die sicherheits- und
    // barrierefreiheitsrelevante Template-Struktur ohne Schein-Mocks.
    const template = await readAdministrationFile('component/mgd-ai-media-preview/mgd-ai-media-preview.html.twig');

    assert.match(template, /aria-live="polite"/);
    assert.match(template, /:src="item\.url"/);
    assert.doesNotMatch(template, /v-html/);
    assert.doesNotMatch(template, /:style=/);
    assert.doesNotMatch(template, /https?:\/\//);
    assert.match(template, /previewPresentation\.labelSnippet/);
    assert.match(template, /previewPresentation\.positionSnippet/);
    assert.match(template, /previewPresentation\.themeSnippet/);
});

test('deutsche und englische Snippets besitzen dieselbe vollständige Struktur', async () => {
    const german = JSON.parse(await readAdministrationFile('snippet/de-DE.json'));
    const english = JSON.parse(await readAdministrationFile('snippet/en-GB.json'));

    assert.deepEqual(collectSnippetKeyPaths(german).sort(), collectSnippetKeyPaths(english).sort());
    assert.equal(german['mgd-ai-image-labels'].preview.status.generated, 'KI-GENERIERT');
    assert.equal(english['mgd-ai-image-labels'].preview.status.generated, 'AI GENERATED');
});
