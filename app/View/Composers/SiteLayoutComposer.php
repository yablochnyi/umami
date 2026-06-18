<?php

namespace App\View\Composers;

use App\Models\SiteSetting;
use App\Models\SocialLink;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SiteLayoutComposer
{
    private const SUPPORTED_LOCALES = ['pl', 'uk', 'en'];

    private static array $commonCache = [];

    public function compose(View $view): void
    {
        $data = $view->getData();
        $locale = $this->locale($data['locale'] ?? app()->getLocale());

        app()->setLocale($locale);

        if (! isset(self::$commonCache[$locale])) {
            self::$commonCache[$locale] = $this->commonLayoutData($locale);
        }

        $view->with('siteLayout', array_replace(self::$commonCache[$locale], [
            'localizedUrls' => $data['localizedUrls'] ?? $this->homeLocalizedUrls(),
            'assets' => [
                'css' => $this->asset($data['layoutCss'] ?? '/assets/umami/landing.css'),
                'js' => $this->asset($data['layoutJs'] ?? '/assets/umami/landing.js'),
            ],
        ]));
    }

    private function commonLayoutData(string $locale): array
    {
        $settings = SiteSetting::query()
            ->orderBy('sort_order')
            ->pluck('value', 'key')
            ->all();

        $openingTime = $this->normalizeTime($settings['opening_time'] ?? '12:00');
        $closingTime = $this->normalizeTime($settings['closing_time'] ?? '20:30');
        $cartCopy = trans('site.cart');

        return [
            'locale' => $locale,
            'supportedLocales' => self::SUPPORTED_LOCALES,
            'localeLabels' => ['pl' => 'PL', 'uk' => 'UA', 'en' => 'EN'],
            'homeUrl' => $this->homeUrl($locale),
            'settings' => [
                'logo' => $this->mediaUrl($settings['logo_image'] ?? 'umami/logo.jpg'),
                'phone' => $settings['phone'] ?? '+48 513 233 722',
                'phoneHref' => $settings['phone_href'] ?? 'tel:+48513233722',
                'siteUrl' => rtrim($settings['site_url'] ?? 'https://umamisushifood.pl', '/'),
            ],
            'nav' => trans('site.nav'),
            'cart' => [
                'copy' => $cartCopy,
                'isOrderingOpen' => $this->isOrderingOpen($openingTime, $closingTime),
                'orderingUnavailableMessage' => strtr($cartCopy['ordering_unavailable'], [
                    ':day' => $this->availabilityDayLabel($openingTime, $closingTime, $cartCopy),
                    ':open' => $openingTime,
                    ':close' => $closingTime,
                ]),
                'freeDeliveryFrom' => $this->money($settings['free_delivery_from'] ?? '0'),
                'checkoutUrl' => $locale === 'pl' ? route('checkout') : route('checkout.localized', ['locale' => $locale]),
            ],
            'socialLinks' => $this->socialLinks(),
            'legalLinks' => $this->legalLinks($locale),
        ];
    }

    private function locale(?string $locale): string
    {
        return in_array($locale, self::SUPPORTED_LOCALES, true) ? $locale : 'pl';
    }

    private function homeUrl(string $locale): string
    {
        return $locale === 'pl' ? '/' : '/'.$locale;
    }

    private function homeLocalizedUrls(): array
    {
        return [
            'pl' => '/',
            'uk' => '/uk',
            'en' => '/en',
        ];
    }

    private function socialLinks()
    {
        return SocialLink::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->each(function (SocialLink $link): void {
                $link->icon = $this->mediaUrl($link->icon);
            });
    }

    private function legalLinks(string $locale): array
    {
        return [
            'pl' => [
                ['label' => trans('site.legal.privacy'), 'url' => '/polityka-prywatnosci'],
                ['label' => trans('site.legal.cookies'), 'url' => '/polityka-plikow-cookie'],
                ['label' => trans('site.legal.terms'), 'url' => '/regulamin'],
            ],
            'uk' => [
                ['label' => trans('site.legal.privacy'), 'url' => '/uk/polityka-konfidentsiynosti'],
                ['label' => trans('site.legal.cookies'), 'url' => '/uk/polityka-cookie'],
                ['label' => trans('site.legal.terms'), 'url' => '/uk/pravila-korystuvannya'],
            ],
            'en' => [
                ['label' => trans('site.legal.privacy'), 'url' => '/en/privacy-policy'],
                ['label' => trans('site.legal.cookies'), 'url' => '/en/cookie-policy'],
                ['label' => trans('site.legal.terms'), 'url' => '/en/terms'],
            ],
        ][$locale];
    }

    private function normalizeTime(?string $time): string
    {
        if (preg_match('/^(\d{1,2}):(\d{2})/', (string) $time, $matches)) {
            return sprintf('%02d:%02d', min(23, (int) $matches[1]), min(59, (int) $matches[2]));
        }

        return '12:00';
    }

    private function isOrderingOpen(string $openingTime, string $closingTime): bool
    {
        $now = CarbonImmutable::now('Europe/Warsaw');
        $open = CarbonImmutable::createFromFormat('Y-m-d H:i', $now->format('Y-m-d').' '.$openingTime, 'Europe/Warsaw');
        $close = CarbonImmutable::createFromFormat('Y-m-d H:i', $now->format('Y-m-d').' '.$closingTime, 'Europe/Warsaw');

        return $open && $close && $now->betweenIncluded($open, $close);
    }

    private function availabilityDayLabel(string $openingTime, string $closingTime, array $copy): string
    {
        $now = CarbonImmutable::now('Europe/Warsaw');
        $close = CarbonImmutable::createFromFormat('Y-m-d H:i', $now->format('Y-m-d').' '.$closingTime, 'Europe/Warsaw');

        return $now->gt($close)
            ? ($copy['day_tomorrow'] ?? 'jutro')
            : ($copy['day_today'] ?? 'dzisiaj');
    }

    private function money(?string $amount): string
    {
        $normalized = str_replace(',', '.', (string) $amount);
        $value = max(0, (float) preg_replace('/[^0-9.]/', '', $normalized));

        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    private function mediaUrl(?string $path): string
    {
        if (blank($path)) {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        if (str_starts_with($path, 'storage/')) {
            return '/'.$path;
        }

        $optimizedPath = preg_replace('/\.(jpe?g|png)$/i', '.webp', $path);
        if ($optimizedPath !== $path && Storage::disk('public')->exists($optimizedPath)) {
            return Storage::disk('public')->url($optimizedPath);
        }

        return Storage::disk('public')->url($path);
    }

    private function asset(string $path): string
    {
        $absolutePath = public_path(ltrim($path, '/'));

        return file_exists($absolutePath) ? $path.'?v='.filemtime($absolutePath) : $path;
    }
}
