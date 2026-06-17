@extends('layouts.site', [
    'metaTitle' => $title,
    'metaDescription' => $description,
    'canonicalUrl' => $canonicalUrl,
    'localizedUrls' => $localizedUrls,
])

@push('head')
    <script type="application/ld+json">{!! $schemaJson !!}</script>
@endpush

@section('content')
    <div class="cart-notice" id="cartNotice" hidden></div>

    <main class="menu-page">
        <section class="menu-seo-head">
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
            <h1>{{ $categoryName }} Toruń</h1>
            <p>{{ $introText }}</p>
        </section>

        <section class="section menu-seo-section">
            <div class="menu-grid">
                @foreach($items as $dish)
                    <a class="dish-card menu-item" href="{{ $dish['url'] }}">
                        @if($dish['image'])
                            <img src="{{ $dish['image'] }}" alt="{{ $dish['name'] }}" loading="lazy">
                        @endif
                        <div class="dish-body">
                            <div class="dish-top">
                                <h2 class="dish-name">{{ $dish['name'] }}</h2>
                                <span class="price">{{ $dish['price'] }}</span>
                            </div>
                            <p class="dish-desc">{{ $dish['description'] }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        @if($seoText)
            <section class="section menu-copy-section">
                <div class="seo-copy">
                    <h2>{{ $categoryName }} w Umami Sushi & Food Toruń</h2>
                    <p>{{ $seoText }}</p>
                </div>
            </section>
        @endif
    </main>
@endsection
