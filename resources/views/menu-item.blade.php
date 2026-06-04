<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    @foreach($localizedUrls as $lang => $url)
        <link rel="alternate" hreflang="{{ $lang }}" href="{{ $url }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $localizedUrls['pl'] }}">
    <meta property="og:type" content="product">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    @if($image)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <link rel="stylesheet" href="/assets/umami/landing.css">
    <script type="application/ld+json">{!! $schemaJson !!}</script>
</head>
<body>
    <header class="topbar legal-topbar">
        <a class="brand" href="{{ $homeUrl }}" aria-label="Umami Sushi & Food">
            <img src="/storage/umami/logo.jpg" alt="Umami logo">
            <span>Umami Sushi & Food</span>
        </a>
        <div class="top-actions">
            <div class="language-switcher" aria-label="Language switcher">
                @foreach($localizedUrls as $lang => $url)
                    <a href="{{ $url }}" class="{{ $locale === $lang ? 'active' : '' }}" @if($locale === $lang) aria-current="page" @endif>{{ $localeLabels[$lang] }}</a>
                @endforeach
            </div>
            <a class="pill" href="{{ $menuUrl }}">Menu</a>
        </div>
    </header>

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
                <p>{{ $description }}</p>
                <h2>{{ $copy['taste'] }}</h2>
                <p>{{ $marketingDescription }}</p>
                <div class="hero-actions">
                    <a class="pill" href="{{ $orderUrl }}" target="_blank" rel="noopener">{{ $copy['order'] }}</a>
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

    <footer>
        <div class="footer-links">
            @foreach($legalLinks as $link)
                <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
            @endforeach
        </div>
        © 2026 Umami Sushi & Food Toruń.
    </footer>
</body>
</html>
