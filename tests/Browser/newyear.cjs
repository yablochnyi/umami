// Run against a local Umami instance with New Year enabled.
// PLAYWRIGHT_MODULE_PATH may point at an existing Playwright installation.
const { chromium } = require(process.env.PLAYWRIGHT_MODULE_PATH || 'playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const base = process.env.UMAMI_TEST_URL || 'http://127.0.0.1:8789';
if (!['127.0.0.1', 'localhost'].includes(new URL(base).hostname)) throw Error('Local QA only');
const output = path.resolve(__dirname, '../../output/newyear');
fs.mkdirSync(output, { recursive: true });

(async () => {
    const browser = await chromium.launch({channel: 'chrome', headless: true});
    try {
        for (const [name, width, height] of [['desktop', 1440, 1000], ['mobile', 390, 844], ['small-mobile', 320, 740]]) {
            const context = await browser.newContext({viewport: {width, height}});
            const page = await context.newPage();
            const errors = [];
            page.on('pageerror', error => errors.push(error.message));
            // Keep QA local: do not contact chat, maps, analytics or ordering providers.
            await page.route('**/*', route => {
                const url = new URL(route.request().url());
                return ['127.0.0.1', 'localhost'].includes(url.hostname) ? route.continue() : route.abort();
            });
            await page.goto(base + '/uk', {waitUntil: 'domcontentloaded'});
            await page.locator('#newyearDecor .ny-snow').first().waitFor();
            await page.waitForFunction(() => [...document.querySelectorAll('#newyearDecor img')].every(i => i.complete && i.naturalWidth));
            if (await page.locator('#cookieDecline').isVisible()) await page.locator('#cookieDecline').click();
            await page.waitForTimeout(500);
            assert.equal(await page.locator('#newyearDecor .ny-snow').count(), width > 700 ? 42 : 20);
            assert(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth));
            assert.equal(await page.locator('#newyearDecor').evaluate(n => getComputedStyle(n).pointerEvents), 'none');
            const headerBottom = await page.locator('.topbar').evaluate(n => n.getBoundingClientRect().bottom);
            const cornerTop = await page.locator('.ny-left').evaluate(n => n.getBoundingClientRect().top);
            if (width > 700) {
                assert.equal(cornerTop, 0, 'Desktop corners attach to viewport top');
                assert.equal(await page.locator('.ny-lights').evaluate(n => n.getBoundingClientRect().top), 0);
                assert(await page.evaluate(() => Number(getComputedStyle(document.querySelector('.topbar')).zIndex) > Number(getComputedStyle(document.getElementById('newyearDecor')).zIndex)));
            } else assert(cornerTop >= headerBottom, 'Mobile decor must not overlap navigation');
            const flake = page.locator('.ny-snow').first();
            assert.match(await flake.getAttribute('src'), /snowflake\.svg/);
            assert(await flake.evaluate(n => n.getBoundingClientRect().width >= 10));
            const bulb = page.locator('.ny-bulb').first();
            const lights = await page.locator('.ny-bulb').evaluateAll(nodes => {
                const shown = nodes.filter(n => getComputedStyle(n).display !== 'none');
                return {count: shown.length, colors: new Set(shown.map(n => getComputedStyle(n).backgroundColor)).size};
            });
            assert.equal(lights.count, width > 700 ? 64 : 32);
            assert.equal(lights.colors, 8);
            const initialOpacity = await bulb.evaluate(n => getComputedStyle(n).opacity);
            const before = await flake.evaluate(n => getComputedStyle(n).transform);
            await page.waitForTimeout(350);
            assert.notEqual(await flake.evaluate(n => getComputedStyle(n).transform), before);
            assert.notEqual(await bulb.evaluate(n => getComputedStyle(n).opacity), initialOpacity);
            await page.screenshot({path: path.join(output, name + '.png')});
            assert.equal(await page.locator('.ny-snowman').count(), 1);
            assert.equal(await page.locator('.ny-fox-frame').count(), 4);
            const foxSprite = await page.locator('.ny-fox-frame image').first().getAttribute('href');
            assert.equal((await page.request.get(new URL(foxSprite, base).href)).status(), 200);
            const storyTime = async time => page.locator('.ny-story').evaluate((node, time) => {
                for (const animation of node.getAnimations({subtree: true})) {
                    animation.pause();
                    animation.currentTime = time;
                }
            }, time);
            await storyTime(3000);
            assert.equal(await page.locator('.ny-fox-run').evaluate(n => getComputedStyle(n).opacity), '1');
            assert.equal(await page.locator('.ny-fox-eat').evaluate(n => getComputedStyle(n).opacity), '0');
            await page.screenshot({path: path.join(output, name + '-fox-running.png')});
            await storyTime(9000);
            assert.equal(await page.locator('.ny-fox-run').evaluate(n => getComputedStyle(n).opacity), '0');
            assert.equal(await page.locator('.ny-fox-eat').evaluate(n => getComputedStyle(n).opacity), '1');
            assert.equal(await page.locator('.ny-sushi').evaluate(n => getComputedStyle(n).opacity), '0');
            if (width <= 700) {
                await page.waitForFunction(() => ['.ny-story', '.ny-snowman'].every(selector => getComputedStyle(document.querySelector(selector)).visibility === 'visible'));
            }
            await page.screenshot({path: path.join(output, name + '-fox-eating.png')});
            if (width <= 700) {
                const actors = page.locator('.ny-snowman, .ny-fox');
                const positions = () => actors.evaluateAll(nodes => nodes.map(n => ({
                    top: n.getBoundingClientRect().top,
                    bottom: n.getBoundingClientRect().bottom,
                    visibility: getComputedStyle(n).visibility,
                })));
                const initialPositions = await positions();
                for (const y of [800, 1600, 2400, 4000, 0]) {
                    await page.evaluate(y => scrollTo({top: y, behavior: 'instant'}), y);
                    await page.waitForTimeout(300);
                    assert.deepEqual(await positions(), initialPositions, 'Scrolling must not move or hide characters');
                    if (y === 1600) await page.screenshot({path: path.join(output, name + '-fixed-characters.png')});
                }
                await page.evaluate(() => {
                    const button = document.createElement('button');
                    button.id = 'newyear-test-control';
                    button.textContent = 'QA';
                    button.style.cssText = 'position:fixed;bottom:0;left:0;width:100%;height:400px;z-index:18';
                    button.addEventListener('click', () => button.dataset.clicked = 'true');
                    document.body.append(button);
                });
                await page.waitForTimeout(600);
                assert.deepEqual(await positions(), initialPositions, 'Controls must not move or hide characters');
                for (const actor of await actors.all()) {
                    const r = await actor.boundingBox();
                    await page.mouse.click(r.x + r.width / 2, r.y + r.height / 2);
                    assert.equal(await page.locator('#newyear-test-control').getAttribute('data-clicked'), 'true');
                    await page.locator('#newyear-test-control').evaluate(n => delete n.dataset.clicked);
                }
                await page.locator('#newyear-test-control').evaluate(n => n.remove());
            }
            await storyTime(16000);
            assert.equal(await page.locator('.ny-fox-run').evaluate(n => getComputedStyle(n).opacity), '1');
            await storyTime(25000);
            assert.equal(await page.locator('.ny-fox').evaluate(n => getComputedStyle(n).opacity), '0');
            await page.locator('.ny-story').evaluate(node => node.getAnimations({subtree: true}).forEach(a => a.play()));
            await page.locator('#cartOpen').click();
            await page.waitForFunction(() => document.getElementById('newyearDecor').hidden);
            assert.equal(await flake.evaluate(n => getComputedStyle(n).animationPlayState), 'paused');
            assert.equal(await page.locator('.ny-fox').evaluate(n => getComputedStyle(n).animationPlayState), 'paused');
            await page.locator('#cartClose').click();
            await page.waitForFunction(() => !document.getElementById('newyearDecor').hidden);
            await page.evaluate(() => scrollTo(0, 800));
            await page.waitForTimeout(300);
            await page.screenshot({path: path.join(output, name + '-scrolled.png')});
            assert.equal(await flake.evaluate(n => getComputedStyle(n).animationPlayState), 'running');
            await page.emulateMedia({reducedMotion: 'reduce'});
            await page.waitForFunction(() => document.querySelectorAll('.ny-snow').length === 6);
            assert.equal(await flake.evaluate(n => getComputedStyle(n).animationName), 'none');
            assert.equal(await page.locator('.ny-story').evaluate(n => getComputedStyle(n).display), 'none');
            assert.deepEqual(errors, []);
            await context.close();
        }
        console.log('PASS: Umami New Year desktop/mobile, image alpha asset, navigation clearance, snow animation, cart pause, scrolling, reduced motion');
    } finally {
        await browser.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
