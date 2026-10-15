/* Browser regression checks. Uses an existing Playwright installation; no app dependency.
   NODE_PATH=<runtime node_modules> node scripts/refinement-qa.cjs
   XUVERSE_URL and XUVERSE_BROWSER optionally select the server and browser. */
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const { chromium } = require('playwright');

const root = path.resolve(__dirname, '..');
const output = path.join(root, 'tmp', 'refinement-qa');
fs.mkdirSync(output, { recursive:true });
const base = (process.env.XUVERSE_URL || 'http://localhost/xuverse/').replace(/\/?$/, '/');
const browserPath = process.env.XUVERSE_BROWSER || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const read = name => JSON.parse(fs.readFileSync(path.join(root, 'content', name + '.json'), 'utf8'));
const published = name => read(name).filter(item => (item.publication_status || 'published') === 'published');
const major = ['', 'projects', 'articles', 'media', 'about', 'resume', 'contact'];
const details = [
    ...published('projects').map(item => 'projects/' + item.slug),
    ...published('articles').map(item => 'articles/' + item.slug),
    ...published('media').map(item => (item.type === 'video' ? 'videos/' : 'photos/') + item.slug),
    ...published('published').map(item => 'photos/' + item.slug)
];
const widths = process.env.XUVERSE_QA_WIDTHS ? process.env.XUVERSE_QA_WIDTHS.split(',').map(Number) : [320,360,390,430,768,820,1024,1280,1366,1440,1920];
const routes = process.env.XUVERSE_QA_ROUTES ? process.env.XUVERSE_QA_ROUTES.split(',').map(route=>route==='home'?'':route) : [...major,...details,'login','this-route-does-not-exist'];
const reportName = path.basename(process.env.XUVERSE_QA_REPORT || 'results.json');
const interactionsOnly = process.env.XUVERSE_QA_INTERACTIONS_ONLY === '1';
const results = { base, widths, routes:[], interactions:[], screenshots:[], errors:[], consoleErrors:[], externalFailures:[], measurements:[] };
const record = label => { results.interactions.push(label); console.log('PASS:', label); };
let browser;

async function settleImages(page) {
    // Instant scrolling really exposes lazy images and reveals; smooth scrolling
    // inside a fast loop can skip most of the page and give a false QA result.
    await page.evaluate(async () => {
        for (let y = 0; y < document.body.scrollHeight; y += Math.max(300, innerHeight - 100)) {
            scrollTo({ top:y, behavior:'instant' });
            await new Promise(resolve => setTimeout(resolve, 35));
        }
        scrollTo({ top:0, behavior:'instant' });
        for (const track of document.querySelectorAll('.post-gallery-track')) {
            if (!track.getClientRects().length) continue;
            for (let x = 0; x < track.scrollWidth; x += Math.max(1,track.clientWidth)) {
                track.scrollTo({left:x,behavior:'instant'});
                await new Promise(resolve => setTimeout(resolve,35));
            }
            track.scrollTo({left:0,behavior:'instant'});
        }
        await Promise.all([...document.images].filter(image => image.getAttribute('src') && image.getClientRects().length).map(image =>
            image.complete ? Promise.resolve() : Promise.race([
                new Promise(resolve => { image.addEventListener('load', resolve, {once:true}); image.addEventListener('error', resolve, {once:true}); }),
                new Promise(resolve => setTimeout(resolve, 4000))
            ])
        ));
    });
}

async function measure(page, route, width) {
    const data = await page.evaluate(() => {
        const rect = element => element.getBoundingClientRect();
        const visible = element => element.getClientRects().length && getComputedStyle(element).visibility !== 'hidden';
        const limit = document.documentElement.clientWidth;
        // Check actual element boundaries too: overflow-x:hidden can hide a defect.
        const outside = [...document.querySelectorAll('main *,nav a,footer *')].filter(element => {
            if (!visible(element) || element.closest('dialog:not([open]),[hidden],.post-gallery-track')) return false;
            if (getComputedStyle(element).position === 'absolute') return false;
            const box = rect(element);
            return box.width > 0 && (box.left < -1 || box.right > limit + 1);
        }).map(element => element.tagName + '.' + element.className).slice(0,8);
        const images = [...document.images].filter(image => visible(image) && image.getAttribute('src') && (!image.complete || !image.naturalWidth)).map(image => image.src);
        const nav = rect(document.querySelector('.navbar'));
        const logo = rect(document.querySelector('.logo'));
        const menu = document.querySelector('.nav-toggle');
        const links = document.querySelector('.nav-links');
        const neighbor = visible(menu) ? rect(menu) : rect(links);
        const search = document.querySelector('.collection-tools');
        const input = document.querySelector('#content-search');
        const portrait = document.querySelector('.identity-panel,.resume-top>.responsive-picture');
        const container = portrait?.parentElement;
        const centerDelta = portrait ? Math.abs(rect(portrait).left + rect(portrait).width / 2 - rect(container).left - rect(container).width / 2) : null;
        const glyphBounds = selector => {
            const element = document.querySelector(selector), box = element.getBBox(), matrix = element.getScreenCTM();
            const points = [[box.x,box.y],[box.x+box.width,box.y+box.height]].map(([x,y]) => new DOMPoint(x,y).matrixTransform(matrix));
            return {cx:(points[0].x+points[1].x)/2, cy:(points[0].y+points[1].y)/2, height:points[1].y-points[0].y};
        };
        const x = glyphBounds('.xv-signature-x'), s = glyphBounds('.xv-signature-s');
        const signature = rect(document.querySelector('.xv-signature'));
        const shortHeadings = [...document.querySelectorAll('.home-featured .section-head-compact h2,#published-title,.home-connect h2')].filter(visible).map(element => ({text:element.textContent.trim(),height:rect(element).height,lineHeight:parseFloat(getComputedStyle(element).lineHeight)}));
        return {outside,images,scrollOverflow:document.documentElement.scrollWidth>innerWidth,
            nav:{left:nav.left,top:nav.top,right:nav.right},navCollision:logo.right>neighbor.left+1,
            searchWidth:search?rect(search).width:null,inputHeight:input?rect(input).height:null,portraitDelta:centerDelta,
            signature:{centerDelta:Math.hypot(x.cx-s.cx,x.cy-s.cy),ratio:s.height/x.height,clipped:signature.left<0||signature.top<0||signature.right>limit||signature.bottom>innerHeight},
            shortHeadings,footerHeight:rect(document.querySelector('footer')).height};
    });
    const context = route + ' at ' + width;
    assert.deepEqual(data.outside, [], 'Elements outside viewport: ' + context + ' ' + JSON.stringify(data.outside));
    assert.deepEqual(data.images, [], 'Broken or pending images: ' + context);
    assert.equal(data.scrollOverflow, false, 'Horizontal overflow: ' + context);
    assert.equal(data.navCollision, false, 'Navbar collision: ' + context);
    assert.ok(data.nav.left >= 8 && data.nav.top >= 7, 'Navbar edge inset: ' + context);
    assert.ok(data.searchWidth === null || data.searchWidth <= 520.5, 'Search width: ' + context);
    assert.ok(data.inputHeight === null || data.inputHeight >= 44 && data.inputHeight <= 48, 'Search height: ' + context);
    if (width <= 720 && data.portraitDelta !== null) assert.ok(data.portraitDelta < 2, 'Portrait center: ' + context);
    assert.ok(data.signature.centerDelta < .1 && Math.abs(data.signature.ratio - .69) < .001 && !data.signature.clipped, 'XS geometry: ' + context);
    for (const heading of data.shortHeadings) assert.ok(heading.height <= heading.lineHeight + 1, 'Short heading wrap: ' + context + ' ' + heading.text);
    assert.ok(data.footerHeight < (width <= 720 ? 260 : 200), 'Footer footprint: ' + context);
    results.measurements.push({route,width,...data});
}

async function capture(page, route, width) {
    const name = (route || 'home').replaceAll('/', '-') + '-' + width + '.png';
    await page.screenshot({path:path.join(output,name),fullPage:true});
    results.screenshots.push(name);
}

(async () => {
    browser = await chromium.launch({headless:true,executablePath:browserPath});
    const context = await browser.newContext({reducedMotion:'reduce'});
    const page = await context.newPage();
    page.on('pageerror', error => results.errors.push(error.message));
    page.on('console', message => {
        if (page.url().includes('this-route-does-not-exist') && message.text().includes('404')) return;
        if (message.type() === 'error' && message.location().url.startsWith(base)) results.consoleErrors.push(message.text());
    });
    page.on('requestfailed', request => {
        if (!request.url().startsWith(base)) results.externalFailures.push({url:request.url(),error:request.failure()?.errorText});
    });
    for (const route of interactionsOnly ? [] : routes) {
        for (const width of widths) {
            await page.setViewportSize({width,height:900});
            const response = await page.goto(base + route, {waitUntil:'domcontentloaded'});
            assert.equal(response.status(), route==='this-route-does-not-exist'?404:200, 'Route ' + route);
            await settleImages(page);
            await measure(page,route,width);
            if ([390,820,1440].includes(width)) await capture(page,route,width);
            if (route === 'media') {
                for (const id of ['videos','published']) {
                    await page.locator('.media-categories a[href="#'+id+'"]').click();
                    await settleImages(page);
                    await measure(page,'media#'+id,width);
                    if ([390,820,1440].includes(width)) await capture(page,'media-'+id,width);
                }
            }
        }
        results.routes.push(route || 'home');
        console.log('PASS: route and layout',route || 'home');
    }

    // Resize through intermediate sizes without reloading, catching media-query
    // transitions, open-menu state and layout collisions between named widths.
    for (const route of interactionsOnly || process.env.XUVERSE_QA_SKIP_SWEEP==='1' ? [] : major) {
        await page.goto(base+route);
        for (let width=320;width<=1920;width+=40) {
            await page.setViewportSize({width,height:900});
            await measure(page,route,width);
        }
    }
    if (!interactionsOnly && process.env.XUVERSE_QA_SKIP_SWEEP!=='1') record('All named widths and a 40px continuous resize sweep across major routes');
    if (process.env.XUVERSE_QA_LAYOUT_ONLY === '1') {
        assert.deepEqual(results.errors,[],'Uncaught JavaScript errors');
        // The intentionally visited 404 route can log a resource-status error.
        results.status='passed'; return;
    }

    for (const route of ['projects','articles']) {
        await page.goto(base+route);
        const input=page.locator('#content-search');
        await input.fill('zzzz-no-results');
        await assertVisible(page.locator('[data-collection-empty]'));
        assert.equal(await page.locator('[data-collection] article:visible').count(),0);
        await page.locator('[data-clear-search]').click();
        assert.equal(await page.locator('[data-collection] article:visible').count(),published(route).length);
        await input.fill(published(route)[0].title.split(' ')[0]);
        assert.ok(await page.locator('[data-collection] article:visible').count()>0);
    }
    record('Project/article search matches, no-result states, clearing and counts');

    await page.setViewportSize({width:320,height:640});
    await page.goto(base);
    await page.locator('.nav-toggle').click();
    assert.equal(await page.locator('.nav-toggle').getAttribute('aria-expanded'),'true');
    await page.keyboard.press('Escape');
    assert.equal(await page.locator('.nav-toggle').getAttribute('aria-expanded'),'false');
    assert.equal(await page.locator('.nav-toggle').evaluate(element=>element===document.activeElement),true);
    await page.locator('.nav-toggle').click();
    await page.locator('.nav-links a').filter({hasText:/^\s*Media\s*$/}).click();
    await page.waitForURL(base+'media');
    await category(page,'photographs');
    const tabs=page.getByRole('tab');
    assert.deepEqual(await tabs.allTextContents(),['Photographs','Videos','Published Work']);
    await tabs.nth(1).click(); await category(page,'videos');
    assert.ok(await page.locator('#videos iframe[src]').count()>0);
    await tabs.nth(2).click(); await category(page,'published');
    assert.equal(await page.locator('#videos iframe[src]').count(),0,'Inactive videos stop');
    await page.goBack(); await category(page,'videos');
    await page.goForward(); await category(page,'published');
    await page.reload(); await category(page,'published');
    await page.waitForFunction(()=>scrollY===0 && !document.activeElement.classList.contains('media-category'));
    await tabs.nth(2).focus(); await page.keyboard.press('Home'); await category(page,'photographs');
    await page.keyboard.press('End'); await category(page,'published');
    await page.keyboard.press('ArrowLeft'); await category(page,'videos');
    await page.keyboard.press('ArrowRight'); await category(page,'published');
    await page.keyboard.press('Space'); await category(page,'published');
    record('Mobile menu, Escape/focus, three equal Media tabs, history, reload, keyboard and video stopping');

    const firstPost=published('published')[0];
    if(firstPost) {
        await page.goto(base+'media#'+firstPost.slug); await category(page,'published');
        await page.goto(base+'photos/'+firstPost.slug);
        assert.equal(await page.locator('.public-return-nav a').first().textContent(),'Published Work');
        await page.locator('.public-return-nav a').first().click(); await category(page,'published');
        const cover=page.locator('#published [data-open-gallery]').first();
        if(await cover.count()) {
            await cover.click();
            const modal=page.locator('.gallery-modal[open]'); await assertVisible(modal);
            const next=modal.locator('[data-gallery-next]');
            if(await next.count()) {
                await next.click();
                await page.waitForFunction(() => document.querySelector('.gallery-modal[open] [data-gallery-counter]')?.textContent.startsWith('2 / '));
                assert.match(await modal.locator('[data-gallery-counter]').textContent(),/^2 \/ /);
                await modal.locator('.post-gallery-track').focus(); await page.keyboard.press('ArrowLeft');
                await page.waitForFunction(() => document.querySelector('.gallery-modal[open] [data-gallery-counter]')?.textContent.startsWith('1 / '));
                assert.match(await modal.locator('[data-gallery-counter]').textContent(),/^1 \/ /);
            }
            await page.keyboard.press('Escape');
            assert.equal(await page.locator('dialog[open]').count(),0);
            assert.equal(await cover.evaluate(element=>element===document.activeElement),true);
        }
        record('Published item bookmarks, correct return category, gallery keyboard controls and focus restoration');
    }
    await page.goto(base+'media');
    await page.locator('.photo-card .media-link').first().click();
    await assertVisible(page.locator('.media-lightbox[open]'));
    await page.keyboard.press('Escape');
    assert.equal(await page.locator('dialog[open]').count(),0);
    record('Photograph lightbox and dismissal');

    // Collect and check first-party navigation destinations without clicking mail,
    // sending forms, logging in or contacting external services.
    const internal=new Set();
    for(const route of major) {
        await page.goto(base+route);
        for(const href of await page.locator('a[href]').evaluateAll(links=>links.map(link=>link.href))) {
            if(href.startsWith(base) && !href.includes('#') && !href.includes('logout')) internal.add(href);
        }
    }
    for(const url of internal) {
        const response=await context.request.get(url);
        assert.equal(response.status(),200,'Internal destination '+url);
    }
    const pdf=await context.request.get(base+'resume/download');
    const bytes=await pdf.body();
    assert.equal(bytes.subarray(0,4).toString(),'%PDF');
    fs.writeFileSync(path.join(output,'resume.pdf'),bytes);
    record('All discovered first-party destinations and resume PDF download');
    const missing=await context.request.get(base+'this-route-does-not-exist');
    assert.equal(missing.status(),404);
    record('404 status retained');

    const noJs=await browser.newContext({javaScriptEnabled:false,viewport:{width:320,height:800}});
    const fallback=await noJs.newPage(); await fallback.goto(base+'media');
    for(const id of ['photographs','videos','published']) await assertVisible(fallback.locator('#'+id));
    assert.equal(await fallback.locator('.xv-signature').count(),1);
    await noJs.close(); record('No-JavaScript categories, native links and background signature');

    await page.emulateMedia({reducedMotion:'no-preference'});
    await page.setViewportSize({width:1440,height:900});
    await page.goto(base); await settleImages(page); await page.waitForTimeout(700);
    assert.equal(await page.locator('.reveal:not(.is-visible)').count(),0,'Every scrolled reveal becomes visible');
    assert.equal(await page.locator('.xv-ambient canvas').count(),0);
    await page.goto(base+'media'); await page.getByRole('tab').nth(2).click();
    await page.waitForTimeout(400); await category(page,'published');
    await page.emulateMedia({reducedMotion:'reduce'});
    assert.equal(await page.locator('#published').evaluate(element=>getComputedStyle(element).animationName),'none');
    await page.goto(base+'resume'); await page.emulateMedia({media:'print'});
    assert.equal(await page.locator('.navbar').isVisible(),false);
    await assertVisible(page.locator('.resume-main'));
    record('Normal content reveals, category transition, live reduced-motion preference and resume print styles');

    assert.deepEqual(results.errors,[],'Uncaught JavaScript errors');
    assert.deepEqual(results.consoleErrors,[],'First-party console errors');
    record('No application JavaScript or first-party console errors');
    results.status='passed';
})().catch(error=>{
    results.status='failed'; results.failure=error.stack; console.error(error); process.exitCode=1;
}).finally(async()=>{
    fs.writeFileSync(path.join(output,reportName),JSON.stringify(results,null,2));
    if(browser) await browser.close();
    console.log('QA evidence:',path.join(output,reportName));
});

async function assertVisible(locator) { assert.equal(await locator.isVisible(),true,'Expected visible: '+locator); }
async function category(page,id) {
    assert.equal(await page.locator('.media-category:visible').count(),1);
    await assertVisible(page.locator('#'+id));
    assert.equal(await page.locator('#media-tab-'+id).getAttribute('aria-selected'),'true');
}
