<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @hasSection('csrf')
        <meta name="csrf-token" content="{{ csrf_token() }}">
    @endif
    <title>{{ $metaTitle }}</title>
    @isset($metaDescription)
        <meta name="description" content="{{ $metaDescription }}">
    @endisset
    <meta name="robots" content="{{ $robots ?? 'index, follow, max-image-preview:large' }}">
    @isset($canonicalUrl)
        <link rel="canonical" href="{{ $canonicalUrl }}">
    @endisset
    @foreach($siteLayout['localizedUrls'] as $lang => $url)
        <link rel="alternate" hreflang="{{ $lang }}" href="{{ $url }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $siteLayout['localizedUrls']['pl'] }}">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    @isset($ogLocale)
        <meta property="og:locale" content="{{ $ogLocale }}">
    @endisset
    <meta property="og:site_name" content="Umami Sushi & Food">
    <meta property="og:title" content="{{ $metaTitle }}">
    @isset($metaDescription)
        <meta property="og:description" content="{{ $metaDescription }}">
    @endisset
    @isset($canonicalUrl)
        <meta property="og:url" content="{{ $canonicalUrl }}">
    @endisset
    @isset($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endisset
    <meta name="twitter:title" content="{{ $metaTitle }}">
    @isset($metaDescription)
        <meta name="twitter:description" content="{{ $metaDescription }}">
    @endisset
    <link rel="icon" href="{{ $siteLayout['settings']['logo'] }}">
    @stack('preload')
    <link rel="stylesheet" href="{{ $siteLayout['assets']['css'] }}">
    <link rel="stylesheet" href="/assets/umami/privacy.css?v={{ filemtime(public_path('assets/umami/privacy.css')) }}">
    @if($halloweenEnabled)
        <link rel="stylesheet" href="/assets/umami/halloween/halloween.css?v={{ filemtime(public_path('assets/umami/halloween/halloween.css')) }}">
    @endif
    @stack('head')
    @if($newyearEnabled)
        <link rel="stylesheet" href="/assets/umami/newyear/newyear.css?v={{ filemtime(public_path('assets/umami/newyear/newyear.css')) }}">
    @endif
    @if($womensDayEnabled)
        <link rel="stylesheet" href="/assets/umami/womens-day/womens-day.css?v={{ filemtime(public_path('assets/umami/womens-day/womens-day.css')) }}">
    @endif
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-QRW6REEHD7"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-QRW6REEHD7');
    </script>
</head>
<body
    @if($halloweenEnabled) data-halloween="true" @endif
    data-google-analytics-id="{{ ($privatePage ?? false) ? '' : $siteLayout['settings']['googleAnalyticsId'] }}"
    @if($showCart ?? true)
        data-ordering-open="{{ $siteLayout['cart']['isOrderingOpen'] ? '1' : '0' }}"
        data-ordering-unavailable-message="{{ $siteLayout['cart']['orderingUnavailableMessage'] }}"
        data-free-delivery-from="{{ $siteLayout['cart']['freeDeliveryFrom'] }}"
        data-checkout-url="{{ $siteLayout['cart']['checkoutUrl'] }}"
        data-cart-add-label="{{ $siteLayout['cart']['copy']['add'] }}"
        data-cart-increase-label="{{ $siteLayout['cart']['copy']['increase'] }}"
        data-cart-decrease-label="{{ $siteLayout['cart']['copy']['decrease'] }}"
        data-cart-remove-label="{{ $siteLayout['cart']['copy']['remove'] }}"
        data-cart-empty-label="{{ $siteLayout['cart']['copy']['empty'] }}"
        data-cart-free-delivery-missing="{{ $siteLayout['cart']['copy']['free_delivery_missing'] }}"
        data-cart-free-delivery-ready="{{ $siteLayout['cart']['copy']['free_delivery_ready'] }}"
    @endif
    @foreach(($bodyData ?? []) as $name => $value)
        data-{{ $name }}="{{ $value }}"
    @endforeach
>
    @include('partials.site-header', ['showCart' => $showCart ?? true])

    <a id="activeOrderLink" class="active-order-link" href="#" hidden data-copy="{{ json_encode(__('tracking', [], $locale)) }}">{{ __('tracking.return', [], $locale) }}</a>

    @yield('content')

    @include('partials.site-footer')
    @include('partials.cookie-consent')

    @if($halloweenEnabled)
        @include('partials.halloween')
        <script src="/assets/umami/halloween/halloween.js?v={{ filemtime(public_path('assets/umami/halloween/halloween.js')) }}" defer></script>
    @endif

    @yield('afterFooter')
    @if($newyearEnabled)
        @include('partials.newyear')
        <script src="/assets/umami/newyear/newyear.js?v={{ filemtime(public_path('assets/umami/newyear/newyear.js')) }}" defer></script>
    @endif
    @if($womensDayEnabled)
        @include('partials.womens-day')
        <script src="/assets/umami/womens-day/womens-day.js?v={{ filemtime(public_path('assets/umami/womens-day/womens-day.js')) }}" defer></script>
    @endif

    <script id="menuAvailabilityData" type="application/json">{!! \Illuminate\Support\Js::encode($menuAvailability) !!}</script>
    <script src="/assets/umami/menu-availability.js?v={{ filemtime(public_path('assets/umami/menu-availability.js')) }}" defer></script>
    <script src="/assets/umami/privacy.js?v={{ filemtime(public_path('assets/umami/privacy.js')) }}" defer></script>
    <script src="{{ $siteLayout['assets']['js'] }}" defer></script>
    <script src="/assets/umami/active-order.js" defer></script>
    @stack('scripts')
    @unless($privatePage ?? false)
        <!-- Asystent Cloud Widget begin -->
        <script async src="https://widget.wenetasystent.ai/?code=rBAad0rd"></script>
        <!-- Asystent Cloud Widget end -->
    @endunless
</body>
</html>
