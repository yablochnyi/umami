(() => {
    const root = document.getElementById('orderTracking');
    if (!root) return;
    const copy = JSON.parse(root.dataset.copy);
    let state = JSON.parse(root.dataset.initial);
    let serverTime = Date.parse(state.server_now);
    let receivedAt = performance.now();
    let busy = false;
    let timer;
    const node = (id) => document.getElementById(id);
    const accepted = () => ['accepted', 'ready', 'delivering', 'completed'].includes(state.status);
    const dateFormat = new Intl.DateTimeFormat(document.documentElement.lang, {
        timeZone: 'Europe/Warsaw', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit',
    });

    function remember() {
        try {
            localStorage.setItem('umami_last_order_status', JSON.stringify({
                path: location.pathname, state,
                expires: Math.max(Date.now(), Date.parse(state.expected_ready_at) || 0) + 7 * 86400000,
            }));
            const marker = 'umami_cart_cleared_' + state.number;
            if (state.submitted && root.dataset.ownsCheckout === '1' && !localStorage.getItem(marker)) {
                localStorage.removeItem('umami_cart');
                localStorage.setItem(marker, '1');
            }
        } catch (_) {}
    }

    function tick() {
        const deadline = Date.parse(state.expected_ready_at);
        const showTime = accepted() && !state.finished && state.status !== 'ready' && Number.isFinite(deadline);
        node('timeBlock').hidden = !showTime;
        const seconds = Math.max(0, Math.ceil((deadline - (serverTime + performance.now() - receivedAt)) / 1000));
        node('overdue').hidden = !showTime || seconds > 0;
        if (showTime) {
            const hours = Math.floor(seconds / 3600);
            node('countdown').textContent = (hours ? hours + ':' : '') +
                String(Math.floor(seconds / 60) % 60).padStart(2, '0') + ':' + String(seconds % 60).padStart(2, '0');
            node('readyTime').textContent = dateFormat.format(deadline);
            node('readyTime').dateTime = state.expected_ready_at;
        }
    }

    function render() {
        node('statusTitle').textContent = copy.states[state.status] || copy.states.submission_uncertain;
        const description = state.status === 'quote_review' ? copy.review
            : state.status === 'goorder_failed' ? copy.failed
            : state.finished ? copy.terminal
            : accepted() ? (state.expected_ready_at || state.status === 'ready' ? '' : copy.no_eta)
            : ['submission_uncertain', 'submitting_goorder', 'preparing_goorder'].includes(state.status) ? copy.uncertain : copy.waiting;
        node('statusDescription').textContent = description;
        node('statusSymbol').dataset.static = String(accepted() || state.status === 'quote_review');
        node('statusSymbol').dataset.error = String(['rejected', 'canceled', 'goorder_failed'].includes(state.status));
        node('connectionWarning').hidden = !state.stale;
        node('lastUpdated').textContent = state.last_synced_at ? copy.updated + ': ' + dateFormat.format(Date.parse(state.last_synced_at)) : '';
        if (node('confirmOrder')) node('confirmOrder').hidden = state.status !== 'quote_review';
        remember();
        tick();
    }

    async function poll() {
        clearTimeout(timer);
        if (busy || state.finished || state.status === 'quote_review') return;
        if (document.hidden) { timer = setTimeout(poll, 10000); return; }
        busy = true;
        try {
            const response = await fetch(root.dataset.statusUrl, {headers: {Accept: 'application/json'}, cache: 'no-store', signal: AbortSignal.timeout(20000)});
            if (!response.ok) throw new Error('Unavailable');
            const next = await response.json();
            state = next;
            serverTime = Date.parse(state.server_now);
            receivedAt = performance.now();
            render();
        } catch (_) {
            node('connectionWarning').hidden = false;
        } finally {
            busy = false;
            timer = setTimeout(poll, (state.poll_seconds || 10) * 1000);
        }
    }

    node('confirmOrder')?.addEventListener('submit', () => { node('confirmOrder').querySelector('button').disabled = true; });
    document.addEventListener('visibilitychange', () => { if (!document.hidden) poll(); });
    window.addEventListener('online', poll);
    window.addEventListener('offline', () => { node('connectionWarning').hidden = false; });
    window.addEventListener('pageshow', (event) => { if (event.persisted) location.reload(); });
    render();
    setInterval(tick, 1000);
    timer = setTimeout(poll, (state.poll_seconds || 10) * 1000);
})();
