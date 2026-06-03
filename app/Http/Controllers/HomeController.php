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
        $siteUrl = rtrim(SiteSetting::query()->where('key', 'site_url')->value('value') ?: 'https://www.umamisushifood.pl', '/');

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
            'heroVideo' => $this->mediaUrl($settings['hero_video'] ?? 'umami/UMAMI.MP4'),
            'heroPoster' => $this->mediaUrl($settings['hero_poster'] ?? 'umami/res1.png'),
            'aboutImage' => $this->mediaUrl($settings['about_image'] ?? 'umami/res8.png'),
            'mapEmbedUrl' => $settings['map_embed_url'] ?? '',
            'address' => $settings['address'] ?? '',
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
        ]);

        $categoryGroups = $categories->map(fn (MenuCategory $category) => [
            'id' => $category->id,
            'name' => $translated($category, 'name'),
            'items' => $category->items->map(fn (MenuItem $dish) => [
                'name' => $translated($dish, 'name'),
                'description' => $translated($dish, 'description', $copy['detailsFallback']),
                'category' => $translated($category, 'name'),
                'price' => $dish->price,
                'image' => $this->mediaUrl($dish->image),
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
