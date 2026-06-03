const desktopBackground = document.body.dataset.backgroundDesktop;
const mobileBackground = document.body.dataset.backgroundMobile;
const googleAnalyticsId = document.body.dataset.googleAnalyticsId;

if (/^G-[A-Z0-9]+$/i.test(googleAnalyticsId || '')) {
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

if (desktopBackground) {
    document.body.style.setProperty('--umami-bg-desktop', `url('${desktopBackground}')`);
}

if (mobileBackground) {
    document.body.style.setProperty('--umami-bg-mobile', `url('${mobileBackground}')`);
}

const modal = document.getElementById('modal');
const modalImage = document.getElementById('modalImage');
const modalTitle = document.getElementById('modalTitle');
const modalCategory = document.getElementById('modalCategory');
const modalPrice = document.getElementById('modalPrice');
const modalDescription = document.getElementById('modalDescription');

document.querySelectorAll('[data-modal-card]').forEach((card) => {
    card.addEventListener('click', () => {
        modalTitle.textContent = card.dataset.name || '';
        modalCategory.textContent = card.dataset.category || '';
        modalPrice.textContent = card.dataset.price || '';
        modalDescription.textContent = card.dataset.desc || '';

        if (card.dataset.image) {
            modalImage.src = card.dataset.image;
            modalImage.alt = card.dataset.name || '';
            modalImage.hidden = false;
        } else {
            modalImage.removeAttribute('src');
            modalImage.alt = '';
            modalImage.hidden = true;
        }

        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
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
    if (event.key === 'Escape') closeModal();
});

document.querySelectorAll('[data-category-tab]').forEach((tab) => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('[data-category-tab]').forEach((button) => button.classList.remove('active'));
        document.querySelectorAll('[data-menu-panel]').forEach((panel) => panel.hidden = true);
        tab.classList.add('active');
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
