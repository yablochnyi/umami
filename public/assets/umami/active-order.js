(() => {
    const link = document.getElementById('activeOrderLink');
    if (!link || document.getElementById('orderTracking')) return;
    let saved;
    try { saved = JSON.parse(localStorage.getItem('umami_last_order_status') || 'null'); } catch (_) {}
    if (!saved || !/^\/zamowienie\/[A-Za-z0-9]{64}$/.test(saved.path) || saved.expires < Date.now()) return;
    const copy = JSON.parse(link.dataset.copy);
    let state = saved.state || {};
    let offset = 0;
    link.href = saved.path;
    link.hidden = false;
    function render() {
        const title = copy.states[state.status] || copy.return;
        const remaining = Math.max(0, Math.ceil((Date.parse(state.expected_ready_at) - Date.now() - offset) / 1000));
        const showTime = ['accepted', 'delivering'].includes(state.status) && remaining > 0;
        link.textContent = title + (showTime ? ' · ' + Math.floor(remaining / 60) + ':' + String(remaining % 60).padStart(2, '0') : '');
    }
    async function poll() {
        if (!document.hidden && !state.finished) {
            try {
                const response = await fetch('/api/orders/' + saved.path.split('/').pop(), {cache: 'no-store', headers: {Accept: 'application/json'}, signal: AbortSignal.timeout(20000)});
                if (!response.ok) throw new Error('Unavailable');
                state = await response.json();
                offset = Date.parse(state.server_now) - Date.now();
                try { localStorage.setItem('umami_last_order_status', JSON.stringify({...saved, state})); } catch (_) {}
                render();
            } catch (_) {}
        }
        setTimeout(poll, 15000);
    }
    render();
    setInterval(render, 1000);
    poll();
})();
