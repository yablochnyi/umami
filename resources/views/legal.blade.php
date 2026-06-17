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
@endsection
