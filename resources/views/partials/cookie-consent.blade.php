<section class="cookie-consent" id="cookieConsent" aria-labelledby="cookieConsentTitle" role="region" hidden>
    <div>
        <h2 id="cookieConsentTitle">{{ __('site.cookie.title') }}</h2>
        <p>{{ __('site.cookie.text') }}</p>
        <a href="{{ $siteLayout['legalLinks'][1]['url'] }}">{{ __('site.legal.cookies') }}</a>
    </div>
    <div class="cookie-actions">
        <button type="button" class="cookie-button secondary" id="cookieDecline">{{ __('site.cookie.decline') }}</button>
        <button type="button" class="cookie-button primary" id="cookieAccept">{{ __('site.cookie.accept') }}</button>
    </div>
</section>
