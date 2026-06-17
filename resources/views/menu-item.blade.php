@extends('layouts.site', [
    'metaTitle' => $title,
    'metaDescription' => $description,
    'canonicalUrl' => $canonicalUrl,
    'localizedUrls' => $localizedUrls,
    'ogType' => 'article',
    'ogImage' => $ogImage,
])

@push('head')
    <script type="application/ld+json">{!! $schemaJson !!}</script>
@endpush

@section('content')
    <div class="cart-notice" id="cartNotice" hidden></div>

    <main class="menu-page">
        <article class="product-hero">
            <div class="product-media">
                @if($image)
                    <img src="{{ $image }}" alt="{{ $itemName }}">
                @endif
            </div>
            <div class="product-content">
                <nav class="breadcrumbs" aria-label="Breadcrumbs">
                    @foreach($breadcrumbs as $crumb)
                        @if(! $loop->last)
                            <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                            <span>/</span>
                        @else
                            <span>{{ $crumb['label'] }}</span>
                        @endif
                    @endforeach
                </nav>
                <h1>{{ $itemName }}</h1>
                <div class="item-meta">
                    <span class="tag">{{ $categoryName }}</span>
                    <span class="price">{{ $item->price }}</span>
                </div>
                <h2>{{ $copy['details'] }}</h2>
                <p>{{ $ingredients }}</p>
                <h2>{{ $copy['taste'] }}</h2>
                <p>{{ $marketingDescription }}</p>
                <div class="hero-actions">
                    <div class="cart-control product-cart-control" data-cart-control data-cart-id="{{ $item->id }}" data-cart-name="{{ $itemName }}" data-cart-price="{{ $item->price }}" data-cart-image="{{ $image }}">
                        <button class="cart-step decrease" type="button" data-cart-decrease aria-label="{{ trans('site.cart.decrease') }}">−</button>
                        <span class="cart-quantity" data-cart-quantity>0</span>
                        <button class="cart-add" type="button" data-cart-add aria-label="{{ trans('site.cart.add') }}">
                            <img src="/cart-svgrepo-com.svg" alt="">
                        </button>
                        <button class="cart-step increase" type="button" data-cart-increase aria-label="{{ trans('site.cart.increase') }}">+</button>
                    </div>
                    <a class="pill ghost dark" href="{{ $menuUrl }}">{{ $copy['back'] }}</a>
                </div>
            </div>
        </article>

        @if($similarItems->isNotEmpty())
            <section class="section menu-seo-section">
                <div class="section-head single">
                    <h2>{{ $copy['similar'] }}</h2>
                </div>
                <div class="best-grid">
                    @foreach($similarItems as $dish)
                        <a class="dish-card" href="{{ $dish['url'] }}">
                            @if($dish['image'])
                                <img src="{{ $dish['image'] }}" alt="{{ $dish['name'] }}" loading="lazy">
                            @endif
                            <div class="dish-body">
                                <div class="dish-top">
                                    <h3 class="dish-name">{{ $dish['name'] }}</h3>
                                    <span class="price">{{ $dish['price'] }}</span>
                                </div>
                                <p class="dish-desc">{{ $dish['description'] }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </main>
@endsection
