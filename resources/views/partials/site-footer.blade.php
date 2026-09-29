<footer>
    <div class="footer-links">
        @foreach($siteLayout['legalLinks'] as $link)
            <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
        @endforeach
        <button type="button" class="cookie-settings-link" data-cookie-settings>{{ __('legal.cookie_settings') }}</button>
    </div>
    © 2026 Umami Sushi & Food Toruń.
</footer>
