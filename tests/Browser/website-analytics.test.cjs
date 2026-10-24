const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const {webcrypto} = require('node:crypto');
const source = fs.readFileSync(require('node:path').join(__dirname, '../../public/assets/umami/website-analytics.js'), 'utf8');

function page({cookies = new Map(), kind = 'page', privatePage = false, search = '', storageBlocked = false} = {}) {
    const requests = [];
    const window = {};
    const document = {
        body: {dataset: {websiteAnalyticsUrl: privatePage ? '' : '/api/site-events', websitePage: kind, websiteItemId: '12', purchaseEvent: 'null'}},
        querySelector: () => ({content: 'csrf-token'}), getElementById: () => null,
        referrer: 'https://www.google.com/search?q=private+query',
        get cookie() { return [...cookies].map(([k, v]) => k + '=' + v).join('; '); },
        set cookie(value) {
            if (storageBlocked) return;
            const [name, content] = value.split(';')[0].split('=');
            if (value.includes('Max-Age=0')) cookies.delete(name); else cookies.set(name, content);
        },
    };
    vm.runInNewContext(source, {window, document, crypto: webcrypto, URL, URLSearchParams,
        location: {pathname: '/', search, protocol: 'https:'},
        fetch: async (url, options) => { requests.push({url, options, data: JSON.parse(options.body)}); return {ok: true}; },
    });
    return {window, requests, cookies, consent(value) { cookies.set('umami_cookie_consent', value); window.umamiAnalytics?.consent(value); }};
}
const flush = () => new Promise(resolve => setImmediate(resolve));

test('no identifier or events before consent and after refusal', async () => {
    const p = page();
    assert.equal(p.cookies.size, 0);
    p.window.umamiAnalytics.track('page_view');
    p.consent('declined');
    await flush();
    assert.equal(p.requests.length, 0);
    assert.equal(p.cookies.has('umami_visitor_id'), false);
});

test('one page event per page, cookie persists across reloads and repeated acceptance', async () => {
    const p = page();
    p.consent('accepted'); p.consent('accepted');
    await flush();
    assert.equal(p.requests.length, 1);
    const visitor = p.cookies.get('umami_visitor_id');
    const reloaded = page({cookies: p.cookies});
    reloaded.consent('accepted');
    await flush();
    assert.equal(reloaded.requests.length, 1);
    assert.equal(reloaded.cookies.get('umami_visitor_id'), visitor);
    assert.notEqual(reloaded.requests[0].data.event_id, p.requests[0].data.event_id);
});

test('referrers and query strings are minimized and same-origin requests carry CSRF', async () => {
    const p = page({search: '?email=private@example.test&utm_source=Instagram&utm_campaign=autumn'});
    p.consent('accepted'); await flush();
    const {data, options, url} = p.requests[0];
    assert.equal(data.path, '/');
    assert.equal(data.referrer_host, 'www.google.com');
    assert.equal(data.utm_source, 'Instagram');
    assert.equal(data.utm_campaign, 'autumn');
    assert.equal(JSON.stringify(data).includes('private'), false);
    assert.equal(url, '/api/site-events');
    assert.equal(options.credentials, 'same-origin');
    assert.equal(options.headers['X-CSRF-TOKEN'], 'csrf-token');
});

test('withdrawal clears identifier and cancels pending events without affecting cart', async () => {
    const p = page(); p.cookies.set('umami_cart', 'keep');
    p.consent('accepted'); p.consent('declined');
    p.window.umamiAnalytics.track('add_to_cart', 12);
    await flush();
    assert.equal(p.requests.length, 0);
    assert.equal(p.cookies.has('umami_visitor_id'), false);
    assert.equal(p.cookies.get('umami_cart'), 'keep');
});

test('private pages and blocked cookies produce no observations', async () => {
    const p = page({privatePage: true}); p.consent('accepted');
    assert.equal(p.window.umamiAnalytics, undefined);
    const blocked = page({storageBlocked: true}); blocked.consent('accepted');
    await flush();
    assert.equal(blocked.requests.length, 0);
});

test('menu, product, cart and checkout events are distinct', async () => {
    for (const [kind, expected] of [['menu', 'menu_view'], ['item', 'product_view'], ['checkout', 'begin_checkout']]) {
        const p = page({kind}); p.consent('accepted');
        p.window.umamiAnalytics.track('add_to_cart', '12');
        await flush();
        assert.deepEqual(p.requests.map(r => r.data.name), ['page_view', expected, 'add_to_cart']);
        assert.equal(p.requests.at(-1).data.menu_item_id, 12);
    }
});
