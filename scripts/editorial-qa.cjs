/* Browser acceptance for the public editorial site. Requires Playwright and Chrome.
 * NODE_PATH can point to an existing Playwright runtime. XUVERSE_URL selects local or hosted.
 */
const assert = require('node:assert/strict');
const crypto = require('node:crypto');
const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require('playwright');

const root = path.resolve(__dirname, '..');
const base = new URL((process.env.XUVERSE_URL || 'http://localhost/xuverse/').replace(/\/?$/, '/'));
const browserPath = process.env.XUVERSE_BROWSER || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const output = path.join(root, 'tmp', 'editorial-qa', base.hostname);
const namedWidths = [320, 360, 375, 390, 430, 768, 820, 1024, 1280, 1366, 1440, 1600, 1920, 3440];
const widths = process.env.XUVERSE_QA_WIDTHS?.split(',').map(Number) || namedWidths;
const missingRoute = 'xuverse-qa-missing-page';
const primaryRoutes = ['', 'projects', 'articles', 'media', 'about', 'resume', 'contact', 'now', 'login', missingRoute];
const mediaModes = ['photographs', 'videos', 'published'];
const routes = [...primaryRoutes, ...mediaModes.map(id => 'media#' + id)];
const redirectTargets = new Map([['now', 'about']]);
const featuredRoutes = new Set(['', 'projects', 'articles', 'media', 'media#published', 'about', 'resume', 'contact', missingRoute]);
const details = [];
const report = { base: base.href, widths, layouts: [], resizeSweeps: [], details: [], interactions: [], links: [], pdfs: [], screenshots: [], failures: [], externalFailures: [] };
const checkedLinks = new Set();
const expectedFailures = new Set([
    missingRoute, 'projects/' + missingRoute, 'articles/' + missingRoute,
    'photos/' + missingRoute, 'videos/' + missingRoute, 'admin/music/index.php'
]);
const sha256 = bytes => crypto.createHash('sha256').update(bytes).digest('hex');

function canonical(name) {
    return JSON.parse(fs.readFileSync(path.join(root, 'content', name + '.json'), 'utf8'));
}

function published(name) {
    return canonical(name).filter(item => (item.publication_status || 'published') === 'published');
}

function isLocal(url) {
    return url.origin === base.origin && url.pathname.startsWith(base.pathname);
}

function expectedStatus(route) {
    return expectedFailures.has(route) ? 404 : 200;
}

async function check(label, run) {
    try {
        await run();
        console.log('PASS ' + label);
    } catch (error) {
        report.failures.push({ label, error: error.message });
        console.error('FAIL ' + label + '\n' + error.message);
    }
}

function watch(page) {
    page.on('request', request => {
        const url = new URL(request.url());
        if (isLocal(url) && /\/uploads\/music\/|\.(?:mp3|wav|m4a|ogg)$/i.test(url.pathname)) {
            report.failures.push({ label: page.url(), error: 'Public page requested music: ' + url.href });
        }
    });
    page.on('pageerror', error => report.failures.push({ label: page.url(), error: error.message }));
    page.on('console', message => {
        const url = message.location().url;
        if (message.type() === 'error' && url.startsWith(base.href) && !message.text().includes('404')) {
            report.failures.push({ label: page.url(), error: message.text() });
        }
    });
    page.on('response', response => {
        if (response.status() < 400 || response.request().resourceType() === 'document') return;
        const url = new URL(response.url());
        if (isLocal(url)) {
            report.failures.push({ label: page.url(), error: 'HTTP ' + response.status() + ' ' + url.href });
        }
    });
    page.on('requestfailed', request => {
        const url = new URL(request.url());
        if (!['https:', 'http:'].includes(url.protocol)) return;
        const failure = request.failure()?.errorText || 'Unknown error';
        if (failure === 'net::ERR_ABORTED') return;
        const item = { label: page.url(), error: failure + ' ' + url.href };
        (isLocal(url) ? report.failures : report.externalFailures).push(item);
    });
}

async function openPage(page, route) {
    const url = new URL(route, base);
    const response = await page.goto(url.href, { waitUntil: 'domcontentloaded', timeout: 40_000 });
    if (!response && page.url() === url.href) {
        // A same-document hash change has no navigation response.
        return;
    }
    assert.ok(response, 'Missing HTTP response for ' + route);
    assert.equal(response.status(), expectedStatus(route), 'HTTP status for ' + route);
    const expectedUrl = redirectTargets.has(route) ? new URL(redirectTargets.get(route), base) : url;
    assert.equal(new URL(page.url()).pathname, expectedUrl.pathname, 'Unexpected redirect for ' + route);
    const main = page.getByRole('main');
    await main.waitFor({ state: 'visible' });
    const headings = main.getByRole('heading', { level: 1 });
    assert.equal(await headings.count(), 1, 'Visible H1 count for ' + route);
    assert.ok((await headings.first().innerText()).trim(), 'Nonempty H1 for ' + route);
}

async function collectLinks(page) {
    const links = await page.locator('a[href]').evaluateAll(anchors => anchors.map(anchor => anchor.href));
    for (const link of links) {
        const url = new URL(link);
        if (!isLocal(url)) continue;
        url.hash = '';
        if (url.pathname.slice(base.pathname.length) === missingRoute) continue;
        checkedLinks.add(url.href);
    }
}

async function settleImages(page) {
    const images = page.locator('img:visible');
    await images.evaluateAll(async elements => {
        elements.forEach(image => { image.loading = 'eager'; });
        // After a viewport resize, Chromium can still be selecting a new srcset candidate.
        await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
    });
    await page.waitForFunction(() => [...document.images].every(image =>
        !image.getClientRects().length || getComputedStyle(image).visibility !== 'visible' || image.complete
    ), null, { timeout: 6000 }).catch(() => {});
    const imageProblems = await images.evaluateAll(images => {
        return images.filter(image => !image.complete || !image.naturalWidth || !image.hasAttribute('alt'))
            .map(image => image.currentSrc || image.getAttribute('src') || '(missing source)');
    });
    assert.deepEqual(imageProblems, [], 'Unloaded image or missing alt attribute');
}

async function layout(page, route, width) {
    const result = await page.evaluate(() => {
        const limit = document.documentElement.clientWidth;
        const outside = [];
        const nestedScroll = new Set();
        for (const element of document.querySelectorAll('main *, nav *, footer *')) {
            if (element.closest('svg, [hidden], dialog:not([open]), [aria-hidden="true"]')) continue;
            if (!element.getClientRects().length) continue;
            const style = getComputedStyle(element);
            if (style.visibility !== 'visible') continue;
            const box = element.getBoundingClientRect();
            if (box.width === 0 || (box.left >= -1 && box.right <= limit + 1)) continue;
            // Clipping masks and nested carousels can intentionally extend child bounds.
            let clipped = false;
            for (let parent = element.parentElement; parent && parent !== document.body; parent = parent.parentElement) {
                if (parent.matches('main, #page-shell')) continue;
                const overflowX = getComputedStyle(parent).overflowX;
                if (!['auto', 'scroll', 'hidden', 'clip'].includes(overflowX)) continue;
                const parentBox = parent.getBoundingClientRect();
                if (parentBox.left < -1 || parentBox.right > limit + 1) continue;
                if (overflowX === 'auto' || overflowX === 'scroll') {
                    nestedScroll.add(parent.id || parent.getAttribute('aria-label') || parent.tagName);
                }
                clipped = true;
                break;
            }
            if (clipped) continue;
            outside.push({ element: element.tagName + (element.id ? '#' + element.id : ''), left: Math.round(box.left), right: Math.round(box.right) });
        }
        return {
            documentWidth: document.documentElement.scrollWidth,
            viewportWidth: limit,
            outside: outside.slice(0, 12),
            nestedScroll: [...nestedScroll]
        };
    });
    assert.ok(result.documentWidth <= result.viewportWidth + 1, 'Horizontal document overflow at ' + route + ' ' + width);
    assert.deepEqual(result.outside, [], 'Elements outside viewport at ' + route + ' ' + width);
    return result;
}

async function recordScreenshot(page, route, width) {
    const name = (route || 'home').replace(/[^a-z0-9-]/gi, '-') + '-' + width + '.png';
    // Scroll once so observed/reveal content is visible in full-page evidence.
    await page.evaluate(async () => {
        const step = Math.max(300, innerHeight - 100);
        for (let top = 0; top < document.body.scrollHeight; top += step) {
            scrollTo({ top, behavior: 'instant' });
            await new Promise(resolve => requestAnimationFrame(resolve));
        }
        scrollTo({ top: 0, behavior: 'instant' });
    });
    await page.screenshot({ path: path.join(output, name), fullPage: true, animations: 'disabled' });
    report.screenshots.push(name);
}

async function responsive(page, route) {
    await page.setViewportSize({ width: widths[0], height: 900 });
    await openPage(page, route);
    for (const width of widths) {
        await check((route || 'home') + ' at ' + width + 'px', async () => {
            await page.setViewportSize({ width, height: 900 });

            if (route.startsWith('media#')) {
                const id = route.split('#')[1];
                await page.locator('#' + id).waitFor({ state: 'visible' });
            }
            await settleImages(page);
            report.layouts.push({ route, width, ...await layout(page, route, width) });
            if (featuredRoutes.has(route) && [390, 1440].includes(width)) {
                await recordScreenshot(page, route, width);
            }
        });
    }
    await collectLinks(page);
}

async function resizeSweep(page, route) {
    await openPage(page, route);
    const tested = [];
    for (let width = 320; width <= 1920; width += 40) {
        await page.setViewportSize({ width, height: 900 });
        await layout(page, route, width);
        tested.push(width);
    }
    report.resizeSweeps.push({ route, widths: tested });
}

async function keyboardNavigation(page) {
    await page.setViewportSize({ width: 390, height: 844 });
    await openPage(page, '');
    const skip = page.getByRole('link', { name: /skip to main/i });
    await page.keyboard.press('Tab');
    assert.ok(await skip.evaluate(link => document.activeElement === link), 'Skip link is first in the Tab order');
    await page.keyboard.press('Enter');
    assert.equal(new URL(page.url()).hash, '#main-content', 'Skip link reaches main content');
    const nav = page.getByRole('navigation', { name: /primary navigation/i });
    const toggle = nav.locator('.nav-toggle');
    await toggle.focus();
    await page.keyboard.press('Enter');
    assert.equal(await toggle.getAttribute('aria-expanded'), 'true');
    const menu = page.locator('#' + await toggle.getAttribute('aria-controls'));
    await menu.waitFor({ state: 'visible' });
    await page.keyboard.press('Escape');
    assert.equal(await toggle.getAttribute('aria-expanded'), 'false');
    assert.ok(await toggle.evaluate(button => document.activeElement === button), 'Escape restores menu focus');
    await page.keyboard.press('Space');
    const mediaLink = menu.getByRole('link', { name: /^Media$/i });
    await mediaLink.focus();
    await page.keyboard.press('Enter');
    await page.waitForURL(new URL('media', base).href);

    const tabs = page.getByRole('tab');
    const photos = tabs.filter({ hasText: /^Photographs$/ });
    const videos = tabs.filter({ hasText: /^Videos$/ });
    const work = tabs.filter({ hasText: /^Published Work$/ });
    await photos.focus();
    await page.keyboard.press('ArrowRight');
    assert.equal(await videos.getAttribute('aria-selected'), 'true');
    const videoPanel = page.locator('#videos');
    const players = videoPanel.locator('iframe[src]');
    assert.equal(await players.count(), 0, 'Video mode waits for playback intent');
    await videoPanel.getByRole('button', { name: /^play\b/i }).first().press('Enter');
    await players.first().waitFor({ state: 'attached' });
    assert.equal(await players.count(), 1, 'Only the selected player loads');
    await videos.focus();
    await page.keyboard.press('End');
    assert.equal(await work.getAttribute('aria-selected'), 'true');
    assert.equal(await players.count(), 0, 'Leaving Videos clears its players');
    await page.goBack();
    assert.equal(await videos.getAttribute('aria-selected'), 'true');
    await page.goForward();
    assert.equal(await work.getAttribute('aria-selected'), 'true');

    const cover = page.locator('#published [data-open-gallery]').first();
    await cover.focus();
    await page.keyboard.press('Enter');
    await page.getByRole('dialog').waitFor({ state: 'visible' });
    await page.keyboard.press('Escape');
    assert.equal(await page.locator('dialog[open]').count(), 0);
    assert.ok(await cover.evaluate(link => document.activeElement === link), 'Closing a gallery restores focus');
    report.interactions.push('Skip link, mobile menu activation/Escape and focus return, media tabs/history, video loading on intent and player unloading, and gallery focus return');
}

async function collectionSearch(page) {
    for (const collection of ['projects', 'articles']) {
        await openPage(page, collection);
        const search = page.getByRole('searchbox');
        await search.fill('xuverse-qa-no-matching-record-49a');
        await page.locator('[data-collection-empty]').waitFor({ state: 'visible' });
        assert.equal(await page.locator('[data-collection] article:visible').count(), 0);
        await page.getByRole('button', { name: /^clear$/i }).click();
        assert.ok(await page.locator('[data-collection] article:visible').count());
    }
    report.interactions.push('Project and article search empty states and recovery');
}

async function contactAndMotion(page) {
    await openPage(page, 'contact');
    const email = canonical('links').email;
    const emailLink = page.getByRole('main').locator('a[href^="mailto:"]').first();
    await emailLink.waitFor({ state: 'visible' });
    assert.equal((await emailLink.getAttribute('href')).split('?')[0], email, 'Canonical email link');
    const inquiry = page.locator('form.inquiry-form');
    if (await inquiry.count()) {
        for (const field of [/^name$/i, /^email$/i, /what is this about/i, /^message$/i]) {
            await inquiry.getByLabel(field).waitFor({ state: 'visible' });
        }
        assert.ok(await inquiry.locator('input[name="csrf_token"]').getAttribute('value'), 'Contact form has a CSRF token');
        await inquiry.getByRole('button', { name: /send message/i }).waitFor({ state: 'visible' });
    }
    report.interactions.push('Contact email and conditional inquiry controls');

    await openPage(page, '');
    const continuouslyRunning = await page.evaluate(() => document.getAnimations()
        .filter(animation => animation.playState === 'running' && animation.effect.getTiming().iterations === Infinity)
        .map(animation => animation.animationName || 'unnamed animation'));
    assert.deepEqual(continuouslyRunning, [], 'Reduced motion stops continuous animations');
    report.interactions.push('Reduced motion stops continuous animations');
}

async function designChecks(page) {
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.emulateMedia({ reducedMotion: 'no-preference' });
    try {
        for (const route of ['', 'projects', 'articles', 'media', 'about', 'resume', 'contact']) {
            await openPage(page, route);
            await page.locator('body.motion-ready').waitFor();
            const design = await page.evaluate(() => {
                const body = getComputedStyle(document.body);
                const grain = getComputedStyle(document.body, '::before');
                const footer = document.querySelector('.site-footer');
                const nav = document.querySelector('.navbar');
                const publicText = document.querySelector('main').innerText;
                return {
                    grain: [grain.animationName, grain.animationPlayState, Number(grain.opacity), grain.backgroundImage],
                    size: parseFloat(getComputedStyle(document.querySelector('main h1')).fontSize),
                    oldArt: !!document.querySelector('.xv-ambient,.xv-signature'),
                    arrows: [...document.querySelectorAll('main a, .site-footer a')].some(anchor => /[→↗↓]/u.test(anchor.textContent)),
                    award: /selected recognition|award reported by|user-reported award/i.test(publicText),
                    base: body.backgroundColor,
                    sans: body.fontFamily,
                    navbar: getComputedStyle(nav).backgroundColor,
                    currentNav: getComputedStyle(nav.querySelector('[aria-current="page"]')).color,
                    footer: getComputedStyle(footer).backgroundColor,
                    edgeDelta: Math.abs(footer.getBoundingClientRect().left - nav.getBoundingClientRect().left)
                };
            });
            assert.equal(design.grain[0], 'xu-grain-drift');
            assert.equal(design.grain[1], 'running');
            assert.ok(design.grain[2] > 0 && design.grain[2] < .1 && design.grain[3].includes('grain.png'));
            assert.ok(design.size <= 64, 'Heading scale on ' + route);
            assert.equal(design.oldArt || design.arrows || design.award, false, 'Old public decoration or wording on ' + route);
            assert.equal(design.base, 'rgb(8, 9, 13)');
            assert.match(design.sans, /Manrope/);
            assert.equal(design.navbar, 'rgb(16, 24, 39)');
            assert.equal(design.currentNav, 'rgb(255, 84, 156)');
            assert.equal(design.footer, 'rgb(16, 24, 39)');
            assert.ok(design.edgeDelta < 1, 'Footer and navbar align');
        }
        const grainTransform = () => page.evaluate(() => getComputedStyle(document.body, '::before').transform);
        const previous = await grainTransform();
        await page.waitForFunction(initial => getComputedStyle(document.body, '::before').transform !== initial, previous);
        await page.emulateMedia({ reducedMotion: 'reduce' });
        assert.equal(await page.evaluate(() => getComputedStyle(document.body, '::before').animationName), 'none');
    } finally {
        await page.emulateMedia({ reducedMotion: 'reduce' });
    }
    report.interactions.push('Editorial palette and type scale, no legacy XS, no decorative arrows or award meta, matched frames, moving grain and live reduced-motion change');
}

async function alignAndFit(page) {
    const viewports = [[1366,768],[1440,900],[1920,1080],[390,844],[430,932],[320,568]];
    report.firstViewports = [];
    for (const [width,height] of viewports) {
        await page.setViewportSize({ width, height });
        await openPage(page, '');
        await page.evaluate(() => document.fonts.ready);
        await settleImages(page);
        const result = await page.evaluate(() => {
            const box = selector => document.querySelector(selector).getBoundingClientRect();
            const grid = box('.xu-hero .xu-container');
            const nav = box('.navbar>.container'), footer = box('.xu-footer-inner');
            const portrait = box('.xu-hero-portrait img'), actions = box('.xu-hero-actions');
            const hero = box('.xu-hero');
            return {
                viewport: innerHeight, hero: hero.bottom,
                next: box('#selected-work').top,
                portraitBottom: portrait.bottom, actionsBottom: actions.bottom,
                portraitCenterError: Math.abs((portrait.left + portrait.right) / 2 - (grid.left + grid.right) / 2),
                frameAlignError: Math.max(Math.abs(grid.left - nav.left), Math.abs(grid.left - footer.left),
                    Math.abs(grid.right - nav.right), Math.abs(grid.right - footer.right)),
                loadedFonts: ['Manrope','Spectral'].map(name => [...document.fonts].some(font => font.family.replaceAll('"','') === name && font.status === 'loaded')),
                prose: getComputedStyle(document.querySelector('.xu-hero-line')).fontFamily,
                primary: ['backgroundColor','color'].map(prop => getComputedStyle(document.querySelector('.xu-hero-actions .xu-primary'))[prop]),
                surfaces: ['.xu-hero','#selected-work','.xu-home-writing','.xu-home-media','.xu-home-about'].map(selector => getComputedStyle(document.querySelector(selector)).backgroundColor),
                firstPartyFonts: performance.getEntriesByType('resource').filter(resource => /\.woff2(?:\?|$)/.test(resource.name))
                    .every(resource => new URL(resource.name).origin === location.origin)
            };
        });
        assert.ok(Math.abs(result.next - height) <= 2, 'Selected Works begins below the first view: ' + width + '×' + height);
        assert.ok(Math.abs(result.hero - result.next) < 1 && Math.max(result.portraitBottom,result.actionsBottom) <= height,
            'The entire hero fits the first view');
        if (width <= 720) assert.ok(result.portraitCenterError < 1, 'Mobile portrait stays centered');
        assert.ok(result.frameAlignError < 1, 'Hero, navigation, and footer share both grid edges');
        assert.deepEqual(result.loadedFonts, [true,true]);
        assert.match(result.prose, /Spectral/);
        assert.deepEqual(result.primary, ['rgb(255, 84, 156)', 'rgb(21, 11, 19)']);
        assert.equal(result.firstPartyFonts,true);
        assert.deepEqual(result.surfaces, ['rgb(8, 9, 13)','rgb(16, 24, 39)','rgb(8, 9, 13)','rgb(16, 24, 39)','rgb(8, 9, 13)']);
        const name = `first-viewport-${width}x${height}.png`;
        await page.screenshot({path:path.join(output,name),animations:'disabled'});
        report.screenshots.push(name);
        report.firstViewports.push({width,height,...result});
    }
    await page.setViewportSize({width:1440,height:900});
    await openPage(page,'projects');
    const projectRhythm = () => page.locator('#content-results > article:visible').evaluateAll(rows => rows.map(row => getComputedStyle(row).backgroundColor));
    assert.deepEqual(await projectRhythm(), ['rgb(8, 9, 13)','rgb(16, 24, 39)','rgb(8, 9, 13)']);
    await page.getByRole('searchbox').fill('Tailwind');
    assert.deepEqual(await projectRhythm(), ['rgb(8, 9, 13)'], 'Search results preserve visible rhythm');
    report.interactions.push('Six exact first viewports; hero fit, Home rhythm, centered phones, matched grid, first-party fonts, and filtered project rhythm');
}

async function verifyPdf(context, route, expectedHash) {
    const response = await context.request.get(new URL(route, base).href);
    try {
        assert.equal(response.status(), 200);
        assert.match(response.headers()['content-type'] || '', /^application\/pdf\b/i);
        const bytes = await response.body();
        assert.equal(bytes.subarray(0, 5).toString(), '%PDF-');
        const digest = sha256(bytes);
        if (expectedHash) assert.equal(digest, expectedHash, 'PDF matches canonical manifest');
        report.pdfs.push({ route, bytes: bytes.length, sha256: digest });
    } finally {
        await response.dispose();
    }
}

async function largerText(page) {
    const targets = new Map([
        ['', '.xu-hero-copy h1, .xu-feature-head h2, .xu-feature-head a'],
        ['media', '.media-categories a']
    ]);
    for (const [route, selector] of targets) {
        await openPage(page, route);
        await page.evaluate(async () => {
            document.documentElement.style.fontSize = '200%';
            await document.fonts.ready;
        });
        try {
            for (const width of [320, 390, 768]) {
                await page.setViewportSize({ width, height: 900 });
                const clipped = await page.locator(selector).evaluateAll(elements => elements.filter(element => {
                    const bounds = element.getBoundingClientRect();
                    const range = document.createRange();
                    range.selectNodeContents(element);
                    return [...range.getClientRects()].some(rect => rect.left < bounds.left - 2
                        || rect.right > bounds.right + 2 || rect.right > document.documentElement.clientWidth + 1);
                }).map(element => element.textContent.trim()));
                assert.deepEqual(clipped, [], 'Text clipping at 200% size: ' + (route || 'home') + ' ' + width);
                await layout(page, route, width);
            }
        } finally {
            await page.evaluate(() => { document.documentElement.style.removeProperty('font-size'); });
        }
    }
    report.interactions.push('Home and Media text resizing at 200%');
}

async function crawlLinks(context) {
    const links = [...checkedLinks].sort();
    for (const url of links) {
        await check('internal link ' + url, async () => {
            const response = await context.request.get(url);
            try {
                assert.ok(response.ok(), 'HTTP ' + response.status() + ' for ' + url);
                assert.ok(isLocal(new URL(response.url())), 'Unexpected external redirect from ' + url);
                report.links.push({ url, status: response.status() });
            } finally {
                await response.dispose();
            }
        });
    }
}

async function verifyRevision(page) {
    const revision = process.env.XUVERSE_EXPECTED_REVISION;
    if (!revision) return;
    const response = await page.goto(new URL('release.json', base).href, { waitUntil: 'domcontentloaded' });
    assert.equal(response.status(), 200);
    const release = JSON.parse(await page.locator('body').innerText());
    assert.equal(release.revision, revision);
    assert.equal(release.content_sha256, canonical('manifest').content_sha256);
    report.revision = release;
}

async function main() {
    fs.mkdirSync(output, { recursive: true });
    const articles = published('articles');
    details.push(...published('projects').map(item => 'projects/' + item.slug));
    details.push(...articles.map(item => 'articles/' + item.slug));
    details.push(...published('media').map(item => (item.type === 'video' ? 'videos/' : 'photos/') + item.slug));
    details.push(...published('published').map(item => 'photos/' + item.slug));
    const browser = await chromium.launch({ headless: true, executablePath: browserPath });
    try {
        const context = await browser.newContext({ reducedMotion: 'reduce' });
        const page = await context.newPage();
        watch(page);
        if (process.env.XUVERSE_EXPECTED_REVISION) {
            await check('requested release and canonical content hash', () => verifyRevision(page));
        }
        for (const route of routes) {
            await check('responsive route ' + (route || 'home'), () => responsive(page, route));
        }
        for (const route of ['', 'projects', 'articles', 'media', 'about', 'resume', 'contact']) {
            if (process.env.XUVERSE_QA_SKIP_SWEEP === '1') break;
            await check('continuous resize ' + (route || 'home'), () => resizeSweep(page, route));
        }
        for (const route of new Set([...details, ...expectedFailures])) {
            await check('direct route ' + route, async () => {
                await openPage(page, route);
                for (const width of [390, 1440]) {
                    await page.setViewportSize({ width, height: 900 });
                    await settleImages(page);
                    await layout(page, route, width);
                    if (route === details[0] || route === 'articles/' + articles[0]?.slug) {
                        await recordScreenshot(page, route, width);
                    }
                }
                report.details.push({ route, status: expectedStatus(route), widths: [390, 1440] });
                if (expectedStatus(route) === 200) await collectLinks(page);
            });
        }
        await check('keyboard navigation and Media interactions', () => keyboardNavigation(page));
        await check('project and article search', () => collectionSearch(page));
        await check('contact access and reduced motion', () => contactAndMotion(page));
        await check('editorial design and moving grain', () => designChecks(page));
        await check('alignment, section rhythm and exact first viewports', () => alignAndFit(page));
        await check('home and Media at 200% text size', () => largerText(page));
        await check('resume PDF', () => verifyPdf(context, 'resume/download'));
        const manifest = canonical('manifest');
        for (const article of articles) {
            await check('article PDF ' + article.slug, () => {
                const expectedHash = manifest.writing[article.slug]?.pdf_sha256;
                assert.ok(expectedHash, 'Canonical hash missing for ' + article.pdf_path);
                return verifyPdf(context, article.pdf_path, expectedHash);
            });
        }
        await crawlLinks(context);
    } finally {
        await browser.close();
    }
}

main().catch(error => {
    report.failures.push({ label: 'Playwright', error: error.message });
}).finally(() => {
    report.status = report.failures.length ? 'failed' : 'passed';
    const reportPath = path.join(output, 'results.json');
    fs.writeFileSync(reportPath, JSON.stringify(report, null, 2) + '\n');
    console.log(report.status + ' — ' + reportPath);
    if (report.failures.length) process.exitCode = 1;
});
