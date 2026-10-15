/* Normal-motion, short viewport, text scaling and red surface checks.
   Uses the same preinstalled Playwright runtime as refinement-qa.cjs. */
const { chromium } = require('playwright');
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const output = path.resolve(__dirname, '../tmp/refinement-qa');
const base = (process.env.XUVERSE_URL || 'http://localhost/xuverse/').replace(/\/?$/, '/');
const routes = ['', 'projects', 'articles', 'media', 'about', 'resume', 'contact'];
const widths = [320,360,390,430,768,820,1024,1280,1366,1440,1920];
const results = { status:'running', checks:[], errors:[] };
let browser;
const record = label => { results.checks.push(label); console.log('PASS:',label); };
async function redSurface(locator, label) {
    const gradient = await locator.evaluate(element => getComputedStyle(element).backgroundImage);
    const colors = [...gradient.matchAll(/rgba?\((\d+),\s*(\d+),\s*(\d+)/g)];
    assert.ok(colors.length && colors.every(color => Number(color[1]) > Number(color[2])*2 && Number(color[1]) > Number(color[3])*1.5),label);
}
async function bounds(page, label) {
    const violations = await page.evaluate(() => {
        const limit = document.documentElement.clientWidth;
        return [...document.querySelectorAll('main *, nav a, footer *')].filter(element => {
            if (!element.getClientRects().length || element.closest('[hidden],dialog:not([open]),.post-gallery-track')) return false;
            const style = getComputedStyle(element);
            if (style.position === 'absolute' || style.visibility === 'hidden') return false;
            const box = element.getBoundingClientRect();
            return box.width > 0 && (box.left < -1 || box.right > limit+1);
        }).slice(0,6).map(element => element.tagName+'.'+element.className);
    });
    assert.deepEqual(violations,[],label);
}
(async () => {
    fs.mkdirSync(output,{recursive:true});
    browser = await chromium.launch({headless:true,executablePath:process.env.XUVERSE_BROWSER || 'C:/Program Files/Google/Chrome/Application/chrome.exe'});
    const page = await browser.newPage({reducedMotion:'no-preference'});
    page.on('pageerror',error => results.errors.push(error.message));
    for (const route of routes) {
        await page.goto(base+route,{waitUntil:'domcontentloaded'});
        await redSurface(page.locator('.navbar'),'Red navbar');
        await redSurface(page.locator('footer'),'Red footer');
        for (const width of widths) {
            await page.setViewportSize({width,height:900});
            await bounds(page,`Normal motion bounds: ${route} ${width}`);
            assert.equal(await page.locator('.xv-edge-s').evaluate(element => getComputedStyle(element).animationPlayState),'running');
        }
        record(`Normal motion and bounds at 11 widths: ${route || 'home'}`);
        for (const viewport of [{width:844,height:390},{width:667,height:320},{width:320,height:568}]) {
            await page.setViewportSize(viewport);
            await bounds(page,`Short viewport: ${route} ${viewport.width}`);
            const box = await page.locator('.xv-signature').boundingBox();
            assert.ok(box.x >= 0 && box.y >= 0 && box.y+box.height <= viewport.height,'Uncropped XS');
            await page.locator('.nav-toggle').click();
            await redSurface(page.locator('.nav-links'),'Red mobile menu');
            const menu = await page.locator('.nav-links').boundingBox();
            assert.ok(menu.y+menu.height <= viewport.height+1,'Mobile menu fits short viewport');
            await page.locator('.nav-links a').last().scrollIntoViewIfNeeded();
            assert.equal(await page.locator('.nav-links a').last().isVisible(),true);
            await page.keyboard.press('Escape');
        }
        record(`Portrait and short landscape viewports: ${route || 'home'}`);
        await page.setViewportSize({width:390,height:844});
        await page.evaluate(() => document.documentElement.style.fontSize='200%');
        await bounds(page,`200% text: ${route}`);
        await page.evaluate(() => document.documentElement.style.removeProperty('font-size'));
        record(`200% text scaling: ${route || 'home'}`);
        for (const width of [390,820,1440]) {
            await page.setViewportSize({width,height:900});
            await page.evaluate(async () => {
                for(let top=0;top<document.body.scrollHeight;top+=700) {
                    scrollTo({top,behavior:'instant'});
                    await new Promise(resolve => setTimeout(resolve,30));
                }
                scrollTo({top:0,behavior:'instant'});
            });
            await page.waitForTimeout(1000);
            await page.screenshot({path:path.join(output,`final-normal-${route || 'home'}-${width}.png`),fullPage:true});
        }
    }
    await page.goto(base+'media#published');
    await page.locator('[data-open-gallery]').first().click();
    for (const viewport of [{width:320,height:568},{width:667,height:320},{width:1440,height:900}]) {
        await page.setViewportSize(viewport);
        const dialog = page.locator('.gallery-modal[open]');
        await redSurface(dialog,'Red gallery frame');
        const box = await dialog.boundingBox();
        assert.ok(box.x >= 0 && box.y >= 0 && box.x+box.width <= viewport.width && box.y+box.height <= viewport.height);
        await page.locator('dialog[open] .gallery-close').scrollIntoViewIfNeeded();
    }
    await page.keyboard.press('Escape');
    await page.goto(base+'media');
    await page.locator('.photo-card .media-link').first().click();
    await page.setViewportSize({width:667,height:320});
    const stage = await page.locator('.lightbox-stage').boundingBox();
    assert.ok(stage.height > 64,'Photo has usable landscape viewing space');
    await page.keyboard.press('Escape');
    record('Published gallery and photo lightbox fit short landscape screens');
    await page.goto(base+'projects');
    await page.setViewportSize({width:1440,height:900});
    const link = page.locator('.public-card .text-link').first();
    await link.scrollIntoViewIfNeeded();
    await page.waitForTimeout(1000);
    await link.hover();
    await page.waitForTimeout(300);
    assert.equal(await link.evaluate(element => getComputedStyle(element).backgroundSize),'100% 1px','Animated link underline reaches full width');
    await link.focus();
    assert.equal(await link.evaluate(element => getComputedStyle(element).outlineStyle),'solid','Keyboard focus remains visible');
    record('Red shared surfaces, animated underline and visible keyboard focus');
    assert.deepEqual(results.errors,[]);
    results.status='passed';
})().catch(error => { results.status='failed'; results.failure=error.stack; console.error(error); process.exitCode=1; })
.finally(async () => { fs.writeFileSync(path.join(output,'extra-audit.json'),JSON.stringify(results,null,2)); if(browser) await browser.close(); });
