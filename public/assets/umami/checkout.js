const cartStorageKey = 'umami_cart';
const desktopBackground = document.body.dataset.backgroundDesktop;
const mobileBackground = document.body.dataset.backgroundMobile;
const deliveryCost = Number.parseFloat(document.body.dataset.deliveryCost || '0') || 0;
const freeDeliveryFrom = Number.parseFloat(document.body.dataset.freeDeliveryFrom || '0') || 0;
const minimumDeliveryAmount = Number.parseFloat(document.body.dataset.minimumDeliveryAmount || '0') || 0;
const orderingOpen = document.body.dataset.orderingOpen !== '0';
const orderingUnavailableMessage = document.body.dataset.orderingUnavailableMessage || '';
const copy = {
    emptyCart: document.body.dataset.emptyCart || 'Koszyk jest pusty.',
    freeMissing: document.body.dataset.freeMissing || '',
    freeReady: document.body.dataset.freeReady || '',
    minimumMissing: document.body.dataset.minimumMissing || '',
};

if (document.body.dataset.clearCart === '1') {
    window.localStorage.removeItem(cartStorageKey);
}

if (desktopBackground) {
    document.body.style.setProperty('--umami-bg-desktop', `url('${desktopBackground}')`);
}

if (mobileBackground) {
    document.body.style.setProperty('--umami-bg-mobile', `url('${mobileBackground}')`);
}

function readCart() {
    try {
        const cart = JSON.parse(window.localStorage.getItem(cartStorageKey) || '{}');
        return cart && typeof cart === 'object' ? cart : {};
    } catch (error) {
        return {};
    }
}

function writeCart(cart) {
    window.localStorage.setItem(cartStorageKey, JSON.stringify(cart));
}

function parsePrice(price) {
    const normalized = String(price || '').replace(/\s/g, '').replace(',', '.').replace(/[^0-9.]/g, '');

    return Number.parseFloat(normalized) || 0;
}

function formatPrice(amount) {
    const rounded = Math.round((Number(amount) || 0) * 100) / 100;

    return Number.isInteger(rounded)
        ? rounded + ' zł'
        : rounded.toFixed(2).replace('.', ',') + ' zł';
}

function sortedItems(cart) {
    return Object.values(cart)
        .filter((item) => Number(item.quantity || 0) > 0)
        .sort((a, b) => String(a.name || '').localeCompare(String(b.name || ''), document.documentElement.lang || 'pl'));
}

function deliveryType() {
    return document.querySelector('input[name="delivery_type"]:checked')?.value || 'pickup';
}

function cartSubtotal(items) {
    return items.reduce((sum, item) => sum + parsePrice(item.price) * Number(item.quantity || 0), 0);
}

function effectiveDeliveryCost(subtotal) {
    if (deliveryType() !== 'delivery') return 0;
    if (freeDeliveryFrom > 0 && subtotal >= freeDeliveryFrom) return 0;

    return deliveryCost;
}

function renderCart() {
    const cart = readCart();
    const items = sortedItems(cart);
    const itemsNode = document.getElementById('checkoutItems');
    const emptyNode = document.getElementById('checkoutEmpty');
    const cartJsonNode = document.getElementById('cartJson');
    const submitButton = document.getElementById('submitOrder');
    const subtotal = cartSubtotal(items);
    const currentDeliveryCost = effectiveDeliveryCost(subtotal);
    const total = subtotal + currentDeliveryCost;

    itemsNode.innerHTML = '';
    emptyNode.hidden = items.length > 0;
    submitButton.disabled = !orderingOpen
        || items.length === 0
        || (deliveryType() === 'delivery' && minimumDeliveryAmount > 0 && subtotal < minimumDeliveryAmount);
    cartJsonNode.value = JSON.stringify(items.map((item) => ({
        id: item.id,
        quantity: Number(item.quantity || 0),
    })));

    items.forEach((item) => {
        const node = document.createElement('article');
        node.className = 'summary-item';

        const image = document.createElement('img');
        image.src = item.image || '';
        image.alt = item.name || '';
        image.hidden = !item.image;

        const body = document.createElement('div');
        const title = document.createElement('h3');
        title.textContent = item.name || '';
        const price = document.createElement('p');
        price.textContent = item.price || '';
        body.append(title, price);

        const remove = document.createElement('button');
        remove.className = 'remove-item';
        remove.type = 'button';
        remove.dataset.remove = item.id;
        remove.textContent = '×';

        const controls = document.createElement('div');
        controls.className = 'summary-controls';
        controls.innerHTML = `
            <button type="button" data-decrease="${item.id}">-</button>
            <strong>${item.quantity}</strong>
            <button type="button" data-increase="${item.id}">+</button>
        `;

        const lineTotal = document.createElement('strong');
        lineTotal.className = 'summary-line-total';
        lineTotal.textContent = formatPrice(parsePrice(item.price) * Number(item.quantity || 0));

        node.append(image, body, remove, controls, lineTotal);
        itemsNode.appendChild(node);
    });

    document.getElementById('subtotalValue').textContent = formatPrice(subtotal);
    document.getElementById('deliveryValue').textContent = formatPrice(currentDeliveryCost);
    document.getElementById('totalValue').textContent = formatPrice(total);

    renderHints(subtotal, items.length);
}

function renderHints(subtotal, itemCount) {
    const freeNode = document.getElementById('freeDeliveryHint');
    const minimumNode = document.getElementById('minimumDeliveryHint');
    const isDelivery = deliveryType() === 'delivery';

    if (!orderingOpen) {
        freeNode.hidden = true;
        minimumNode.textContent = orderingUnavailableMessage;
        minimumNode.hidden = false;
        return;
    }

    if (itemCount === 0 || freeDeliveryFrom <= 0 || !isDelivery) {
        freeNode.hidden = true;
    } else {
        const missing = Math.max(0, freeDeliveryFrom - subtotal);
        freeNode.textContent = missing > 0
            ? copy.freeMissing.replace(':amount', formatPrice(missing))
            : copy.freeReady;
        freeNode.classList.toggle('is-ready', missing <= 0);
        freeNode.hidden = false;
    }

    if (itemCount === 0 || minimumDeliveryAmount <= 0 || !isDelivery || subtotal >= minimumDeliveryAmount) {
        minimumNode.hidden = true;
    } else {
        minimumNode.textContent = copy.minimumMissing.replace(':amount', formatPrice(minimumDeliveryAmount - subtotal));
        minimumNode.hidden = false;
    }
}

function updateConditionalFields() {
    document.getElementById('nipField').classList.toggle('is-visible', document.getElementById('invoiceToggle').checked);
    document.getElementById('addressFields').classList.toggle('is-visible', deliveryType() === 'delivery');
    document.getElementById('scheduleFields').classList.toggle('is-visible', document.querySelector('input[name="fulfillment_type"]:checked')?.value === 'scheduled');
    renderCart();
}

function setupStreetAutocomplete() {
    const input = document.querySelector('[data-street-autocomplete]');
    const list = document.getElementById('streetSuggestions');
    if (!input || !list) return;

    let timer = null;
    let controller = null;
    let activeIndex = -1;
    let streets = [];

    function closeSuggestions() {
        list.hidden = true;
        list.innerHTML = '';
        activeIndex = -1;
    }

    function renderSuggestions(items) {
        streets = items;
        activeIndex = -1;
        list.innerHTML = '';

        if (items.length === 0) {
            closeSuggestions();
            return;
        }

        items.forEach((street, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'street-suggestion';
            button.textContent = street;
            button.setAttribute('role', 'option');
            button.addEventListener('mousedown', (event) => {
                event.preventDefault();
                input.value = street;
                closeSuggestions();
            });
            list.appendChild(button);
        });

        list.hidden = false;
    }

    function setActiveSuggestion(index) {
        const buttons = [...list.querySelectorAll('.street-suggestion')];
        if (buttons.length === 0) return;

        activeIndex = (index + buttons.length) % buttons.length;
        buttons.forEach((button, buttonIndex) => {
            button.classList.toggle('is-active', buttonIndex === activeIndex);
        });
    }

    input.addEventListener('input', () => {
        const query = input.value.trim();
        window.clearTimeout(timer);

        if (query.length < 2) {
            closeSuggestions();
            return;
        }

        timer = window.setTimeout(async () => {
            if (controller) controller.abort();
            controller = new AbortController();

            try {
                const response = await fetch(`/api/torun-streets?q=${encodeURIComponent(query)}`, {
                    headers: { Accept: 'application/json' },
                    signal: controller.signal,
                });
                if (!response.ok) return;

                renderSuggestions(await response.json());
            } catch (error) {
                if (error.name !== 'AbortError') {
                    closeSuggestions();
                }
            }
        }, 260);
    });

    input.addEventListener('keydown', (event) => {
        if (list.hidden || streets.length === 0) return;

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            setActiveSuggestion(activeIndex + 1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            setActiveSuggestion(activeIndex - 1);
        } else if (event.key === 'Enter' && activeIndex >= 0) {
            event.preventDefault();
            input.value = streets[activeIndex];
            closeSuggestions();
        } else if (event.key === 'Escape') {
            closeSuggestions();
        }
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.street-field')) {
            closeSuggestions();
        }
    });
}

document.addEventListener('click', (event) => {
    const cart = readCart();
    const increase = event.target.closest('[data-increase]');
    const decrease = event.target.closest('[data-decrease]');
    const remove = event.target.closest('[data-remove]');

    if (!increase && !decrease && !remove) return;

    const id = increase?.dataset.increase || decrease?.dataset.decrease || remove?.dataset.remove;
    if (!id || !cart[id]) return;

    if (remove) {
        delete cart[id];
    } else if (increase) {
        cart[id].quantity = Number(cart[id].quantity || 0) + 1;
    } else if (decrease) {
        cart[id].quantity = Number(cart[id].quantity || 0) - 1;
        if (cart[id].quantity <= 0) delete cart[id];
    }

    writeCart(cart);
    renderCart();
});

document.querySelectorAll('input[name="delivery_type"], input[name="fulfillment_type"], #invoiceToggle').forEach((input) => {
    input.addEventListener('change', updateConditionalFields);
});

document.getElementById('checkoutForm').addEventListener('submit', (event) => {
    const items = sortedItems(readCart());
    const subtotal = cartSubtotal(items);

    if (!orderingOpen) {
        event.preventDefault();
        alert(orderingUnavailableMessage);
        return;
    }

    if (items.length === 0) {
        event.preventDefault();
        alert(copy.emptyCart);
        return;
    }

    if (deliveryType() === 'delivery' && minimumDeliveryAmount > 0 && subtotal < minimumDeliveryAmount) {
        event.preventDefault();
        alert(copy.minimumMissing.replace(':amount', formatPrice(minimumDeliveryAmount - subtotal)));
        return;
    }

    document.getElementById('cartJson').value = JSON.stringify(items.map((item) => ({
        id: item.id,
        quantity: Number(item.quantity || 0),
    })));
});

setupStreetAutocomplete();
updateConditionalFields();
