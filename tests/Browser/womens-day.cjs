// Run against a local Umami instance with Women's Day enabled.
const { chromium } = require(process.env.PLAYWRIGHT_MODULE_PATH || 'playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const base = process.env.UMAMI_TEST_URL || 'http://127.0.0.1:8789';
if (!['127.0.0.1', 'localhost'].includes(new URL(base).hostname)) throw Error('Local QA only');
const output = path.resolve(__dirname, '../../output/womens-day');
fs.mkdirSync(output, {recursive: true});

(async () => {
    const browser = await chromium.launch({channel: 'chrome', headless: true});
    try {
        for (const [name, width, height] of [['desktop', 1440, 1000], ['mobile', 390, 844], ['small-mobile', 320, 740]]) {
            const context = await browser.newContext({viewport: {width, height}});
            const page = await context.newPage();
            const errors = [];
            page.on('pageerror', e => errors.push(e.message));
            await page.route('**/*', route => {
                const host = new URL(route.request().url()).hostname;
                return ['127.0.0.1', 'localhost'].includes(host) ? route.continue() : route.abort();
            });
            await page.goto(base + '/uk', {waitUntil: 'domcontentloaded'});
            const decor = page.locator('#womensDayDecor');
            const petal = page.locator('.wd-petal').first();
            await petal.waitFor();
            await page.waitForFunction(() => [...document.querySelectorAll('#womensDayDecor img')].every(i => i.complete && i.naturalWidth));
            if (await page.locator('#cookieDecline').isVisible()) await page.locator('#cookieDecline').click();
            assert.equal(await decor.getAttribute('aria-hidden'), 'true');
            assert.equal(await decor.evaluate(n => n.inert), true);
            assert.equal(await decor.evaluate(n => getComputedStyle(n).pointerEvents), 'none');
            assert.equal(await page.locator('.wd-corner').count(), 0);
            assert.equal(await page.locator('.wd-accent').count(), 1);
            assert.equal(await page.locator('.wd-petal').count(), width > 700 ? 12 : 6);
            assert(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth));
            assert(await page.locator('.wd-accent').evaluate(n => n.getBoundingClientRect().top >= document.querySelector('.topbar').getBoundingClientRect().bottom));
            // Verify that the generated artwork has real transparency.
            const alpha = await page.locator('.wd-ribbon').evaluate(img => {
                const canvas = document.createElement('canvas');
                canvas.width = img.naturalWidth;
                canvas.height = img.naturalHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0);
                const pixels = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
                let transparent = 0, opaque = 0;
                for (let i = 3; i < pixels.length; i += 4) {
                    if (pixels[i] === 0) transparent++;
                    if (pixels[i] > 220) opaque++;
                }
                return {transparent, opaque, total: pixels.length / 4};
            });
            assert(alpha.transparent / alpha.total > .4);
            assert(alpha.opaque / alpha.total > .1);
            const before = await petal.evaluate(n => getComputedStyle(n).transform);
            await page.waitForTimeout(350);
            assert.notEqual(await petal.evaluate(n => getComputedStyle(n).transform), before);
            await page.screenshot({path: path.join(output, name + '.png')});
            const bottom = await page.locator('.wd-accent').boundingBox();
            await page.evaluate(() => scrollTo({top: 1600, behavior: 'instant'}));
            await page.waitForTimeout(350);
            assert.deepEqual(await page.locator('.wd-accent').boundingBox(), bottom);
            assert.equal(await decor.evaluate(n => getComputedStyle(n).visibility), 'visible');
            await page.screenshot({path: path.join(output, name + '-scrolled.png')});
            await page.locator('#cartOpen').click();
            await page.waitForFunction(() => document.getElementById('womensDayDecor').hidden);
            assert.equal(await petal.evaluate(n => getComputedStyle(n).animationPlayState), 'paused');
            await page.locator('#cartClose').click();
            await page.waitForFunction(() => !document.getElementById('womensDayDecor').hidden);
            // Product dialogs use the same storefront modal.
            await page.locator('#modal').evaluate(n => n.setAttribute('aria-hidden', 'false'));
            await page.waitForFunction(() => document.getElementById('womensDayDecor').hidden);
            await page.locator('#modal').evaluate(n => n.setAttribute('aria-hidden', 'true'));
            await page.waitForFunction(() => !document.getElementById('womensDayDecor').hidden);
            await page.emulateMedia({reducedMotion: 'reduce'});
            await page.waitForFunction(() => document.querySelectorAll('.wd-petal').length === 0);
            assert.equal(await page.locator('.wd-ribbon').evaluate(n => getComputedStyle(n).animationName), 'none');
            assert.deepEqual(errors, []);
            await context.close();
        }
        console.log('PASS: March 8 desktop/mobile, transparent assets, animation, scrolling, cart/modal pause, reduced motion');
    } finally {
        await browser.close();
    }
})().catch(error => {console.error(error); process.exitCode = 1;});
