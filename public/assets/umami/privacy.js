(() => {
    const key = 'umami_cookie_consent';
    const analyticsId = document.body.dataset.googleAnalyticsId || '';
    const banner = document.getElementById('cookieConsent');
    let loaded = false;
    let returnFocus = null;

    function readConsent() {
        try {
            const value = localStorage.getItem(key);
            if (value) return value;
        } catch (_) {}
        return document.cookie.split('; ').find(value => value.startsWith(key + '='))?.split('=')[1] || null;
    }

    function saveConsent(value) {
        try { localStorage.setItem(key, value); } catch (_) {}
        // Keep the fallback in sync even when storage permissions change.
        document.cookie = key + '=' + value + '; Path=/; Max-Age=31536000; SameSite=Lax'
            + (location.protocol === 'https:' ? '; Secure' : '');
    }

    function loadAnalytics() {
        if (loaded || !/^G-[A-Z0-9]+$/i.test(analyticsId)) return;
        loaded = true;
        window['ga-disable-' + analyticsId] = false;
        window.dataLayer = window.dataLayer || [];
        window.gtag = function () { window.dataLayer.push(arguments); };
        window.gtag('consent', 'default', {
            analytics_storage: 'granted',
            ad_storage: 'denied',
            ad_user_data: 'denied',
            ad_personalization: 'denied',
        });
        window.gtag('js', new Date());
        window.gtag('config', analyticsId, {
            allow_google_signals: false,
            allow_ad_personalization_signals: false,
        });
        const script = document.createElement('script');
        script.async = true;
        script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(analyticsId);
        document.head.append(script);
    }

    function removeAnalyticsCookies() {
        const labels = location.hostname.split('.');
        const domains = ['', ...labels.map((_, index) => labels.slice(index).join('.'))];
        const names = document.cookie.split('; ').map(value => value.split('=')[0]);
        for (const name of names.filter(name => /^_ga($|_)/.test(name))) {
            for (const domain of domains) {
                document.cookie = name + '=; Max-Age=0; Path=/' + (domain ? '; Domain=' + domain : '');
            }
        }
    }

    function applyConsent(value) {
        if (banner) banner.hidden = ['accepted', 'declined'].includes(value);
        if (value === 'accepted') {
            loadAnalytics();
        } else {
            window['ga-disable-' + analyticsId] = true;
            removeAnalyticsCookies();
            // Reload to stop an already-loaded third-party runtime without new pings.
            if (loaded) location.reload();
        }
    }

    function choose(value) {
        saveConsent(value);
        applyConsent(value);
        returnFocus?.focus();
    }

    document.getElementById('cookieAccept')?.addEventListener('click', () => choose('accepted'));
    document.getElementById('cookieDecline')?.addEventListener('click', () => choose('declined'));
    document.querySelectorAll('[data-cookie-settings]').forEach(button => {
        button.addEventListener('click', () => {
            if (!banner) return;
            returnFocus = button;
            banner.hidden = false;
            document.getElementById('cookieDecline')?.focus();
        });
    });
    window.addEventListener('storage', event => {
        if (event.key === key || event.key === null) applyConsent(event.newValue);
    });
    document.querySelectorAll('[data-map-load]').forEach(button => {
        button.addEventListener('click', () => {
            const notice = button.closest('[data-map-notice]');
            const frame = notice?.parentElement.querySelector('[data-map-src]');
            if (!frame) return;
            frame.src = frame.dataset.mapSrc;
            frame.hidden = false;
            notice.hidden = true;
            frame.focus();
        });
    });
    applyConsent(readConsent());
})();
