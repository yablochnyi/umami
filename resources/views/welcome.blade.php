<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $copy['title'] }}</title>
    <meta name="description" content="{{ $copy['metaDescription'] }}">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <link rel="canonical" href="{{ $seo['canonicalUrl'] }}">
    @foreach($seo['localizedUrls'] as $lang => $url)
        <link rel="alternate" hreflang="{{ $lang }}" href="{{ $url }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $seo['xDefaultUrl'] }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="{{ $seo['ogLocale'] }}">
    <meta property="og:site_name" content="Umami Sushi & Food">
    <meta property="og:title" content="{{ $copy['title'] }}">
    <meta property="og:description" content="{{ $copy['metaDescription'] }}">
    <meta property="og:url" content="{{ $seo['canonicalUrl'] }}">
    <meta property="og:image" content="{{ $seo['ogImage'] }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $copy['title'] }}">
    <meta name="twitter:description" content="{{ $copy['metaDescription'] }}">
    <meta name="twitter:image" content="{{ $seo['ogImage'] }}">
    <link rel="icon" href="{{ $settings['logo'] }}">
    <link rel="preload" as="image" href="{{ $settings['heroPoster'] }}" fetchpriority="high">
    <link rel="preload" as="image" href="{{ $settings['backgroundMobile'] }}" media="(max-width: 900px)">
    <link rel="stylesheet" href="/assets/umami/landing.css?v={{ filemtime(public_path('assets/umami/landing.css')) }}">
    <script src="/assets/umami/landing.js?v={{ filemtime(public_path('assets/umami/landing.js')) }}" defer></script>
    <script type="application/ld+json">{!! $restaurantSchemaJson !!}</script>
</head>
@php
    $cartCopy = [
        'pl' => ['cart' => 'Koszyk', 'add' => 'Dodaj do koszyka', 'increase' => 'Zwiększ ilość', 'decrease' => 'Zmniejsz ilość'],
        'uk' => ['cart' => 'Кошик', 'add' => 'Додати до кошика', 'increase' => 'Збільшити кількість', 'decrease' => 'Зменшити кількість'],
        'en' => ['cart' => 'Cart', 'add' => 'Add to cart', 'increase' => 'Increase quantity', 'decrease' => 'Decrease quantity'],
    ][$locale] ?? ['cart' => 'Koszyk', 'add' => 'Dodaj do koszyka', 'increase' => 'Zwiększ ilość', 'decrease' => 'Zmniejsz ilość'];
@endphp
<body
    data-background-desktop="{{ $settings['backgroundDesktop'] }}"
    data-background-mobile="{{ $settings['backgroundMobile'] }}"
    data-hero-video-desktop="{{ $settings['heroVideoDesktop'] }}"
    data-hero-video-mobile="{{ $settings['heroVideoMobile'] }}"
    data-hero-poster="{{ $settings['heroPoster'] }}"
    data-show-photo-label="{{ $copy['showPhoto'] }}"
    data-google-analytics-id="{{ $settings['googleAnalyticsId'] }}"
    data-cart-add-label="{{ $cartCopy['add'] }}"
    data-cart-increase-label="{{ $cartCopy['increase'] }}"
    data-cart-decrease-label="{{ $cartCopy['decrease'] }}"
>
    <header class="topbar">
        <a class="brand" href="{{ route('home', ['lang' => $locale]) }}#top" aria-label="Umami Sushi & Food">
            <img src="{{ $settings['logo'] }}" alt="Umami logo">
            <span>Umami Sushi & Food</span>
        </a>
        <nav class="nav" aria-label="Główna nawigacja">
            <a href="#bestsellers">{{ $copy['navBestsellers'] }}</a>
            <a href="#menu">{{ $copy['navMenu'] }}</a>
            <a href="#about">{{ $copy['navAbout'] }}</a>
            <a href="#gallery">{{ $copy['navGallery'] }}</a>
            <a href="#contact">{{ $copy['navContact'] }}</a>
        </nav>
        <div class="top-actions">
            @foreach($socialLinks as $link)
                <a class="social-link" href="{{ $link->url }}" target="_blank" rel="noopener" aria-label="{{ $link->label }}">
                    @if($link->icon)
                        <img src="{{ $link->icon }}" alt="" width="18" height="18">
                    @endif
                </a>
            @endforeach
            <nav class="language-switcher" aria-label="Language switcher">
                @foreach($supportedLocales as $lang)
                    <a href="{{ $seo['localizedUrls'][$lang] }}" class="{{ $locale === $lang ? 'active' : '' }}" @if($locale === $lang) aria-current="page" @endif>{{ $localeLabels[$lang] }}</a>
                @endforeach
            </nav>
            <button class="cart-button" type="button" aria-label="{{ $cartCopy['cart'] }}">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6.4 7.2h13.1l-1.2 7.1a2 2 0 0 1-2 1.7H8.8a2 2 0 0 1-2-1.7L5.7 4.9H3.8" />
                    <circle cx="9.2" cy="19.4" r="1.1" />
                    <circle cx="16.4" cy="19.4" r="1.1" />
                </svg>
                <span class="cart-badge" id="cartBadge" hidden>0</span>
            </button>
            <a class="pill" href="{{ $settings['phoneHref'] }}">{{ $settings['phone'] }}</a>
        </div>
    </header>

    <main id="top">
        <section class="hero">
            <video class="bg-video" autoplay muted loop playsinline poster="{{ $settings['heroPoster'] }}">
            </video>
            <div class="hero-inner">
                <img class="hero-logo" src="{{ $settings['logo'] }}" alt="Umami Sushi & Food">
                <div class="eyebrow">{{ $copy['heroEyebrow'] }}</div>
                <h1>UMAMI Sushi & Food</h1>
                <p>{{ $copy['heroText'] }}</p>
                <div class="hero-actions">
                    <a class="pill" href="#menu">{{ $copy['heroMenu'] }}</a>
                    <a class="pill ghost" href="{{ $settings['orderUrl'] }}" target="_blank" rel="noopener">{{ $copy['heroOrder'] }}</a>
                </div>
            </div>
        </section>

        <section id="bestsellers" class="section">
            <div class="section-head">
                <h2>{{ $copy['bestsellersTitle'] }}</h2>
            </div>
            <div class="best-grid">
                @foreach($bestsellers as $dish)
                    <article class="dish-card" data-modal-card data-cart-id="{{ $dish['id'] }}" data-name="{{ $dish['name'] }}" data-category="{{ $dish['category'] }}" data-price="{{ $dish['price'] }}" data-desc="{{ $dish['description'] }}" data-image="{{ $dish['image'] }}" data-url="{{ $dish['url'] }}">
                        @if($dish['image'])
                            <button class="dish-image-button" type="button" data-modal-trigger aria-label="{{ $menuDetailsLabel }}: {{ $dish['name'] }}">
                                <img src="{{ $dish['image'] }}" alt="{{ $dish['name'] }}" loading="lazy">
                            </button>
                        @endif
                        <div class="dish-body">
                            <div class="dish-top">
                                <h3 class="dish-name">{{ $dish['name'] }}</h3>
                                <span class="price">{{ $dish['price'] }}</span>
                            </div>
                            <p class="dish-desc">{{ $dish['description'] }}</p>
                            <div class="dish-actions">
                                <a class="details-button" href="{{ $dish['url'] }}">{{ $menuDetailsLabel }}</a>
                                <div class="cart-control" data-cart-control data-cart-id="{{ $dish['id'] }}" data-cart-name="{{ $dish['name'] }}" data-cart-price="{{ $dish['price'] }}" data-cart-image="{{ $dish['image'] }}">
                                    <button class="cart-step decrease" type="button" data-cart-decrease aria-label="{{ $cartCopy['decrease'] }}">−</button>
                                    <span class="cart-quantity" data-cart-quantity>0</span>
                                    <button class="cart-add" type="button" data-cart-add aria-label="{{ $cartCopy['add'] }}">
                                        <img src="/cart-svgrepo-com.svg" alt="">
                                    </button>
                                    <button class="cart-step increase" type="button" data-cart-increase aria-label="{{ $cartCopy['increase'] }}">+</button>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section id="menu" class="section">
            <div class="section-head">
                <h2>{{ $copy['menuTitle'] }}</h2>
            </div>
            <div class="menu-shell">
                <div class="category-tabs" id="categoryTabs" role="tablist" aria-label="Kategorie menu">
                    @foreach($categories as $category)
                        <button
                            type="button"
                            role="tab"
                            id="category-tab-{{ $category['id'] }}"
                            class="{{ $loop->first ? 'active' : '' }}"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                            aria-controls="category-panel-{{ $category['id'] }}"
                            data-category-tab="category-{{ $category['id'] }}"
                        >{{ $category['name'] }}</button>
                    @endforeach
                </div>
                <div>
                    @foreach($categories as $category)
                        <div
                            class="menu-grid"
                            role="tabpanel"
                            id="category-panel-{{ $category['id'] }}"
                            aria-labelledby="category-tab-{{ $category['id'] }}"
                            data-menu-panel="category-{{ $category['id'] }}"
                            @if(! $loop->first) hidden @endif
                        >
                            @foreach($category['items'] as $dish)
                                <article class="dish-card menu-item" data-modal-card data-cart-id="{{ $dish['id'] }}" data-name="{{ $dish['name'] }}" data-category="{{ $dish['category'] }}" data-price="{{ $dish['price'] }}" data-desc="{{ $dish['description'] }}" data-image="{{ $dish['image'] }}" data-url="{{ $dish['url'] }}">
                                    @if($dish['image'])
                                        <button class="dish-image-button" type="button" data-modal-trigger aria-label="{{ $menuDetailsLabel }}: {{ $dish['name'] }}">
                                            <img src="{{ $dish['image'] }}" alt="{{ $dish['name'] }}" loading="lazy">
                                        </button>
                                    @endif
                                    <div class="dish-body">
                                        <div class="dish-top">
                                            <h3 class="dish-name">{{ $dish['name'] }}</h3>
                                            <span class="price">{{ $dish['price'] }}</span>
                                        </div>
                                        <p class="dish-desc">{{ $dish['description'] }}</p>
                                        <div class="dish-actions">
                                            <a class="details-button" href="{{ $dish['url'] }}">{{ $menuDetailsLabel }}</a>
                                            <div class="cart-control" data-cart-control data-cart-id="{{ $dish['id'] }}" data-cart-name="{{ $dish['name'] }}" data-cart-price="{{ $dish['price'] }}" data-cart-image="{{ $dish['image'] }}">
                                                <button class="cart-step decrease" type="button" data-cart-decrease aria-label="{{ $cartCopy['decrease'] }}">−</button>
                                                <span class="cart-quantity" data-cart-quantity>0</span>
                                                <button class="cart-add" type="button" data-cart-add aria-label="{{ $cartCopy['add'] }}">
                                                    <img src="/cart-svgrepo-com.svg" alt="">
                                                </button>
                                                <button class="cart-step increase" type="button" data-cart-increase aria-label="{{ $cartCopy['increase'] }}">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="about" class="about-band">
            <div class="section">
                <div>
                    <div class="eyebrow">{{ $copy['aboutEyebrow'] }}</div>
                    <h2>{{ $copy['aboutTitle'] }}</h2>
                </div>
                <div>
                    <img src="{{ $settings['aboutImage'] }}" alt="Sushi w Umami" loading="lazy" decoding="async">
                    <p>{{ $copy['aboutText'] }}</p>
                </div>
            </div>
        </section>

        <section id="gallery" class="section">
            <div class="section-head">
                <h2>{{ $copy['galleryTitle'] }}</h2>
            </div>
            <div class="gallery-slider" id="gallerySlider">
                <div class="gallery-track" id="galleryTrack">
                    @foreach($galleryImages as $image)
                        <button type="button" class="gallery-slide" data-modal-card data-name="{{ $image['title'] }}" data-category="{{ $copy['galleryTitle'] }}" data-price="" data-desc="{{ $copy['galleryDesc'] }}" data-image="{{ $image['image'] }}">
                            <img src="{{ $image['image'] }}" alt="{{ $image['alt'] }}" loading="lazy">
                        </button>
                    @endforeach
                </div>
                <button class="slider-arrow prev" type="button" id="galleryPrev" aria-label="{{ $copy['prevPhoto'] }}">‹</button>
                <button class="slider-arrow next" type="button" id="galleryNext" aria-label="{{ $copy['nextPhoto'] }}">›</button>
                <div class="slider-dots" id="galleryDots" role="group" aria-label="{{ $copy['photoChoice'] }}"></div>
            </div>
        </section>

        <section id="contact" class="section">
            <div class="section-head">
                <h2>{{ $copy['contactTitle'] }}</h2>
            </div>
            <div class="info-grid">
                <div class="info-panel">
                    <h3>{{ $copy['restaurantTitle'] }}</h3>
                    <p><strong>{{ $copy['addressLabel'] }}</strong> {{ $settings['address'] }}</p>
                    <p><strong>{{ $copy['phoneLabel'] }}</strong> <a href="{{ $settings['phoneHref'] }}">{{ $settings['phone'] }}</a></p>
                    <p><strong>{{ $copy['hoursLabel'] }}</strong> <span>{{ $copy['hoursValue'] }}</span></p>
                </div>
                <div class="info-panel">
                    <h3>{{ $copy['socialTitle'] }}</h3>
                    <p>{{ $copy['socialText'] }}</p>
                    <div class="social-buttons">
                        @foreach($socialLinks as $link)
                            <a class="social-link" href="{{ $link->url }}" target="_blank" rel="noopener" aria-label="{{ $link->label }}">
                                @if($link->icon)
                                    <img src="{{ $link->icon }}" alt="" width="18" height="18">
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="info-panel">
                    <h3>{{ $copy['takeawayTitle'] }}</h3>
                    <p>{{ $copy['takeawayText'] }}</p>
                    <p><a class="pill" href="{{ $settings['orderUrl'] }}" target="_blank" rel="noopener">{{ $copy['takeawayButton'] }}</a></p>
                </div>
            </div>
            @if($settings['mapEmbedUrl'])
                <div class="map-frame">
                    <iframe src="{{ $settings['mapEmbedUrl'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Mapa Umami Sushi & Food Toruń"></iframe>
                </div>
            @endif
        </section>
    </main>

    <footer>
        <div class="footer-links">
            @foreach($legalLinks as $link)
                <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
            @endforeach
        </div>
        © 2026 Umami Sushi & Food Toruń.
    </footer>

    <section class="cookie-consent" id="cookieConsent" aria-labelledby="cookieConsentTitle" hidden>
        <div>
            <h2 id="cookieConsentTitle">{{ $cookieConsent['title'] }}</h2>
            <p>{{ $cookieConsent['text'] }}</p>
        </div>
        <div class="cookie-actions">
            <button type="button" class="cookie-button secondary" id="cookieDecline">{{ $cookieConsent['decline'] }}</button>
            <button type="button" class="cookie-button primary" id="cookieAccept">{{ $cookieConsent['accept'] }}</button>
        </div>
    </section>

    <div class="modal" id="modal" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
            <button class="close" type="button" aria-label="{{ $copy['close'] }}" id="modalClose">×</button>
            <img class="modal-image" id="modalImage" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==" alt="" hidden>
            <div class="modal-content">
                <div class="item-meta">
                    <span class="tag" id="modalCategory"></span>
                    <span class="price" id="modalPrice"></span>
                </div>
                <h3 id="modalTitle">{{ $copy['detailsFallback'] }}</h3>
                <p id="modalDescription"></p>
                <div class="modal-actions">
                    <div class="cart-control modal-cart-control" id="modalCartControl" data-cart-control hidden>
                        <button class="cart-step decrease" type="button" data-cart-decrease aria-label="{{ $cartCopy['decrease'] }}">−</button>
                        <span class="cart-quantity" data-cart-quantity>0</span>
                        <button class="cart-add" type="button" data-cart-add aria-label="{{ $cartCopy['add'] }}">
                            <img src="/cart-svgrepo-com.svg" alt="">
                        </button>
                        <button class="cart-step increase" type="button" data-cart-increase aria-label="{{ $cartCopy['increase'] }}">+</button>
                    </div>
                    <a class="pill modal-details-link" href="#" id="modalDetailsLink">{{ $menuDetailsLabel }}</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
