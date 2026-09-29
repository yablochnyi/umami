@extends('layouts.site', [
    'metaTitle' => $title.' | Umami Sushi & Food Toruń',
    'metaDescription' => $description,
    'canonicalUrl' => $canonicalUrl,
    'localizedUrls' => $localizedUrls,
    'robots' => 'index, follow',
])

@section('content')
    <div class="cart-notice" id="cartNotice" hidden></div>

    <main class="legal-page">
        <article class="legal-document">
            <a class="legal-back" href="{{ $siteLayout['homeUrl'] }}">Umami Sushi & Food</a>
            <h1>{{ $title }}</h1>
            <p class="legal-lead">{{ $description }}</p>
            <p class="legal-updated">{{ __('legal.updated_label') }} <time datetime="2026-09-29">{{ $updatedAt }}</time></p>
            <nav class="legal-contents" aria-label="{{ __('legal.contents') }}">
                @foreach($sections as $section)
                    <a href="#section-{{ $loop->iteration }}">{{ $section['heading'] }}</a>
                @endforeach
            </nav>

            @foreach($sections as $section)
                <section id="section-{{ $loop->iteration }}">
                    <h2>{{ $section['heading'] }}</h2>
                    @foreach($section['body'] as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </section>
            @endforeach
            <nav class="legal-related" aria-label="{{ __('legal.related') }}">
                @foreach($siteLayout['legalLinks'] as $link)
                    <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                @endforeach
            </nav>
        </article>
    </main>
@endsection
