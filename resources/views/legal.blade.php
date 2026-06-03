<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} | Umami Sushi & Food Toruń</title>
    <meta name="description" content="{{ $description }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    @foreach($localizedUrls as $lang => $url)
        <link rel="alternate" hreflang="{{ $lang }}" href="{{ $url }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $localizedUrls['pl'] }}">
    <link rel="stylesheet" href="/assets/umami/landing.css">
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
                    <a href="{{ $url }}" class="{{ $locale === $lang ? 'active' : '' }}" aria-pressed="{{ $locale === $lang ? 'true' : 'false' }}">{{ $localeLabels[$lang] }}</a>
                @endforeach
            </div>
            <a class="pill" href="{{ $homeUrl }}#menu">Menu</a>
        </div>
    </header>

    <main class="legal-page">
        <article class="legal-document">
            <a class="legal-back" href="{{ $homeUrl }}">Umami Sushi & Food</a>
            <h1>{{ $title }}</h1>
            <p class="legal-lead">{{ $description }}</p>
            <p class="legal-updated">Ostatnia aktualizacja: {{ $updatedAt }}</p>

            @foreach($sections as $section)
                <section>
                    <h2>{{ $section['heading'] }}</h2>
                    @foreach($section['body'] as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </section>
            @endforeach
        </article>
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
