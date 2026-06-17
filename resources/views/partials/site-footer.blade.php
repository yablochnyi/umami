<footer>
    <div class="footer-links">
        @foreach($siteLayout['legalLinks'] as $link)
            <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
        @endforeach
    </div>
    © 2026 Umami Sushi & Food Toruń.
</footer>
