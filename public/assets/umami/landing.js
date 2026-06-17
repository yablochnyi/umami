const desktopBackground = document.body.dataset.backgroundDesktop;
const mobileBackground = document.body.dataset.backgroundMobile;
const heroVideoDesktop = document.body.dataset.heroVideoDesktop;
const heroVideoMobile = document.body.dataset.heroVideoMobile;
const heroPoster = document.body.dataset.heroPoster;
const googleAnalyticsId = document.body.dataset.googleAnalyticsId;
const isOrderingOpen = document.body.dataset.orderingOpen === '1';
const orderingUnavailableMessage = document.body.dataset.orderingUnavailableMessage || '';
const freeDeliveryFrom = Number.parseFloat(document.body.dataset.freeDeliveryFrom || '0') || 0;
const freeDeliveryMissingTemplate = document.body.dataset.cartFreeDeliveryMissing || '';
const freeDeliveryReadyText = document.body.dataset.cartFreeDeliveryReady || '';
const cookieConsentKey = 'umami_cookie_consent';
const cartStorageKey = 'umami_cart';
let cart = readCart();
let isCartModalOpen = false;
const cartPopoverNode = document.getElementById('cartPopover');
const cartPopoverOriginalParent = cartPopoverNode?.parentNode || null;
const cartPopoverOriginalNext = cartPopoverNode?.nextSibling || null;
const cartMobileQuery = window.matchMedia('(max-width: 620px)');

function getStoredCookieConsent() {
    try {
        return window.localStorage.getItem(cookieConsentKey);
    } catch (error) {
        const cookieValue = document.cookie
            .split('; ')
            .find((item) => item.startsWith(cookieConsentKey + '='))
            ?.split('=')[1];

        return cookieValue ? decodeURIComponent(cookieValue) : null;
    }
}

function storeCookieConsent(value) {
    try {
        window.localStorage.setItem(cookieConsentKey, value);
    } catch (error) {
        document.cookie = cookieConsentKey + '=' + encodeURIComponent(value) + '; path=/; max-age=31536000; SameSite=Lax';
    }
}

function loadGoogleAnalytics() {
    if (!/^G-[A-Z0-9]+$/i.test(googleAnalyticsId || '') || window.umamiAnalyticsLoaded) return;

    window.umamiAnalyticsLoaded = true;
    window.dataLayer = window.dataLayer || [];
    window.gtag = function gtag() {
        window.dataLayer.push(arguments);
    };

    window.gtag('js', new Date());
    window.gtag('config', googleAnalyticsId);

    const googleAnalyticsScript = document.createElement('script');
    googleAnalyticsScript.async = true;
    googleAnalyticsScript.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(googleAnalyticsId);
    document.head.appendChild(googleAnalyticsScript);
}

function setCookieConsent(value) {
    storeCookieConsent(value);
    const consentBanner = document.getElementById('cookieConsent');
    if (consentBanner) consentBanner.hidden = true;
    if (value === 'accepted') loadGoogleAnalytics();
}

const savedCookieConsent = getStoredCookieConsent();

if (savedCookieConsent === 'accepted') {
    loadGoogleAnalytics();
} else if (!savedCookieConsent) {
    const consentBanner = document.getElementById('cookieConsent');
    if (consentBanner) consentBanner.hidden = false;
}

document.getElementById('cookieAccept')?.addEventListener('click', () => setCookieConsent('accepted'));
document.getElementById('cookieDecline')?.addEventListener('click', () => setCookieConsent('declined'));

function readCart() {
    try {
        const storedCart = JSON.parse(window.localStorage.getItem(cartStorageKey) || '{}');
        return storedCart && typeof storedCart === 'object' ? storedCart : {};
    } catch (error) {
        return {};
    }
}

function writeCart() {
    try {
        window.localStorage.setItem(cartStorageKey, JSON.stringify(cart));
    } catch (error) {}
}

function cartQuantity(id) {
    return Math.max(0, Number(cart[id]?.quantity || 0));
}

let cartNoticeTimeout = null;

function showCartNotice(message) {
    const notice = document.getElementById('cartNotice');
    if (!notice || !message) return;

    notice.textContent = message;
    notice.hidden = false;
    window.clearTimeout(cartNoticeTimeout);
    cartNoticeTimeout = window.setTimeout(() => {
        notice.hidden = true;
    }, 3200);
}

function applyOrderingState() {
    document.querySelectorAll('[data-cart-add], [data-cart-increase]').forEach((button) => {
        button.classList.toggle('is-unavailable', !isOrderingOpen);
        button.setAttribute('aria-disabled', isOrderingOpen ? 'false' : 'true');
        if (orderingUnavailableMessage) {
            button.setAttribute('title', isOrderingOpen ? (button.getAttribute('aria-label') || '') : orderingUnavailableMessage);
        }
    });
}

function parsePriceAmount(price) {
    const normalized = String(price || '')
        .replace(/\s/g, '')
        .replace(',', '.')
        .replace(/[^0-9.]/g, '');

    return Number.parseFloat(normalized) || 0;
}

function formatPrice(amount) {
    const rounded = Math.round((Number(amount) || 0) * 100) / 100;

    return Number.isInteger(rounded)
        ? rounded + ' zł'
        : rounded.toFixed(2).replace('.', ',') + ' zł';
}

function freeDeliveryText(total) {
    if (freeDeliveryFrom <= 0) return '';

    const missing = Math.max(0, freeDeliveryFrom - total);

    if (missing <= 0) {
        return freeDeliveryReadyText;
    }

    return freeDeliveryMissingTemplate.replace(':amount', formatPrice(missing));
}

function setCartItem(control, quantity) {
    const id = control?.dataset.cartId;
    if (!id) return;

    const nextQuantity = Math.max(0, Number(quantity || 0));
    if (nextQuantity > 0) {
        cart[id] = {
            id,
            name: control.dataset.cartName || '',
            price: control.dataset.cartPrice || '',
            image: control.dataset.cartImage || '',
            quantity: nextQuantity,
        };
    } else {
        delete cart[id];
    }

    writeCart();
    updateCartUi();
}

function updateCartUi() {
    const totalQuantity = Object.values(cart).reduce((total, item) => total + Math.max(0, Number(item.quantity || 0)), 0);
    const cartBadge = document.getElementById('cartBadge');

    if (cartBadge) {
        cartBadge.textContent = String(totalQuantity);
        cartBadge.hidden = totalQuantity === 0;
    }

    document.querySelectorAll('[data-cart-control]').forEach((control) => {
        const id = control.dataset.cartId;
        const quantity = id ? cartQuantity(id) : 0;
        const hasItem = quantity > 0;
        const quantityNode = control.querySelector('[data-cart-quantity]');
        const addButton = control.querySelector('[data-cart-add]');
        const decreaseButton = control.querySelector('[data-cart-decrease]');
        const increaseButton = control.querySelector('[data-cart-increase]');

        control.classList.toggle('has-item', hasItem);
        if (quantityNode) quantityNode.textContent = String(quantity);
        if (addButton) addButton.hidden = hasItem;
        if (quantityNode) quantityNode.hidden = !hasItem;
        if (decreaseButton) decreaseButton.hidden = !hasItem;
        if (increaseButton) increaseButton.hidden = !hasItem;
    });

    if (isCartModalOpen) renderCartModal();
    applyOrderingState();
}

function sortedCartItems() {
    return Object.values(cart)
        .filter((item) => Math.max(0, Number(item.quantity || 0)) > 0)
        .sort((first, second) => String(first.name || '').localeCompare(String(second.name || ''), document.documentElement.lang || 'pl'));
}

function renderCartModal() {
    const cartItemsNode = document.getElementById('cartItems');
    const cartEmptyNode = document.getElementById('cartEmpty');
    const cartFreeDeliveryNode = document.getElementById('cartFreeDelivery');
    const cartTotalNode = document.getElementById('cartTotal');
    const cartCheckout = document.getElementById('cartCheckout');
    if (!cartItemsNode || !cartEmptyNode || !cartTotalNode) return;

    const items = sortedCartItems();
    const total = items.reduce((sum, item) => sum + parsePriceAmount(item.price) * Number(item.quantity || 0), 0);

    cartItemsNode.innerHTML = '';
    cartEmptyNode.hidden = items.length > 0;
    if (cartCheckout) cartCheckout.disabled = items.length === 0;

    if (cartFreeDeliveryNode) {
        const text = items.length > 0 ? freeDeliveryText(total) : '';
        cartFreeDeliveryNode.textContent = text;
        cartFreeDeliveryNode.hidden = !text;
        cartFreeDeliveryNode.classList.toggle('is-ready', total >= freeDeliveryFrom && freeDeliveryFrom > 0);
    }

    items.forEach((item) => {
        const itemNode = document.createElement('article');
        itemNode.className = 'cart-row';

        const imageNode = document.createElement('img');
        imageNode.className = 'cart-row-image';
        imageNode.src = item.image || '';
        imageNode.alt = item.name || '';
        imageNode.hidden = !item.image;

        const textNode = document.createElement('div');
        textNode.className = 'cart-row-text';

        const nameNode = document.createElement('h4');
        nameNode.textContent = item.name || '';

        const priceNode = document.createElement('p');
        priceNode.textContent = item.price || '';

        textNode.append(nameNode, priceNode);

        const controlNode = document.createElement('div');
        controlNode.className = 'cart-control has-item';
        controlNode.dataset.cartControl = '';
        controlNode.dataset.cartId = item.id;
        controlNode.dataset.cartName = item.name || '';
        controlNode.dataset.cartPrice = item.price || '';
        controlNode.dataset.cartImage = item.image || '';

        const decreaseButton = document.createElement('button');
        decreaseButton.className = 'cart-step decrease';
        decreaseButton.type = 'button';
        decreaseButton.dataset.cartDecrease = '';
        decreaseButton.setAttribute('aria-label', document.body.dataset.cartDecreaseLabel || 'Decrease quantity');
        decreaseButton.textContent = '-';

        const quantityNode = document.createElement('span');
        quantityNode.className = 'cart-quantity';
        quantityNode.dataset.cartQuantity = '';
        quantityNode.textContent = String(item.quantity || 0);

        const increaseButton = document.createElement('button');
        increaseButton.className = 'cart-step increase';
        increaseButton.type = 'button';
        increaseButton.dataset.cartIncrease = '';
        increaseButton.setAttribute('aria-label', document.body.dataset.cartIncreaseLabel || 'Increase quantity');
        increaseButton.textContent = '+';

        controlNode.append(decreaseButton, quantityNode, increaseButton);

        const lineTotalNode = document.createElement('strong');
        lineTotalNode.className = 'cart-row-total';
        lineTotalNode.textContent = formatPrice(parsePriceAmount(item.price) * Number(item.quantity || 0));

        const removeButton = document.createElement('button');
        removeButton.className = 'cart-remove';
        removeButton.type = 'button';
        removeButton.dataset.cartRemove = item.id;
        removeButton.setAttribute('aria-label', document.body.dataset.cartRemoveLabel || 'Remove from cart');
        removeButton.textContent = '×';

        itemNode.append(imageNode, textNode, controlNode, lineTotalNode, removeButton);
        cartItemsNode.appendChild(itemNode);
    });

    cartTotalNode.textContent = formatPrice(total);
}

function placeCartPopover() {
    const cartPopover = document.getElementById('cartPopover');
    if (!cartPopover || !cartPopoverOriginalParent) return;

    if (cartMobileQuery.matches) {
        if (cartPopover.parentNode !== document.body) {
            document.body.appendChild(cartPopover);
        }
    } else if (cartPopover.parentNode !== cartPopoverOriginalParent) {
        cartPopoverOriginalParent.insertBefore(cartPopover, cartPopoverOriginalNext);
    }
}

function openCartModal() {
    const cartPopover = document.getElementById('cartPopover');
    const cartOpen = document.getElementById('cartOpen');
    if (!cartPopover) return;

    isCartModalOpen = true;
    placeCartPopover();
    renderCartModal();
    cartPopover.hidden = false;
    document.body.classList.add('cart-open');
    cartOpen?.setAttribute('aria-expanded', 'true');
}

function closeCartModal() {
    const cartPopover = document.getElementById('cartPopover');
    const cartOpen = document.getElementById('cartOpen');
    if (!cartPopover) return;

    isCartModalOpen = false;
    cartPopover.hidden = true;
    document.body.classList.remove('cart-open');
    placeCartPopover();
    cartOpen?.setAttribute('aria-expanded', 'false');
}

document.addEventListener('click', (event) => {
    const cartButton = event.target.closest('[data-cart-add], [data-cart-increase], [data-cart-decrease]');
    if (!cartButton) return;

    const control = cartButton.closest('[data-cart-control]');
    if (!control) return;

    event.preventDefault();
    event.stopPropagation();

    if (!isOrderingOpen && cartButton.matches('[data-cart-add], [data-cart-increase]')) {
        showCartNotice(orderingUnavailableMessage);
        return;
    }

    const currentQuantity = cartQuantity(control.dataset.cartId);
    if (cartButton.matches('[data-cart-decrease]')) {
        setCartItem(control, currentQuantity - 1);
    } else {
        setCartItem(control, currentQuantity + 1);
    }
});

document.addEventListener('click', (event) => {
    const removeButton = event.target.closest('[data-cart-remove]');
    if (!removeButton) return;

    event.preventDefault();
    event.stopPropagation();
    delete cart[removeButton.dataset.cartRemove];
    writeCart();
    updateCartUi();
});

document.getElementById('cartOpen')?.addEventListener('click', () => {
    const cartPopover = document.getElementById('cartPopover');
    if (cartPopover && !cartPopover.hidden) {
        closeCartModal();
    } else {
        openCartModal();
    }
});
document.getElementById('cartClose')?.addEventListener('click', closeCartModal);
document.addEventListener('click', (event) => {
    const cartWrap = event.target.closest('.cart-popover-wrap, .cart-popover');
    const cartPopover = document.getElementById('cartPopover');

    if (event.target.closest('[data-cart-add], [data-cart-increase], [data-cart-decrease], [data-cart-remove]')) {
        return;
    }

    if (cartPopover && !cartPopover.hidden && !cartWrap) {
        closeCartModal();
    }
});

cartMobileQuery.addEventListener?.('change', () => {
    if (isCartModalOpen) {
        placeCartPopover();
    }
});

updateCartUi();

if (desktopBackground) {
    document.body.style.setProperty('--umami-bg-desktop', `url('${desktopBackground}')`);
}

if (mobileBackground) {
    document.body.style.setProperty('--umami-bg-mobile', `url('${mobileBackground}')`);
}

if (heroPoster) {
    document.body.style.setProperty('--umami-hero-poster', `url('${heroPoster}')`);
}

const heroVideo = document.querySelector('.bg-video');
if (heroVideo) {
    const hero = heroVideo.closest('.hero');
    const mobileVideoQuery = window.matchMedia('(max-width: 900px)');

    heroVideo.muted = true;
    heroVideo.playsInline = true;
    heroVideo.setAttribute('muted', '');
    heroVideo.setAttribute('playsinline', '');
    heroVideo.setAttribute('webkit-playsinline', '');

    const markHeroVideoReady = () => {
        if (heroVideo.readyState >= 2) {
            hero?.classList.add('video-ready');
        }
    };

    const startHeroVideo = () => {
        const playPromise = heroVideo.play();
        if (playPromise && typeof playPromise.catch === 'function') {
            playPromise.catch(() => {});
        }
    };

    const selectHeroVideoSource = () => {
        const selectedSource = mobileVideoQuery.matches
            ? (heroVideoMobile || heroVideoDesktop)
            : (heroVideoDesktop || heroVideoMobile);

        if (!selectedSource || heroVideo.dataset.currentSource === selectedSource) return;

        heroVideo.dataset.currentSource = selectedSource;
        hero?.classList.remove('video-ready');
        heroVideo.pause();
        heroVideo.src = selectedSource;
        heroVideo.load();
        startHeroVideo();
    };

    heroVideo.addEventListener('loadeddata', markHeroVideoReady);
    heroVideo.addEventListener('playing', markHeroVideoReady);
    heroVideo.addEventListener('error', () => hero?.classList.remove('video-ready'));

    selectHeroVideoSource();
    markHeroVideoReady();
    startHeroVideo();
    mobileVideoQuery.addEventListener('change', selectHeroVideoSource);
    window.addEventListener('pageshow', startHeroVideo, { once: true });
    document.addEventListener('touchstart', startHeroVideo, { once: true, passive: true });
}

const modal = document.getElementById('modal');
const modalImage = document.getElementById('modalImage');
const modalTitle = document.getElementById('modalTitle');
const modalCategory = document.getElementById('modalCategory');
const modalPrice = document.getElementById('modalPrice');
const modalDescription = document.getElementById('modalDescription');
const modalDetailsLink = document.getElementById('modalDetailsLink');
const modalCartControl = document.getElementById('modalCartControl');

function openModalCard(card) {
    modalTitle.textContent = card.dataset.name || '';
    modalCategory.textContent = card.dataset.category || '';
    modalPrice.textContent = card.dataset.price || '';
    modalDescription.textContent = card.dataset.desc || '';

    if (modalDetailsLink) {
        if (card.dataset.url) {
            modalDetailsLink.href = card.dataset.url;
            modalDetailsLink.hidden = false;
        } else {
            modalDetailsLink.hidden = true;
            modalDetailsLink.removeAttribute('href');
        }
    }

    if (card.dataset.image) {
        modalImage.src = card.dataset.image;
        modalImage.alt = card.dataset.name || '';
        modalImage.hidden = false;
    } else {
        modalImage.removeAttribute('src');
        modalImage.alt = '';
        modalImage.hidden = true;
    }

    if (modalCartControl) {
        if (card.dataset.cartId) {
            modalCartControl.dataset.cartId = card.dataset.cartId;
            modalCartControl.dataset.cartName = card.dataset.name || '';
            modalCartControl.dataset.cartPrice = card.dataset.price || '';
            modalCartControl.dataset.cartImage = card.dataset.image || '';
            modalCartControl.hidden = false;
        } else {
            modalCartControl.hidden = true;
            delete modalCartControl.dataset.cartId;
            delete modalCartControl.dataset.cartName;
            delete modalCartControl.dataset.cartPrice;
            delete modalCartControl.dataset.cartImage;
        }
    }

    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    updateCartUi();
}

document.querySelectorAll('[data-modal-card]').forEach((card) => {
    card.addEventListener('click', (event) => {
        if (card.tagName === 'A') {
            event.preventDefault();
        }

        if (card.classList.contains('dish-card') && !event.target.closest('[data-modal-trigger]')) {
            return;
        }

        openModalCard(card);
    });
    card.addEventListener('keydown', (event) => {
        if (card.classList.contains('dish-card')) {
            return;
        }

        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            openModalCard(card);
        }
    });
});

function closeModal() {
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
}

document.getElementById('modalClose').addEventListener('click', closeModal);
modal.addEventListener('click', (event) => {
    if (event.target.id === 'modal') closeModal();
});
document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;

    closeModal();
    closeCartModal();
});

document.querySelectorAll('[data-category-tab]').forEach((tab) => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('[data-category-tab]').forEach((button) => {
            button.classList.remove('active');
            button.setAttribute('aria-selected', 'false');
        });
        document.querySelectorAll('[data-menu-panel]').forEach((panel) => panel.hidden = true);
        tab.classList.add('active');
        tab.setAttribute('aria-selected', 'true');
        const panel = document.querySelector('[data-menu-panel="' + tab.dataset.categoryTab + '"]');
        if (panel) panel.hidden = false;
    });
});

const galleryTrack = document.getElementById('galleryTrack');
const gallerySlides = document.querySelectorAll('.gallery-slide');
const galleryDots = document.getElementById('galleryDots');
let activeSlide = 0;

gallerySlides.forEach((slide, index) => {
    const dot = document.createElement('button');
    dot.type = 'button';
    dot.setAttribute('aria-label', (document.body.dataset.showPhotoLabel || 'Show photo') + ' ' + (index + 1));
    dot.addEventListener('click', (event) => {
        event.stopPropagation();
        setSlide(index);
    });
    galleryDots.appendChild(dot);
});

function setSlide(index) {
    if (!gallerySlides.length) return;
    activeSlide = (index + gallerySlides.length) % gallerySlides.length;
    galleryTrack.style.transform = 'translateX(-' + (activeSlide * 100) + '%)';
    galleryDots.querySelectorAll('button').forEach((dot, dotIndex) => dot.classList.toggle('active', dotIndex === activeSlide));
}

document.getElementById('galleryPrev').addEventListener('click', (event) => {
    event.stopPropagation();
    setSlide(activeSlide - 1);
});
document.getElementById('galleryNext').addEventListener('click', (event) => {
    event.stopPropagation();
    setSlide(activeSlide + 1);
});
setSlide(0);
