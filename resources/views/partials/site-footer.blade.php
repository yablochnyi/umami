<footer>
    <div class="footer-links">
        @foreach($siteLayout['legalLinks'] as $link)
            <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
        @endforeach
        <button type="button" class="cookie-settings-link" data-cookie-settings>{{ __('legal.cookie_settings') }}</button>
    </div>
    © 2026 Umami Sushi & Food Toruń.
    <p class="vendra-credit">{{ __('legal.developed_by') }} <a href="https://getvendra.app/{{ in_array(app()->getLocale(), ['uk', 'pl', 'en'], true) ? app()->getLocale() : 'pl' }}?utm_source=umami&amp;utm_medium=referral&amp;utm_campaign=project_credit" target="_blank" rel="noopener">Vendra ↗</a></p>
</footer>
