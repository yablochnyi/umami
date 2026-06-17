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
    @stack('head')
</head>
<body
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

    @yield('content')

    @include('partials.site-footer')

    @yield('afterFooter')

    <script src="{{ $siteLayout['assets']['js'] }}" defer></script>
    @stack('scripts')
</body>
</html>
