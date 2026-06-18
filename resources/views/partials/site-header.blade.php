<header class="topbar">
    <a class="brand" href="{{ $siteLayout['homeUrl'] }}#top" aria-label="{{ $siteLayout['settings']['siteUrl'] }}">
        <img src="{{ $siteLayout['settings']['logo'] }}" alt="Umami logo">
        <span>{{ trans('site.brand') }}</span>
    </a>

    <nav class="nav" aria-label="{{ $siteLayout['nav']['label'] }}">
        <a href="{{ $siteLayout['homeUrl'] }}#bestsellers">{{ $siteLayout['nav']['bestsellers'] }}</a>
        <a href="{{ $siteLayout['homeUrl'] }}#menu">{{ $siteLayout['nav']['menu'] }}</a>
        <a href="{{ $siteLayout['homeUrl'] }}#about">{{ $siteLayout['nav']['about'] }}</a>
        <a href="{{ $siteLayout['homeUrl'] }}#gallery">{{ $siteLayout['nav']['gallery'] }}</a>
        <a href="{{ $siteLayout['homeUrl'] }}#contact">{{ $siteLayout['nav']['contact'] }}</a>
    </nav>

    <div class="top-actions">
        @foreach($siteLayout['socialLinks'] as $link)
            <a class="social-link" href="{{ $link->url }}" target="_blank" rel="noopener" aria-label="{{ $link->label }}">
                @if($link->icon)
                    <img src="{{ $link->icon }}" alt="" width="18" height="18">
                @endif
            </a>
        @endforeach

        <nav class="language-switcher" aria-label="Language switcher">
            @foreach($siteLayout['supportedLocales'] as $lang)
                <a href="{{ $siteLayout['localizedUrls'][$lang] }}" class="{{ $siteLayout['locale'] === $lang ? 'active' : '' }}" @if($siteLayout['locale'] === $lang) aria-current="page" @endif>{{ $siteLayout['localeLabels'][$lang] }}</a>
            @endforeach
        </nav>

        @if($showCart ?? true)
            <div class="cart-popover-wrap">
                <button class="cart-button" type="button" id="cartOpen" aria-label="{{ $siteLayout['cart']['copy']['cart'] }}" aria-controls="cartPopover" aria-expanded="false">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6.4 7.2h13.1l-1.2 7.1a2 2 0 0 1-2 1.7H8.8a2 2 0 0 1-2-1.7L5.7 4.9H3.8" />
                        <circle cx="9.2" cy="19.4" r="1.1" />
                        <circle cx="16.4" cy="19.4" r="1.1" />
                    </svg>
                    <span class="cart-badge" id="cartBadge" hidden>0</span>
                </button>
                <div class="cart-popover" id="cartPopover" aria-labelledby="cartOpen" hidden>
                    <div class="cart-popover-head">
                        <h3 id="cartPopoverTitle">{{ $siteLayout['cart']['copy']['cart'] }}</h3>
                        <button class="cart-popover-close" type="button" aria-label="{{ trans('site.ui.close') }}" id="cartClose">×</button>
                    </div>
                    <div class="cart-items" id="cartItems"></div>
                    <p class="cart-empty" id="cartEmpty" hidden>{{ $siteLayout['cart']['copy']['empty'] }}</p>
                    <p class="cart-availability" id="cartAvailability" hidden></p>
                    <p class="cart-free-delivery" id="cartFreeDelivery" hidden></p>
                    <div class="cart-summary">
                        <span>{{ $siteLayout['cart']['copy']['total'] }}</span>
                        <strong id="cartTotal">0 zł</strong>
                    </div>
                    <button class="pill cart-checkout" type="button" id="cartCheckout">{{ $siteLayout['cart']['copy']['checkout'] }}</button>
                </div>
            </div>
        @endif

        <a class="pill" href="{{ $siteLayout['settings']['phoneHref'] }}">{{ $siteLayout['settings']['phone'] }}</a>
    </div>
</header>
