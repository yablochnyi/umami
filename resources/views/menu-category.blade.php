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
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
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
