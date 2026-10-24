(() => {
    const endpoint = document.body.dataset.websiteAnalyticsUrl;
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!endpoint || !token) return;

    const cookie = name => {
        try { return document.cookie.split('; ').find(part => part.startsWith(name + '='))?.split('=')[1]; }
        catch (_) { return null; }
    };
    const cookieName = 'umami_visitor_id';
    let accepted = false;
    let pageSent = false;
    let menuSent = false;
    let menuVisible = false;
    let queue = Promise.resolve();
    let generation = 0;

    function attribution() {
        const params = new URLSearchParams(location.search);
        const safe = value => /^[a-z0-9_-]{1,96}$/i.test(value || '') ? value : null;
        let host = null;
        try { host = new URL(document.referrer).hostname; } catch (_) {}
        return {referrer_host: host, utm_source: safe(params.get('utm_source')), utm_campaign: safe(params.get('utm_campaign'))};
    }

    function send(name, itemId = null) {
        if (!accepted || cookie('umami_cookie_consent') !== 'accepted' || !cookie(cookieName)) return;
        const payload = {
            event_id: crypto.randomUUID(), name, path: location.pathname,
            menu_item_id: itemId ? Number(itemId) : null, ...attribution(),
        };
        const currentGeneration = generation;
        queue = queue.then(async () => {
            if (!accepted || generation !== currentGeneration) return;
            try {
                await fetch(endpoint, {
                    method: 'POST', credentials: 'same-origin', keepalive: true,
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token},
                    body: JSON.stringify(payload),
                });
            } catch (_) {}
        });
    }

    function menuView() {
        if (accepted && menuVisible && !menuSent) {
            menuSent = true;
            send('menu_view');
        }
    }

    window.umamiAnalytics = {
        consent(value) {
            accepted = value === 'accepted' && !!globalThis.crypto?.randomUUID;
            if (!accepted) {
                generation++;
                try { document.cookie = cookieName + '=; Path=/; Max-Age=0; SameSite=Lax'; } catch (_) {}
                pageSent = false;
                menuSent = false;
                return;
            }
            if (!/^[a-f0-9-]{36}$/i.test(cookie(cookieName) || '')) {
                try {
                    document.cookie = cookieName + '=' + crypto.randomUUID() + '; Path=/; Max-Age=31536000; SameSite=Lax'
                        + (location.protocol === 'https:' ? '; Secure' : '');
                } catch (_) { accepted = false; return; }
            }
            // With cookies blocked, do not invent a new visitor on every page.
            if (!cookie(cookieName) || cookie('umami_cookie_consent') !== 'accepted') return;
            if (!pageSent) {
                pageSent = true;
                send('page_view');
                const kind = document.body.dataset.websitePage;
                if (kind === 'menu') { menuVisible = true; menuView(); }
                if (kind === 'item') send('product_view', document.body.dataset.websiteItemId);
                let purchase = null;
                try { purchase = JSON.parse(document.body.dataset.purchaseEvent || 'null'); } catch (_) {}
                if (kind === 'checkout' && !purchase?.transaction_id) send('begin_checkout');
            }
            menuView();
        },
        track: send,
    };

    const menu = document.getElementById('menu');
    if (menu && 'IntersectionObserver' in window) {
        new IntersectionObserver(entries => {
            menuVisible = entries.some(entry => entry.isIntersecting);
            menuView();
        }, {threshold: 0}).observe(menu);
    }
})();
