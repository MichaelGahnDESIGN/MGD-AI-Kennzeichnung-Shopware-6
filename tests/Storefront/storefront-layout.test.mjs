import assert from 'node:assert/strict';
import { execFile } from 'node:child_process';
import { access, mkdtemp, readFile, rm, writeFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { pathToFileURL } from 'node:url';
import { promisify } from 'node:util';
import test from 'node:test';

const execFileAsync = promisify(execFile);
const componentPath = new URL('../../src/Resources/app/storefront/src/scss/component/_ai-image-label.scss', import.meta.url);

/** Ermittelt bewusst nur lokal installierte Chromium-Browser. */
async function findBrowser() {
    const candidates = [
        process.env.MGD_HEADLESS_BROWSER,
        '/opt/homebrew/bin/chromium',
        '/usr/bin/chromium',
        '/usr/bin/chromium-browser',
        '/usr/bin/google-chrome',
        '/usr/bin/google-chrome-stable',
        '/snap/bin/chromium',
        '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
        '/Applications/Chromium.app/Contents/MacOS/Chromium',
    ].filter(Boolean);

    for (const candidate of candidates) {
        try {
            await access(candidate);
            await execFileAsync(candidate, ['--version']);
            return candidate;
        } catch {
            // Der nächste feste lokale Kandidat wird geprüft.
        }
    }

    throw new Error('Kein lokaler Chromium-Browser gefunden. MGD_HEADLESS_BROWSER kann einen festen Pfad vorgeben.');
}

/** Entfernt nur SCSS-Zeilenkommentare; die Komponente nutzt ansonsten valides CSS. */
function browserCss(scss) {
    return scss
        .split('\n')
        .filter((line) => !line.trimStart().startsWith('//'))
        .join('\n');
}

function fixture(id, layout, width, height, values, text) {
    const wrapper = `<div id="${id}" class="mgd-ai-labeled-media mgd-ai-labeled-media--${layout}">`;
    const medium = `<div class="fixture-medium" style="width:${width}px;height:${height}px"></div>`;
    const badge = `<div class="mgd-ai-labeled-media__overlay"><span class="mgd-ai-image-label mgd-ai-image-label--top-right mgd-ai-image-label--theme-dark" role="note" style="--mgd-ai-font-size:${values.fontSize}px;--mgd-ai-offset:${values.offset}px;--mgd-ai-padding-y:${values.paddingY}px;--mgd-ai-padding-x:${values.paddingX}px;--mgd-ai-radius:${values.radius}px;--mgd-ai-blur:${values.blur}px"><span class="mgd-ai-image-label__text">${text}</span></span></div>`;

    if (layout === 'fill') {
        return `<div class="fixture-slot" style="width:${width}px">${wrapper}${medium}${badge}</div></div>`;
    }

    return `${wrapper}${medium}${badge}</div>`;
}

function testDocument(css) {
    const standard = { fontSize: 6, offset: 12, paddingY: 5, paddingX: 9, radius: 999, blur: 10 };
    const maximum = { fontSize: 24, offset: 96, paddingY: 24, paddingX: 40, radius: 999, blur: 24 };

    return `<!doctype html>
<html><head><meta charset="utf-8"><style>
* { box-sizing: border-box; }
body { margin: 0; font-size: 16px; }
.fixture-slot { display: block; }
.fixture-medium { display: block; background: #777; }
${css}
</style></head><body>
${fixture('small-fill', 'fill', 40, 40, maximum, 'PARTIALLY AI-GENERATED')}
${fixture('small-intrinsic', 'intrinsic', 80, 80, standard, 'AI GENERATED')}
${fixture('minimum-fill-standard', 'fill', 128, 128, standard, 'PARTIALLY AI-GENERATED')}
${fixture('minimum-intrinsic-maximum', 'intrinsic', 128, 128, maximum, 'PARTIALLY AI-GENERATED')}
${fixture('large-fill-maximum', 'fill', 240, 160, maximum, 'PARTIALLY AI-GENERATED')}
<script>
(() => {
    const ids = ['small-fill', 'small-intrinsic', 'minimum-fill-standard', 'minimum-intrinsic-maximum', 'large-fill-maximum'];
    const result = Object.fromEntries(ids.map((id) => {
        const wrapper = document.getElementById(id);
        const medium = wrapper.querySelector('.fixture-medium');
        const badge = wrapper.querySelector('.mgd-ai-image-label');
        const textElement = badge.querySelector('.mgd-ai-image-label__text');
        const rectangle = (element) => {
            const value = element.getBoundingClientRect();
            return { left: value.left, top: value.top, right: value.right, bottom: value.bottom, width: value.width, height: value.height };
        };
        const style = getComputedStyle(badge);
        const textStyle = getComputedStyle(textElement);
        return [id, {
            wrapper: rectangle(wrapper),
            medium: rectangle(medium),
            badge: rectangle(badge),
            display: style.display,
            whiteSpace: textStyle.whiteSpace,
            textOverflow: textStyle.textOverflow,
            text: badge.textContent,
        }];
    }));
    document.body.dataset.result = encodeURIComponent(JSON.stringify(result));
})();
</script></body></html>`;
}

function assertSize(rectangle, width, height, label) {
    assert.ok(Math.abs(rectangle.width - width) < 0.1, `${label}: Breite ${rectangle.width} statt ${width}`);
    assert.ok(Math.abs(rectangle.height - height) < 0.1, `${label}: Höhe ${rectangle.height} statt ${height}`);
}

function assertInside(wrapper, badge, label) {
    const tolerance = 0.1;
    assert.ok(badge.width > 0 && badge.height > 0, `${label}: sichtbares Badge benötigt Geometrie.`);
    assert.ok(badge.left >= wrapper.left - tolerance, `${label}: Badge ragt links heraus.`);
    assert.ok(badge.top >= wrapper.top - tolerance, `${label}: Badge ragt oben heraus.`);
    assert.ok(badge.right <= wrapper.right + tolerance, `${label}: Badge ragt rechts heraus.`);
    assert.ok(badge.bottom <= wrapper.bottom + tolerance, `${label}: Badge ragt unten heraus.`);
}

test('Badges bleiben in echten kleinen und großen Mediengeometrien sicher', async () => {
    const browser = await findBrowser();
    const temporaryDirectory = await mkdtemp(join(tmpdir(), 'mgd-ai-layout-'));
    const htmlPath = join(temporaryDirectory, 'layout.html');

    try {
        const css = browserCss(await readFile(componentPath, 'utf8'));
        await writeFile(htmlPath, testDocument(css), 'utf8');
        const { stdout } = await execFileAsync(browser, [
            '--headless=new',
            '--disable-gpu',
            '--disable-dev-shm-usage',
            '--no-sandbox',
            '--run-all-compositor-stages-before-draw',
            '--virtual-time-budget=1000',
            '--dump-dom',
            pathToFileURL(htmlPath).href,
        ], { maxBuffer: 2_000_000 });
        const encodedResult = stdout.match(/data-result="([^"]+)"/)?.[1];
        assert.ok(encodedResult, `Chromium muss die gemessenen Geometriedaten ausgeben: ${stdout.slice(-1200)}`);
        const result = JSON.parse(decodeURIComponent(encodedResult));

        assertSize(result['small-fill'].wrapper, 40, 40, '40px fill wrapper');
        assertSize(result['small-fill'].medium, 40, 40, '40px fill medium');
        assert.equal(result['small-fill'].display, 'none');

        assertSize(result['small-intrinsic'].wrapper, 80, 80, '80px intrinsic wrapper');
        assertSize(result['small-intrinsic'].medium, 80, 80, '80px intrinsic medium');
        assert.equal(result['small-intrinsic'].display, 'none');

        for (const id of ['minimum-fill-standard', 'minimum-intrinsic-maximum', 'large-fill-maximum']) {
            assert.notEqual(result[id].display, 'none', `${id}: Badge muss ab 128px sichtbar sein.`);
            assertInside(result[id].wrapper, result[id].badge, id);
            assert.equal(result[id].whiteSpace, 'nowrap');
            assert.equal(result[id].textOverflow, 'ellipsis');
            assert.equal(result[id].text, 'PARTIALLY AI-GENERATED');
        }

        assertSize(result['minimum-fill-standard'].wrapper, 128, 128, '128px fill wrapper');
        assertSize(result['minimum-fill-standard'].medium, 128, 128, '128px fill medium');
        assertSize(result['minimum-intrinsic-maximum'].wrapper, 128, 128, '128px intrinsic wrapper');
        assertSize(result['minimum-intrinsic-maximum'].medium, 128, 128, '128px intrinsic medium');
        assertSize(result['large-fill-maximum'].wrapper, 240, 160, '240px fill wrapper');
        assertSize(result['large-fill-maximum'].medium, 240, 160, '240px fill medium');
    } finally {
        await rm(temporaryDirectory, { recursive: true, force: true });
    }
});
