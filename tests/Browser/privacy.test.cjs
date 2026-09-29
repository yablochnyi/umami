const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/umami/privacy.js'), 'utf8');

function page({choice, analyticsId = 'G-TEST123', storageBlocked = false, cookieChoice} = {}) {
    const stored = new Map(choice ? [['umami_cookie_consent', choice]] : []);
    const cookies = new Map(cookieChoice ? [['umami_cookie_consent', cookieChoice]] : []);
    const scripts = [];
    const listeners = {};
    const node = () => ({hidden: true, handlers: {}, addEventListener(type, callback) { this.handlers[type] = callback; }, focus() {}});
    const elements = Object.fromEntries(['cookieConsent', 'cookieAccept', 'cookieDecline'].map(id => [id, node()]));
    const settings = node();
    let reloads = 0;
    const document = {
        body: {dataset: {googleAnalyticsId: analyticsId}},
        head: {append: script => scripts.push(script)},
        createElement: () => ({}),
        getElementById: id => elements[id],
        querySelectorAll: selector => selector === '[data-cookie-settings]' ? [settings] : [],
        get cookie() { return [...cookies].map(([key, value]) => key + '=' + value).join('; '); },
        set cookie(value) {
            const [key, content] = value.split(';')[0].split('=');
            if (value.includes('Max-Age=0')) cookies.delete(key);
            else cookies.set(key, content);
        },
    };
    const window = {addEventListener: (type, callback) => { listeners[type] = callback; }};
    vm.runInNewContext(source, {
        window, document,
        localStorage: {
            getItem(key) { if (storageBlocked) throw Error('blocked'); return stored.get(key) || null; },
            setItem(key, value) { if (storageBlocked) throw Error('blocked'); stored.set(key, value); },
        },
        location: {protocol: 'https:', hostname: 'umamisushifood.pl', reload: () => reloads++},
    });
    return {window, stored, cookies, scripts, elements, settings, listeners, get reloads() { return reloads; },
        click: id => elements[id].handlers.click()};
}

test('no analytics before consent or after refusal, including malformed choices', () => {
    for (const choice of [undefined, 'declined', 'invalid']) {
        const p = page({choice});
        assert.equal(p.scripts.length, 0);
        assert.equal(p.elements.cookieConsent.hidden, choice === 'declined');
        p.click('cookieDecline');
        assert.equal(p.stored.get('umami_cookie_consent'), 'declined');
        assert.equal(p.scripts.length, 0);
    }
});

test('acceptance loads once with advertising denied; footer reopens the choice', () => {
    const p = page();
    p.click('cookieAccept');
    p.click('cookieAccept');
    assert.equal(p.scripts.length, 1);
    assert.equal(p.window.dataLayer[0][2].analytics_storage, 'granted');
    assert.equal(p.window.dataLayer[0][2].ad_storage, 'denied');
    assert.equal(p.window.dataLayer[0][2].ad_personalization, 'denied');
    assert.equal(p.window.dataLayer[2][2].allow_google_signals, false);
    p.settings.handlers.click();
    assert.equal(p.elements.cookieConsent.hidden, false);
});

test('withdrawal disables collection, removes analytics cookies and preserves basket', () => {
    const p = page({choice: 'accepted'});
    p.cookies.set('_ga', 'identifier');
    p.cookies.set('_ga_TEST123', 'session');
    p.cookies.set('XSRF-TOKEN', 'essential');
    p.stored.set('umami_cart', 'basket');
    p.click('cookieDecline');
    assert.equal(p.window['ga-disable-G-TEST123'], true);
    assert.equal(p.cookies.has('_ga'), false);
    assert.equal(p.cookies.has('_ga_TEST123'), false);
    assert.equal(p.cookies.get('XSRF-TOKEN'), 'essential');
    assert.equal(p.stored.get('umami_cart'), 'basket');
    assert.equal(p.reloads, 1);
});

test('saved consent and cookie fallback work when localStorage is blocked', () => {
    assert.equal(page({choice: 'accepted'}).scripts.length, 1);
    assert.equal(page({storageBlocked: true, cookieChoice: 'accepted'}).scripts.length, 1);
    const p = page({storageBlocked: true});
    p.click('cookieDecline');
    assert.equal(p.cookies.get('umami_cookie_consent'), 'declined');
    assert.equal(p.scripts.length, 0);
});

test('private pages and invalid analytics identifiers never load the vendor', () => {
    for (const analyticsId of ['', 'not-a-measurement-id']) {
        assert.equal(page({choice: 'accepted', analyticsId}).scripts.length, 0);
    }
});

test('another tab withdrawing consent stops the current analytics runtime', () => {
    const p = page({choice: 'accepted'});
    p.listeners.storage({key: 'umami_cookie_consent', newValue: 'declined'});
    assert.equal(p.window['ga-disable-G-TEST123'], true);
    assert.equal(p.reloads, 1);
});
