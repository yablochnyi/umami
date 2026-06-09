<?php

namespace App\Http\Controllers;

use App\Models\GalleryImage;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\SiteSetting;
use App\Models\SiteText;
use App\Models\SocialLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, ?string $locale = null)
    {
        $supportedLocales = ['pl', 'uk', 'en'];
        $siteUrl = rtrim(SiteSetting::query()->where('key', 'site_url')->value('value') ?: 'https://umamisushifood.pl', '/');

        if (in_array($request->query('lang'), $supportedLocales, true)) {
            return redirect()->to($this->localizedUrl($siteUrl, $request->query('lang')), 301);
        }

        $locale = in_array($locale, $supportedLocales, true) ? $locale : 'pl';

        app()->setLocale($locale);

        $texts = SiteText::query()
            ->orderBy('sort_order')
            ->get()
            ->keyBy('key');

        $settings = SiteSetting::query()
            ->orderBy('sort_order')
            ->pluck('value', 'key')
            ->all();

        $categories = MenuCategory::query()
            ->where('is_active', true)
            ->with(['items' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        $bestsellers = MenuItem::query()
            ->with('category')
            ->where('is_active', true)
            ->where('is_bestseller', true)
            ->orderBy('sort_order')
            ->get();

        $galleryImages = GalleryImage::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $socialLinks = SocialLink::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $text = fn (string $key, ?string $fallback = null): string => (string) (
            $texts->get($key)?->getTranslation('value', $locale, false)
            ?? $texts->get($key)?->getTranslation('value', 'pl', false)
            ?? $fallback
            ?? $key
        );

        $translated = fn ($model, string $field, string $fallback = ''): string => (string) (
            $model->getTranslation($field, $locale, false)
            ?: $model->getTranslation($field, 'pl', false)
            ?: $fallback
        );

        $copyKeys = [
            'title',
            'metaDescription',
            'navBestsellers',
            'navMenu',
            'navAbout',
            'navGallery',
            'navContact',
            'heroEyebrow',
            'heroText',
            'heroMenu',
            'heroOrder',
            'bestsellersTitle',
            'menuTitle',
            'aboutEyebrow',
            'aboutTitle',
            'aboutText',
            'galleryTitle',
            'contactTitle',
            'restaurantTitle',
            'addressLabel',
            'phoneLabel',
            'hoursLabel',
            'hoursValue',
            'socialTitle',
            'socialText',
            'takeawayTitle',
            'takeawayText',
            'takeawayButton',
            'detailsFallback',
            'galleryDesc',
            'showPhoto',
            'prevPhoto',
            'nextPhoto',
            'photoChoice',
            'close',
        ];

        $copy = collect($copyKeys)
            ->mapWithKeys(fn (string $key) => [$key => $text($key)])
            ->all();

        $viewSettings = [
            'siteUrl' => rtrim($settings['site_url'] ?? $siteUrl, '/'),
            'logo' => $this->mediaUrl($settings['logo_image'] ?? 'umami/logo.jpg'),
            'backgroundDesktop' => $this->mediaUrl($settings['background_desktop'] ?? 'umami/tlo3.png'),
            'backgroundMobile' => $this->mediaUrl($settings['background_mobile'] ?? 'umami/tlo4.png'),
            'phone' => $settings['phone'] ?? '+48 513 233 722',
            'phoneHref' => $settings['phone_href'] ?? 'tel:+48513233722',
            'orderUrl' => $settings['order_url'] ?? 'http://umamisushifood.goorder.pl/',
            'heroVideoDesktop' => $this->mediaUrl($settings['hero_video_desktop'] ?? $settings['hero_video'] ?? 'umami/UMAMI.MP4'),
            'heroVideoMobile' => $this->mediaUrl($settings['hero_video_mobile'] ?? $settings['hero_video'] ?? 'umami/UMAMI.MP4'),
            'heroPoster' => $this->mediaUrl($settings['hero_poster'] ?? 'umami/res1.png'),
            'aboutImage' => $this->mediaUrl($settings['about_image'] ?? 'umami/res8.png'),
            'mapEmbedUrl' => $settings['map_embed_url'] ?? '',
            'address' => $settings['address'] ?? '',
            'googleAnalyticsId' => $settings['google_analytics_id'] ?? '',
        ];

        $localizedUrls = collect($supportedLocales)
            ->mapWithKeys(fn (string $lang) => [$lang => $this->localizedUrl($viewSettings['siteUrl'], $lang)])
            ->all();

        $absoluteLogoUrl = $this->absoluteUrl($viewSettings['logo'], $viewSettings['siteUrl']);
        $canonicalUrl = $localizedUrls[$locale];

        $restaurantSchema = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Restaurant',
                    '@id' => $viewSettings['siteUrl'].'/#restaurant',
                    'name' => 'Umami Sushi & Food Toruń',
                    'alternateName' => 'Umami Sushi Toruń',
                    'description' => $copy['metaDescription'],
                    'image' => $absoluteLogoUrl,
                    'logo' => $absoluteLogoUrl,
                    'url' => $viewSettings['siteUrl'].'/',
                    'telephone' => preg_replace('/\s+/', '', $viewSettings['phone']),
                    'priceRange' => '$$',
                    'servesCuisine' => ['Sushi', 'Japanese cuisine', 'Asian cuisine', 'Ramen', 'Udon'],
                    'address' => [
                        '@type' => 'PostalAddress',
                        'streetAddress' => 'ul. Gen. Andersa 72',
                        'addressLocality' => 'Toruń',
                        'postalCode' => '87-100',
                        'addressCountry' => 'PL',
                    ],
                    'geo' => [
                        '@type' => 'GeoCoordinates',
                        'latitude' => 52.9905789,
                        'longitude' => 18.6111919,
                    ],
                    'areaServed' => [
                        '@type' => 'City',
                        'name' => 'Toruń',
                    ],
                    'hasMenu' => $viewSettings['siteUrl'].'/#menu',
                    'sameAs' => $socialLinks->pluck('url')->filter()->values()->all(),
                    'openingHoursSpecification' => [
                        [
                            '@type' => 'OpeningHoursSpecification',
                            'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                            'opens' => '12:00',
                            'closes' => '21:00',
                        ],
                    ],
                    'potentialAction' => [
                        '@type' => 'OrderAction',
                        'target' => [
                            '@type' => 'EntryPoint',
                            'urlTemplate' => $viewSettings['orderUrl'],
                            'inLanguage' => $locale,
                            'actionPlatform' => [
                                'https://schema.org/DesktopWebPlatform',
                                'https://schema.org/MobileWebPlatform',
                            ],
                        ],
                    ],
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => $viewSettings['siteUrl'].'/#website',
                    'name' => 'Umami Sushi & Food Toruń',
                    'url' => $viewSettings['siteUrl'].'/',
                    'description' => $copy['metaDescription'],
                    'inLanguage' => $locale,
                    'publisher' => [
                        '@id' => $viewSettings['siteUrl'].'/#restaurant',
                    ],
                ],
            ],
        ];

        $localeLabels = ['pl' => 'PL', 'uk' => 'UA', 'en' => 'EN'];
        $ogLocales = ['pl' => 'pl_PL', 'uk' => 'uk_UA', 'en' => 'en_US'];

        $bestsellerCards = $bestsellers->map(fn (MenuItem $dish) => [
            'name' => $translated($dish, 'name'),
            'description' => $translated($dish, 'description', $copy['detailsFallback']),
            'category' => $dish->category ? $translated($dish->category, 'name') : '',
            'price' => $dish->price,
            'image' => $this->mediaUrl($dish->image),
            'url' => $dish->category ? $this->menuItemUrl($viewSettings['siteUrl'], $locale, $dish->category, $dish) : '',
        ]);

        $categoryGroups = $categories->map(fn (MenuCategory $category) => [
            'id' => $category->id,
            'name' => $translated($category, 'name'),
            'url' => $this->menuCategoryUrl($viewSettings['siteUrl'], $locale, $category),
            'items' => $category->items->map(fn (MenuItem $dish) => [
                'name' => $translated($dish, 'name'),
                'description' => $translated($dish, 'description', $copy['detailsFallback']),
                'category' => $translated($category, 'name'),
                'price' => $dish->price,
                'image' => $this->mediaUrl($dish->image),
                'url' => $this->menuItemUrl($viewSettings['siteUrl'], $locale, $category, $dish),
            ]),
        ]);

        $gallery = $galleryImages->map(fn (GalleryImage $image) => [
            'title' => $translated($image, 'title'),
            'alt' => $translated($image, 'alt', $translated($image, 'title')),
            'image' => $this->mediaUrl($image->image),
        ]);

        $socialLinks->each(function (SocialLink $link): void {
            $link->icon = $this->mediaUrl($link->icon);
        });

        return view('welcome', [
            'locale' => $locale,
            'supportedLocales' => $supportedLocales,
            'localeLabels' => $localeLabels,
            'copy' => $copy,
            'settings' => $viewSettings,
            'categories' => $categoryGroups,
            'bestsellers' => $bestsellerCards,
            'galleryImages' => $gallery,
            'socialLinks' => $socialLinks,
            'cookieConsent' => $this->cookieConsentCopy($locale),
            'legalLinks' => $this->legalLinks($viewSettings['siteUrl'], $locale),
            'menuDetailsLabel' => $this->menuDetailsLabel($locale),
            'seo' => [
                'canonicalUrl' => $canonicalUrl,
                'localizedUrls' => $localizedUrls,
                'xDefaultUrl' => $localizedUrls['pl'],
                'ogLocale' => $ogLocales[$locale],
                'ogImage' => $absoluteLogoUrl,
            ],
            'restaurantSchemaJson' => json_encode($restaurantSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        ]);
    }

    private function localizedUrl(string $siteUrl, string $locale): string
    {
        return $locale === 'pl'
            ? $siteUrl.'/'
            : $siteUrl.'/'.$locale;
    }

    /**
     * Cookie consent copy is intentionally kept outside the content seeder so the banner
     * stays available even before optional site content is imported.
     */
    private function cookieConsentCopy(string $locale): array
    {
        $copy = [
            'pl' => [
                'title' => 'Prywatność i cookies',
                'text' => 'Dbamy o komfort korzystania ze strony. Niezbędne pliki cookie pomagają jej działać poprawnie, a za Twoją zgodą możemy korzystać z analityki, aby lepiej rozumieć zainteresowanie naszym menu.',
                'accept' => 'Zgadzam się',
                'decline' => 'Tylko niezbędne',
            ],
            'uk' => [
                'title' => 'Приватність і cookies',
                'text' => 'Ми дбаємо про зручність користування сайтом. Необхідні cookie допомагають йому працювати правильно, а за вашою згодою ми можемо використовувати аналітику, щоб краще розуміти інтерес до нашого меню.',
                'accept' => 'Погоджуюся',
                'decline' => 'Лише необхідні',
            ],
            'en' => [
                'title' => 'Privacy and cookies',
                'text' => 'We care about a smooth website experience. Essential cookies help the site work properly, and with your consent we may use analytics to better understand interest in our menu.',
                'accept' => 'I agree',
                'decline' => 'Essential only',
            ],
        ];

        return $copy[$locale] ?? $copy['pl'];
    }

    private function legalLinks(string $siteUrl, string $locale): array
    {
        $links = [
            'pl' => [
                ['label' => 'Polityka prywatności', 'path' => '/polityka-prywatnosci'],
                ['label' => 'Polityka plików cookie', 'path' => '/polityka-plikow-cookie'],
                ['label' => 'Regulamin', 'path' => '/regulamin'],
            ],
            'uk' => [
                ['label' => 'Політика конфіденційності', 'path' => '/uk/polityka-konfidentsiynosti'],
                ['label' => 'Політика cookie', 'path' => '/uk/polityka-cookie'],
                ['label' => 'Правила користування', 'path' => '/uk/pravila-korystuvannya'],
            ],
            'en' => [
                ['label' => 'Privacy policy', 'path' => '/en/privacy-policy'],
                ['label' => 'Cookie policy', 'path' => '/en/cookie-policy'],
                ['label' => 'Terms', 'path' => '/en/terms'],
            ],
        ];

        return collect($links[$locale] ?? $links['pl'])
            ->map(fn (array $link) => [
                'label' => $link['label'],
                'url' => $link['path'],
            ])
            ->all();
    }

    private function menuCategoryUrl(string $siteUrl, string $locale, MenuCategory $category): string
    {
        $prefix = $locale === 'pl' ? '' : '/'.$locale;

        return $prefix.'/menu/'.$category->slug;
    }

    private function menuItemUrl(string $siteUrl, string $locale, MenuCategory $category, MenuItem $item): string
    {
        return $this->menuCategoryUrl($siteUrl, $locale, $category).'/'.$item->slug;
    }

    private function menuDetailsLabel(string $locale): string
    {
        return [
            'pl' => 'Zobacz szczegóły',
            'uk' => 'Детальніше',
            'en' => 'View details',
        ][$locale] ?? 'Zobacz szczegóły';
    }

    private function mediaUrl(?string $path): string
    {
        if (blank($path)) {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/storage/') || str_starts_with($path, '/assets/')) {
            return $path;
        }

        $optimizedPath = preg_replace('/\.(jpe?g|png)$/i', '.webp', $path);
        if ($optimizedPath !== $path && Storage::disk('public')->exists($optimizedPath)) {
            return Storage::disk('public')->url($optimizedPath);
        }

        return Storage::disk('public')->url($path);
    }

    private function absoluteUrl(string $path, string $siteUrl): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return rtrim($siteUrl, '/').'/'.ltrim($path, '/');
    }
}
