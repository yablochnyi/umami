// First render synthetic fixtures with UMAMI_FORM_PREVIEW_DIR=/tmp/umami-menu-form-qa
// php artisan test --filter=MenuTranslationFormTest. No real admin session is used.
const {chromium} = require(process.env.PLAYWRIGHT_MODULE_PATH || 'playwright');
const fs = require('node:fs');
const assert = require('node:assert/strict');
const path = require('node:path');
const output = path.resolve(__dirname, '../../output/menu-forms');
fs.mkdirSync(output, {recursive: true});
(async () => {
    const browser = await chromium.launch({channel: 'chrome', headless: true});
    try {
        for (const width of [1440, 390]) {
            for (const resource of ['menu-items', 'menu-categories']) {
                const page = await browser.newPage({viewport: {width, height: 1000}});
                const errors = [];
                page.on('pageerror', e => errors.push(e.stack || e.message));
                const login = await page.request.get('http://127.0.0.1:8789/admin/login');
                const livewireScript = await page.evaluate(html => {
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    return doc.querySelector('script[data-update-uri]').outerHTML;
                }, await login.text());
                await page.route('**/*', route => {
                    const request = route.request();
                    const url = new URL(request.url());
                    if (request.isNavigationRequest()) {
                        const html = fs.readFileSync(`/tmp/umami-menu-form-qa/${resource}-uk.html`, 'utf8');
                        return route.fulfill({contentType: 'text/html', body: html.replace('</body>', livewireScript + '</body>')});
                    }
                    if (request.method() === 'POST' && url.pathname.endsWith('/update')) {
                        // The synthetic dish has no uploads. Stub only FileUpload's read-only initialization.
                        const {components} = request.postDataJSON();
                        assert(components.every(c => c.calls.every(call => call.method === 'callSchemaComponentMethod'
                            && call.params[0] === 'form.image' && call.params[1] === 'getUploadedFiles')));
                        return route.fulfill({json: {components: components.map(c => ({snapshot: c.snapshot, effects: {returns: c.calls.map(() => [])}})), assets: []}});
                    }
                    if (request.method() !== 'GET' || !['127.0.0.1', 'localhost'].includes(url.hostname)) return route.abort();
                    return route.continue();
                });
                await page.goto(`http://127.0.0.1:8789/admin/${resource}/1/edit`);
                const groups = resource === 'menu-items'
                    ? [['dish-description', 'description'], ['dish-marketing', 'marketing_description'], ['dish-seo', 'seo_description']]
                    : [['category-intro', 'intro_text'], ['category-seo', 'seo_text']];
                for (const [group, field] of groups) {
                    const tabs = page.getByRole('tablist', {name: group, exact: true});
                    await tabs.waitFor();
                    for (const [language, locale] of [['Polski', 'pl'], ['Українська', 'uk'], ['English', 'en']]) {
                        await tabs.getByRole('tab', {name: language, exact: true}).click();
                        const input = page.locator(`textarea[wire\\:model="data.${field}.${locale}"]`);
                        await input.waitFor({state: 'visible'});
                        const box = await input.boundingBox();
                        assert(box.width > (width > 700 ? 700 : 240), `${field} too narrow: ${box.width}`);
                        await input.fill(`${language} sample text`);
                    }
                    await tabs.getByRole('tab', {name: 'Polski', exact: true}).click();
                    assert.equal(await page.locator(`textarea[wire\\:model="data.${field}.pl"]`).inputValue(), 'Polski sample text');
                    await tabs.scrollIntoViewIfNeeded();
                    await page.screenshot({path: path.join(output, `${resource}-${group}-${width}.png`)});
                }
                assert(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth));
                assert.deepEqual(errors, []);
                await page.close();
            }
        }
        console.log('PASS: desktop/mobile full-width text fields, language switching, draft preservation, no horizontal overflow');
    } finally {
        await browser.close();
    }
})().catch(e => { console.error(e); process.exitCode = 1; });
