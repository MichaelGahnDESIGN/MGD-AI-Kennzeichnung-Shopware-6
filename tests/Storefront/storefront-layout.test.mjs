import assert from 'node:assert/strict';
import { execFile, spawn } from 'node:child_process';
import { once } from 'node:events';
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

function fixture(id, layout, width, height, values, text, options = {}) {
    const wrapper = `<div id="${id}" class="mgd-ai-labeled-media mgd-ai-labeled-media--${layout}">`;
    const transform = options.transform ? `;transform:${options.transform}` : '';
    const medium = `<div class="fixture-medium" style="width:${width}px;height:${height}px${transform}"></div>`;
    const screenReaderText = options.screenReaderText ? `<span class="visually-hidden">${options.screenReaderText}</span>` : '';
    const badge = `<div class="mgd-ai-labeled-media__overlay"><span class="mgd-ai-image-label mgd-ai-image-label--top-right mgd-ai-image-label--theme-dark" role="note" style="--mgd-ai-font-size:${values.fontSize}px;--mgd-ai-offset:${values.offset}px;--mgd-ai-padding-y:${values.paddingY}px;--mgd-ai-padding-x:${values.paddingX}px;--mgd-ai-radius:${values.radius}px;--mgd-ai-blur:${values.blur}px"><span class="mgd-ai-image-label__text">${text}</span>${screenReaderText}</span></div>`;

    if (layout === 'fill') {
        return `<div class="fixture-slot" style="width:${width}px">${wrapper}${medium}${badge}</div></div>`;
    }

    return `${wrapper}${medium}${badge}</div>`;
}

/**
 * Stellt dasselbe Shopware-Medium einmal unverändert und einmal im Labelrahmen
 * dar. So werden Abmessungen und sichtbestimmende CSS-Eigenschaften verglichen.
 */
function presentationPair(id, layout, mediaClass) {
    const original = `<div id="${id}-original-slot" class="geometry-slot"><div id="${id}-original" class="${mediaClass}"></div></div>`;
    const wrapped = `<div id="${id}-wrapped-slot" class="geometry-slot"><div id="${id}-wrapper" class="mgd-ai-labeled-media mgd-ai-labeled-media--${layout}"><div id="${id}-wrapped" class="${mediaClass}"></div><div class="mgd-ai-labeled-media__overlay"></div></div></div>`;

    return `<div class="geometry-pair geometry-${id}">${original}${wrapped}</div>`;
}

/** Ein echtes 160×90-SVG liefert dem Browser natürliche Bildabmessungen. */
const naturalImage = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='90'%3E%3Crect width='160' height='90' fill='%232878b4'/%3E%3C/svg%3E";

function naturalImagePair(id, layout, mediaClass) {
    const original = `<div id="${id}-original-slot" class="geometry-slot"><img id="${id}-original" class="${mediaClass}" src="${naturalImage}" alt=""></div>`;
    const wrapped = `<div id="${id}-wrapped-slot" class="geometry-slot"><div id="${id}-wrapper" class="mgd-ai-labeled-media mgd-ai-labeled-media--${layout}"><img id="${id}-wrapped" class="${mediaClass}" src="${naturalImage}" alt=""><div class="mgd-ai-labeled-media__overlay"></div></div></div>`;

    return `<div class="geometry-pair geometry-${id}">${original}${wrapped}</div>`;
}

/** Bild und nachfolgender Text bilden Shopwares echte Float-Nachbarschaft nach. */
function floatingMethodPair(id, mediaClass, direction = 'ltr') {
    const original = `<div class="method-slot"><div id="${id}-original-row" class="method-row" dir="${direction}"><img id="${id}-original" class="${mediaClass}" src="${naturalImage}" alt=""><span id="${id}-original-sibling">Beschreibung der Methode mit ausreichend langem Text</span></div></div>`;
    const wrapped = `<div class="method-slot"><div id="${id}-wrapped-row" class="method-row" dir="${direction}"><div id="${id}-wrapper" class="mgd-ai-labeled-media mgd-ai-labeled-media--intrinsic mgd-ai-labeled-media--intrinsic-float-end"><img id="${id}-wrapped" class="${mediaClass}" src="${naturalImage}" alt=""><div class="mgd-ai-labeled-media__overlay"></div></div><span id="${id}-wrapped-sibling">Beschreibung der Methode mit ausreichend langem Text</span></div></div>`;

    return `<div class="method-pair method-${id}">${original}${wrapped}</div>`;
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
.geometry-pair { display: flex; align-items: flex-start; gap: 8px; margin-top: 8px; }
.geometry-slot { flex: 0 0 auto; position: relative; }
.geometry-pair [id$="-original"], .geometry-pair [id$="-wrapped"] { display: block; background: rgb(40, 120, 180); }
.geometry-cms-standard .geometry-slot { width: 300px; }
.geometry-cms-standard .cms-image { width: 160px; height: 90px; max-width: 100%; }
.geometry-cms-cover .geometry-slot { width: 300px; height: 180px; }
.geometry-cms-cover .cms-image { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
.geometry-cms-stretch .geometry-slot { width: 300px; }
.geometry-cms-stretch .cms-image { width: 100%; height: 169px; }
.geometry-listing .geometry-slot { width: 200px; height: 200px; }
.geometry-listing .product-image { width: 100%; height: 100%; object-fit: contain; transition: transform 120ms ease; }
.geometry-gallery-cover .geometry-slot { width: 300px; height: 180px; }
.geometry-gallery-cover .gallery-slider-image { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transform: scale(0.98); }
.geometry-gallery-standard .geometry-slot { width: 300px; }
.geometry-gallery-standard .gallery-slider-image { width: 180px; height: 120px; max-width: 100%; }
.geometry-cart .geometry-slot { width: 70px; }
.geometry-cart .line-item-img { width: 100%; height: 70px; padding: 8px; border: 1px solid rgb(80, 80, 80); border-radius: 4px; object-fit: contain; }
.geometry-logo .geometry-slot { width: 240px; }
.geometry-logo .footer-logo-image { width: 100px; height: 35px; max-width: 100px; max-height: 35px; }
.geometry-search .geometry-slot { width: 35px; height: 35px; }
.geometry-search .search-suggest-product-image { width: 35px; height: 35px; max-width: 35px; max-height: 35px; }
.geometry-configurator .geometry-slot { width: 52px; height: 52px; }
.geometry-configurator .product-detail-configurator-option-image { width: 40px; height: 100%; }
.geometry-image-slider .geometry-slot { width: 240px; }
.geometry-image-slider .image-slider-image { width: 100%; height: 135px; }
.geometry-navigation .geometry-slot { width: 310px; }
.geometry-navigation .img-fluid { max-width: 100%; height: auto; }
.method-pair { display: flex; align-items: flex-start; gap: 8px; margin-top: 8px; }
.method-slot, .method-row { width: 320px; }
.method-row { color: rgb(20, 20, 20); font-size: 16px; line-height: 20px; }
.payment-method-image, .shipping-method-image { max-height: 24px; max-width: 100%; margin-right: 4px; margin-left: 4px; float: right; }
.geometry-video .geometry-slot, .geometry-background .geometry-slot { width: 320px; height: 180px; }
.geometry-video .video-placeholder-image, .geometry-background .cms-block-background { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
.visually-hidden { position: absolute !important; width: 1px !important; height: 1px !important; padding: 0 !important; margin: -1px !important; overflow: hidden !important; clip: rect(0, 0, 0, 0) !important; white-space: nowrap !important; border: 0 !important; }
${css}
</style></head><body>
${fixture('small-fill', 'fill', 40, 40, maximum, 'DEEPFAKE', { screenReaderText: 'Notice: This image is labeled as a deepfake.' })}
${fixture('small-intrinsic', 'intrinsic', 80, 80, standard, 'AI GENERATED')}
${fixture('flat-fill', 'fill', 240, 40, maximum, 'AI-MODIFIED')}
${fixture('minimum-fill-standard', 'fill', 128, 128, standard, 'PARTIALLY AI-GENERATED')}
${fixture('minimum-intrinsic-maximum', 'intrinsic', 128, 128, maximum, 'PARTIALLY AI-GENERATED')}
${fixture('large-fill-maximum', 'fill', 240, 160, maximum, 'PARTIALLY AI-GENERATED')}
${fixture('overflow-intrinsic', 'intrinsic', 128, 128, standard, 'AI GENERATED', { transform: 'translateX(24px)' })}
${presentationPair('cms-standard', 'intrinsic', 'cms-image')}
${presentationPair('cms-cover', 'fill', 'cms-image')}
${presentationPair('cms-stretch', 'fill', 'cms-image')}
${presentationPair('listing', 'fill', 'product-image')}
${presentationPair('gallery-cover', 'fill', 'gallery-slider-image')}
${presentationPair('gallery-standard', 'intrinsic', 'gallery-slider-image')}
${presentationPair('cart', 'fill', 'line-item-img')}
${presentationPair('logo', 'intrinsic', 'footer-logo-image')}
${presentationPair('search', 'intrinsic', 'search-suggest-product-image')}
${presentationPair('configurator', 'fill', 'product-detail-configurator-option-image')}
${presentationPair('image-slider', 'fill', 'image-slider-image')}
${naturalImagePair('navigation', 'intrinsic', 'navigation-flyout-teaser-image img-fluid')}
${presentationPair('video', 'fill', 'video-placeholder-image')}
${presentationPair('background', 'fill', 'cms-block-background')}
${floatingMethodPair('payment', 'payment-method-image')}
${floatingMethodPair('shipping', 'shipping-method-image')}
${floatingMethodPair('payment-rtl', 'payment-method-image', 'rtl')}
${floatingMethodPair('shipping-rtl', 'shipping-method-image', 'rtl')}
<script>
(async () => {
    await Promise.all([...document.images].map((image) => image.decode()));
    const ids = ['small-fill', 'small-intrinsic', 'flat-fill', 'minimum-fill-standard', 'minimum-intrinsic-maximum', 'large-fill-maximum', 'overflow-intrinsic'];
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
            visibility: style.visibility,
            clipPath: style.clipPath,
            ariaHidden: badge.getAttribute('aria-hidden'),
            wrapperOverflow: getComputedStyle(wrapper).overflow,
            overlayOverflow: getComputedStyle(wrapper.querySelector('.mgd-ai-labeled-media__overlay')).overflow,
            whiteSpace: textStyle.whiteSpace,
            textOverflow: textStyle.textOverflow,
            text: badge.textContent,
        }];
    }));
    const presentationIds = ['cms-standard', 'cms-cover', 'cms-stretch', 'listing', 'gallery-cover', 'gallery-standard', 'cart', 'logo', 'search', 'configurator', 'image-slider', 'navigation', 'video', 'background'];
    result.presentationPairs = Object.fromEntries(presentationIds.map((id) => {
        const original = document.getElementById(id + '-original');
        const wrapped = document.getElementById(id + '-wrapped');
        const wrapper = document.getElementById(id + '-wrapper');
        const rectangle = (element) => {
            const value = element.getBoundingClientRect();
            return { left: value.left, top: value.top, right: value.right, bottom: value.bottom, width: value.width, height: value.height };
        };
        const paint = (element) => {
            const style = getComputedStyle(element);
            return {
                objectFit: style.objectFit,
                transform: style.transform,
                padding: style.padding,
                borderWidth: style.borderWidth,
                borderRadius: style.borderRadius,
                backgroundColor: style.backgroundColor,
            };
        };

        return [id, {
            original: rectangle(original),
            wrapped: rectangle(wrapped),
            wrapper: rectangle(wrapper),
            originalSlot: rectangle(document.getElementById(id + '-original-slot')),
            wrappedSlot: rectangle(document.getElementById(id + '-wrapped-slot')),
            originalPaint: paint(original),
            wrappedPaint: paint(wrapped),
        }];
    }));
    result.methodPairs = Object.fromEntries(['payment', 'shipping', 'payment-rtl', 'shipping-rtl'].map((id) => {
        const rectangle = (element) => {
            const value = element.getBoundingClientRect();
            return { left: value.left, top: value.top, right: value.right, bottom: value.bottom, width: value.width, height: value.height };
        };

        return [id, {
            original: rectangle(document.getElementById(id + '-original')),
            wrapped: rectangle(document.getElementById(id + '-wrapped')),
            wrapper: rectangle(document.getElementById(id + '-wrapper')),
            originalRow: rectangle(document.getElementById(id + '-original-row')),
            wrappedRow: rectangle(document.getElementById(id + '-wrapped-row')),
            originalSibling: rectangle(document.getElementById(id + '-original-sibling')),
            wrappedSibling: rectangle(document.getElementById(id + '-wrapped-sibling')),
        }];
    }));
    document.body.dataset.result = encodeURIComponent(JSON.stringify(result));
})();
</script></body></html>`;
}

/** Öffnet eine lokale Datei per Chrome DevTools Protocol und liest Geometrie sowie AX-Baum. */
async function inspectWithCdp(browser, htmlPath, temporaryDirectory) {
    const browserProcess = spawn(browser, [
        '--headless=new',
        '--disable-gpu',
        '--disable-dev-shm-usage',
        '--no-sandbox',
        '--remote-debugging-port=0',
        `--user-data-dir=${join(temporaryDirectory, 'chrome-profile')}`,
        'about:blank',
    ], { stdio: ['ignore', 'ignore', 'pipe'] });

    let socket;

    const bounded = async (promise, label, milliseconds = 15_000) => {
        let timeout;

        try {
            return await Promise.race([
                promise,
                new Promise((resolve, reject) => {
                    timeout = setTimeout(() => reject(new Error(`${label} hat das Zeitlimit überschritten.`)), milliseconds);
                }),
            ]);
        } finally {
            clearTimeout(timeout);
        }
    };

    try {
        const browserSocketUrl = await new Promise((resolve, reject) => {
            let diagnostics = '';
            const timeout = setTimeout(
                () => reject(new Error(`Chromium hat keinen CDP-Endpunkt geöffnet: ${diagnostics}`)),
                30_000,
            );

            browserProcess.stderr.on('data', (chunk) => {
                diagnostics += chunk.toString();
                const match = diagnostics.match(/DevTools listening on (ws:\/\/[^\s]+)/);
                if (match) {
                    clearTimeout(timeout);
                    resolve(match[1]);
                }
            });
            browserProcess.once('exit', (code) => {
                clearTimeout(timeout);
                reject(new Error(`Chromium endete vor dem CDP-Start mit ${code}: ${diagnostics}`));
            });
        });
        const browserEndpoint = new URL(browserSocketUrl);
        const targetResponse = await bounded(
            fetch(`http://${browserEndpoint.host}/json/new?${encodeURIComponent(pathToFileURL(htmlPath).href)}`, { method: 'PUT' }),
            'Das Anlegen des CDP-Ziels',
        );
        assert.ok(targetResponse.ok, `CDP-Ziel konnte nicht erstellt werden: ${targetResponse.status}`);
        const target = await targetResponse.json();

        socket = new WebSocket(target.webSocketDebuggerUrl);
        await bounded(new Promise((resolve, reject) => {
            socket.addEventListener('open', resolve, { once: true });
            socket.addEventListener('error', reject, { once: true });
        }), 'Die CDP-WebSocket-Verbindung');

        let commandId = 0;
        const pending = new Map();
        socket.addEventListener('message', (event) => {
            const message = JSON.parse(event.data);
            const waiter = pending.get(message.id);
            if (!waiter) {
                return;
            }

            pending.delete(message.id);
            if (message.error) {
                waiter.reject(new Error(message.error.message));
                return;
            }

            waiter.resolve(message.result);
        });
        const send = (method, params = {}) => bounded(new Promise((resolve, reject) => {
            const id = ++commandId;
            pending.set(id, { resolve, reject });
            socket.send(JSON.stringify({ id, method, params }));
        }), `Der CDP-Befehl ${method}`);

        await send('Runtime.enable');
        await send('Accessibility.enable');

        let encodedResult = null;
        for (let attempt = 0; attempt < 100 && encodedResult === null; attempt += 1) {
            const evaluation = await send('Runtime.evaluate', {
                expression: 'document.body?.dataset?.result ?? null',
                returnByValue: true,
            });
            encodedResult = evaluation.result.value;
            if (encodedResult === null) {
                await new Promise((resolve) => setTimeout(resolve, 50));
            }
        }
        assert.ok(encodedResult, 'Die lokale Testseite muss ihre Geometriedaten bereitstellen.');

        const accessibility = await send('Accessibility.getFullAXTree');

        return {
            geometry: JSON.parse(decodeURIComponent(encodedResult)),
            accessibilityNodes: accessibility.nodes,
        };
    } finally {
        socket?.close();
        if (browserProcess.exitCode === null) {
            browserProcess.kill();
            await Promise.race([
                once(browserProcess, 'exit'),
                new Promise((resolve) => setTimeout(resolve, 2_000)),
            ]);
        }
    }
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

/** Sammelt den zugänglichen Text eines AX-Knotens einschließlich seiner Kinder. */
function accessibilityText(node, nodesById, visited = new Set()) {
    if (!node || visited.has(node.nodeId)) {
        return '';
    }

    visited.add(node.nodeId);
    const ownText = node.name?.value ?? node.value?.value ?? '';
    const childText = (node.childIds ?? [])
        .map((childId) => accessibilityText(nodesById.get(childId), nodesById, visited))
        .join(' ');

    return `${ownText} ${childText}`.trim();
}

test('Badges bleiben in echten kleinen und großen Mediengeometrien sicher', async () => {
    const browser = await findBrowser();
    const temporaryDirectory = await mkdtemp(join(tmpdir(), 'mgd-ai-layout-'));
    const htmlPath = join(temporaryDirectory, 'layout.html');

    try {
        const css = browserCss(await readFile(componentPath, 'utf8'));
        await writeFile(htmlPath, testDocument(css), 'utf8');
        const inspection = await inspectWithCdp(browser, htmlPath, temporaryDirectory);
        const result = inspection.geometry;

        assertSize(result['small-fill'].wrapper, 40, 40, '40px fill wrapper');
        assertSize(result['small-fill'].medium, 40, 40, '40px fill medium');

        assertSize(result['small-intrinsic'].wrapper, 80, 80, '80px intrinsic wrapper');
        assertSize(result['small-intrinsic'].medium, 80, 80, '80px intrinsic medium');

        assertSize(result['flat-fill'].wrapper, 240, 40, '240×40px fill wrapper');
        assertSize(result['flat-fill'].medium, 240, 40, '240×40px fill medium');

        for (const id of ['small-fill', 'small-intrinsic', 'flat-fill']) {
            assert.notEqual(result[id].display, 'none', `${id}: Semantik darf nicht mit display:none verschwinden.`);
            assert.notEqual(result[id].visibility, 'hidden', `${id}: Semantik darf nicht unsichtbar geschaltet werden.`);
            assert.equal(result[id].ariaHidden, null, `${id}: aria-hidden ist unzulässig.`);
            assert.ok(result[id].badge.width <= 1.1 && result[id].badge.height <= 1.1, `${id}: visueller Fallback muss höchstens 1×1px sein.`);
        }

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

        assert.equal(result['overflow-intrinsic'].wrapperOverflow, 'visible');
        assert.equal(result['overflow-intrinsic'].overlayOverflow, 'clip');
        assert.ok(result['overflow-intrinsic'].medium.right > result['overflow-intrinsic'].wrapper.right + 20, 'Das transformierte Originalmedium darf über den Wrapper hinausragen.');

        const notes = inspection.accessibilityNodes.filter((node) => !node.ignored && node.role?.value === 'note');
        const nodesById = new Map(inspection.accessibilityNodes.map((node) => [node.nodeId, node]));
        const noteTexts = notes.map((node) => accessibilityText(node, nodesById));
        assert.equal(notes.length, 7, 'Jedes Label muss als eigener note-Knoten im AX-Baum erhalten bleiben.');
        assert.ok(noteTexts.some((name) => name.includes('DEEPFAKE') && name.includes('Notice: This image is labeled as a deepfake.')), `Der kleine Deepfake-Hinweis muss im AX-Baum vollständig erhalten bleiben: ${JSON.stringify(noteTexts)}`);
        assert.ok(noteTexts.some((name) => name.includes('AI-MODIFIED')), 'Das flache Label muss als note im AX-Baum erhalten bleiben.');

        for (const [id, pair] of Object.entries(result.presentationPairs)) {
            assertSize(pair.wrapped, pair.original.width, pair.original.height, `${id}: Medium`);
            assert.deepEqual(pair.wrappedPaint, pair.originalPaint, `${id}: sichtbestimmende CSS-Eigenschaften`);
        }

        const fillSlotSizes = {
            'cms-cover': [300, 180],
            'cms-stretch': [300, 169],
            listing: [200, 200],
            'gallery-cover': [300, 180],
            cart: [70, 70],
            configurator: [52, 52],
            'image-slider': [240, 135],
            video: [320, 180],
            background: [320, 180],
        };
        for (const [id, [width, height]] of Object.entries(fillSlotSizes)) {
            assertSize(result.presentationPairs[id].wrapper, width, height, `${id}: Fill-Rahmen`);
        }

        for (const id of ['cms-standard', 'gallery-standard', 'logo', 'search', 'navigation']) {
            const pair = result.presentationPairs[id];
            assertSize(pair.wrapper, pair.original.width, pair.original.height, `${id}: Intrinsic-Rahmen`);
        }

        const navigation = result.presentationPairs.navigation;
        assertSize(navigation.original, 160, 90, 'Navigation: natürliche Originalgröße');
        assert.ok(Math.abs((navigation.original.left - navigation.originalSlot.left) - (navigation.wrapped.left - navigation.wrappedSlot.left)) < 0.1, 'Navigation: horizontale Position bleibt erhalten.');
        assert.ok(Math.abs((navigation.original.top - navigation.originalSlot.top) - (navigation.wrapped.top - navigation.wrappedSlot.top)) < 0.1, 'Navigation: vertikale Position bleibt erhalten.');

        for (const [id, pair] of Object.entries(result.methodPairs)) {
            assertSize(pair.wrapped, pair.original.width, pair.original.height, `${id}: Logo`);
            assertSize(pair.wrapper, pair.wrapped.width + 8, pair.wrapped.height, `${id}: Float-Rahmen einschließlich beider 4px-Bildränder`);
            assert.ok(Math.abs((pair.original.left - pair.originalRow.left) - (pair.wrapped.left - pair.wrappedRow.left)) < 0.1, `${id}: Logo-x bleibt erhalten.`);
            assert.ok(Math.abs((pair.original.top - pair.originalRow.top) - (pair.wrapped.top - pair.wrappedRow.top)) < 0.1, `${id}: Logo-y bleibt erhalten.`);
            assert.ok(Math.abs((pair.originalSibling.left - pair.originalRow.left) - (pair.wrappedSibling.left - pair.wrappedRow.left)) < 0.1, `${id}: Geschwister-x bleibt erhalten.`);
            assert.ok(Math.abs((pair.originalSibling.top - pair.originalRow.top) - (pair.wrappedSibling.top - pair.wrappedRow.top)) < 0.1, `${id}: Geschwister-y bleibt erhalten.`);
            assertSize(pair.wrappedRow, pair.originalRow.width, pair.originalRow.height, `${id}: Elternzeile`);
        }
    } finally {
        await rm(temporaryDirectory, { recursive: true, force: true });
    }
});
