(() => {
    const node = document.getElementById('menuAvailabilityData');
    if (!node) return;
    const initial = JSON.parse(node.textContent);
    let revision = 0;
    const menu = window.umamiMenu = {
        items: initial.items, copy: initial.copy, pending: false, failed: false,
        available(id) { return !this.pending && !this.failed && this.items[id]?.available === true; },
        apply() {
            document.querySelectorAll('[data-cart-control]').forEach((control) => {
                const state = this.items[control.dataset.cartId];
                const unavailable = !this.available(control.dataset.cartId);
                control.querySelectorAll('[data-cart-add], [data-cart-increase]').forEach((button) => {
                    button.disabled = unavailable;
                    button.classList.toggle('is-unavailable', unavailable);
                    button.setAttribute('aria-disabled', String(unavailable));
                    button.title = unavailable ? [this.copy.unavailable, state?.label].filter(Boolean).join(': ') : (button.getAttribute('aria-label') || '');
                });
            });
            document.querySelectorAll('[data-modal-card][data-cart-id], .product-cart-control').forEach((card) => {
                const state = this.items[card.dataset.cartId];
                let label = card.matches('.product-cart-control') ? document.getElementById('productAvailability') : card.querySelector('[data-availability-label]');
                if (!label) {
                    label = document.createElement('p');
                    label.dataset.availabilityLabel = '';
                    label.className = 'menu-availability-label';
                    (card.querySelector('.dish-body') || card.parentElement).appendChild(label);
                    // Product controls do not contain their label.
                    if (card.matches('.product-cart-control')) card.dataset.availabilityLabelId = 'productAvailability';
                    if (card.matches('.product-cart-control')) label.id = 'productAvailability';
                }
                label.textContent = [!this.available(card.dataset.cartId) ? this.copy.unavailable : '', state?.label].filter(Boolean).join(' · ');
                label.hidden = !label.textContent;
            });
        },
        async refresh() {
            const current = ++revision;
            this.pending = true;
            document.dispatchEvent(new Event('menu-availability'));
            const params = new URLSearchParams({ locale: document.documentElement.lang });
            const form = document.getElementById('checkoutForm');
            if (form) {
                params.set('type', form.querySelector('[name="delivery_type"]:checked')?.value === 'delivery' ? 'DELIVERY' : 'PICK_UP');
                if (form.querySelector('[name="fulfillment_type"]:checked')?.value === 'scheduled') {
                    const day = form.querySelector('[name="scheduled_day"]').value;
                    const time = form.querySelector('[name="scheduled_time"]').value;
                    if (day && time) { params.set('day', day); params.set('time', time); }
                }
            }
            try {
                const response = await fetch('/api/menu-availability?' + params, { cache: 'no-store', signal: AbortSignal.timeout(10000) });
                if (!response.ok) throw new Error('availability');
                const data = await response.json();
                if (current !== revision) return;
                this.items = data.items;
                this.failed = false;
            } catch (_) {
                if (current !== revision) return;
                this.failed = true;
            } finally {
                if (current === revision) {
                    this.pending = false;
                    this.apply();
                    document.dispatchEvent(new Event('menu-availability'));
                }
            }
        },
    };
    // Keep the availability display current on pages left open across a time boundary.
    menu.apply();
    window.setInterval(() => { if (!document.hidden) menu.refresh(); }, 30000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) menu.refresh(); });
    window.addEventListener('pageshow', () => menu.refresh());
    document.addEventListener('change', (event) => {
        if (event.target.matches('[name="delivery_type"], [name="fulfillment_type"], [name="scheduled_day"], [name="scheduled_time"]')) menu.refresh();
    });
})();
