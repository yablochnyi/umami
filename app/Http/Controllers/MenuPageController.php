<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class MenuPageController extends Controller
{
    private const SUPPORTED_LOCALES = ['pl', 'uk', 'en'];

    public function category(Request $request): Response
    {
        $locale = $this->localeFromRequest($request);
        $categorySlug = (string) $request->route('categorySlug');
        $siteUrl = $this->siteUrl();
        $category = MenuCategory::query()
            ->where('slug', $categorySlug)
            ->where('is_active', true)
            ->with(['items' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
            ->firstOrFail();

        app()->setLocale($locale);

        $name = $this->translated($category, 'name');
        $title = $this->categoryTitle($locale, $name);
        $introText = $this->translated($category, 'intro_text') ?: $this->categoryDescription($locale, $name);
        $seoText = $this->translated($category, 'seo_text');
        $description = str($introText)->limit(155, '')->toString();
        $localizedUrls = $this->localizedUrls($siteUrl, 'category', $category);
        $items = $category->items->map(fn (MenuItem $item) => $this->itemCard($item, $category, $locale));
        $breadcrumbs = $this->breadcrumbs($locale, $siteUrl, $category);
        $schema = $this->categorySchema($siteUrl, $locale, $category, $name, $introText, $items, $breadcrumbs);

        return response()->view('menu-category', [
            'locale' => $locale,
            'localeLabels' => $this->localeLabels(),
            'title' => $title,
            'description' => $description,
            'category' => $category,
            'categoryName' => $name,
            'introText' => $introText,
            'seoText' => $seoText,
            'breadcrumbs' => $breadcrumbs['relative'],
            'items' => $items,
            'localizedUrls' => $localizedUrls,
            'canonicalUrl' => $localizedUrls[$locale],
            'homeUrl' => $this->homeUrl($locale),
            'menuUrl' => $this->homeUrl($locale).'#menu',
            'legalLinks' => $this->legalLinks($locale),
            'schemaJson' => json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        ]);
    }

    public function item(Request $request): Response
    {
        $locale = $this->localeFromRequest($request);
        $categorySlug = (string) $request->route('categorySlug');
        $itemSlug = (string) $request->route('itemSlug');
        $siteUrl = $this->siteUrl();
        $category = MenuCategory::query()
            ->where('slug', $categorySlug)
            ->where('is_active', true)
            ->firstOrFail();

        $item = MenuItem::query()
            ->where('menu_category_id', $category->id)
            ->where('slug', $itemSlug)
            ->where('is_active', true)
            ->firstOrFail();

        app()->setLocale($locale);

        $categoryName = $this->translated($category, 'name');
        $name = $this->translated($item, 'name');
        $description = $this->translated($item, 'description') ?: $this->fallbackDescription($locale, $name, $categoryName);
        $marketingDescription = $this->translated($item, 'marketing_description') ?: $this->itemMarketingFallback($locale, $name, $description);
        $title = $this->itemTitle($locale, $name);
        $localizedUrls = $this->localizedUrls($siteUrl, 'item', $category, $item);
        $similarItems = MenuItem::query()
            ->where('menu_category_id', $category->id)
            ->where('id', '!=', $item->id)
            ->where('is_active', true)
            ->orderByDesc('is_bestseller')
            ->orderBy('sort_order')
            ->limit(4)
            ->get()
            ->map(fn (MenuItem $similar) => $this->itemCard($similar, $category, $locale));
        $image = $this->mediaUrl($item->image);
        $breadcrumbs = $this->breadcrumbs($locale, $siteUrl, $category, $item);
        $schema = $this->itemSchema($siteUrl, $locale, $category, $item, $name, $description, $image, $breadcrumbs);

        return response()->view('menu-item', [
            'locale' => $locale,
            'localeLabels' => $this->localeLabels(),
            'title' => $title,
            'description' => $description,
            'marketingDescription' => $marketingDescription,
            'categoryName' => $categoryName,
            'categoryUrl' => $this->relativePageUrl($locale, $category),
            'breadcrumbs' => $breadcrumbs['relative'],
            'itemName' => $name,
            'item' => $item,
            'image' => $image,
            'ogImage' => $this->absoluteUrl($image, $siteUrl),
            'similarItems' => $similarItems,
            'localizedUrls' => $localizedUrls,
            'canonicalUrl' => $localizedUrls[$locale],
            'homeUrl' => $this->homeUrl($locale),
            'menuUrl' => $this->homeUrl($locale).'#menu',
            'orderUrl' => SiteSetting::query()->where('key', 'order_url')->value('value') ?: 'http://umamisushifood.goorder.pl/',
            'legalLinks' => $this->legalLinks($locale),
            'schemaJson' => json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            'copy' => $this->copy($locale),
        ]);
    }

    public static function pageUrls(string $siteUrl): array
    {
        $siteUrl = rtrim($siteUrl, '/');

        $categories = MenuCategory::query()
            ->where('is_active', true)
            ->with(['items' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        $groups = [];

        foreach ($categories as $category) {
            $groups['category-'.$category->id] = collect(self::SUPPORTED_LOCALES)
                ->mapWithKeys(fn (string $locale) => [$locale => self::localizedStaticPageUrl($siteUrl, $locale, $category->slug)])
                ->all();

            foreach ($category->items as $item) {
                if (blank($item->slug)) {
                    continue;
                }

                $groups['item-'.$item->id] = collect(self::SUPPORTED_LOCALES)
                    ->mapWithKeys(fn (string $locale) => [$locale => self::localizedStaticPageUrl($siteUrl, $locale, $category->slug, $item->slug)])
                    ->all();
            }
        }

        return $groups;
    }

    private static function localizedStaticPageUrl(string $siteUrl, string $locale, string $categorySlug, ?string $itemSlug = null): string
    {
        $prefix = $locale === 'pl' ? '' : '/'.$locale;
        $path = $prefix.'/menu/'.$categorySlug;

        if ($itemSlug) {
            $path .= '/'.$itemSlug;
        }

        return rtrim($siteUrl, '/').$path;
    }

    private function localizedPageUrl(string $siteUrl, string $locale, MenuCategory $category, ?MenuItem $item = null): string
    {
        return self::localizedStaticPageUrl($siteUrl, $locale, $category->slug, $item?->slug);
    }

    private function relativePageUrl(string $locale, MenuCategory $category, ?MenuItem $item = null): string
    {
        $prefix = $locale === 'pl' ? '' : '/'.$locale;
        $path = $prefix.'/menu/'.$category->slug;

        if ($item) {
            $path .= '/'.$item->slug;
        }

        return $path;
    }

    private function localizedUrls(string $siteUrl, string $type, MenuCategory $category, ?MenuItem $item = null): array
    {
        return collect(self::SUPPORTED_LOCALES)
            ->mapWithKeys(fn (string $locale) => [$locale => $this->localizedPageUrl($siteUrl, $locale, $category, $type === 'item' ? $item : null)])
            ->all();
    }

    private function itemCard(MenuItem $item, MenuCategory $category, string $locale): array
    {
        return [
            'name' => $this->translated($item, 'name'),
            'description' => $this->translated($item, 'description') ?: $this->fallbackDescription($locale, $this->translated($item, 'name'), $this->translated($category, 'name')),
            'category' => $this->translated($category, 'name'),
            'price' => $item->price,
            'image' => $this->mediaUrl($item->image),
            'url' => $this->relativePageUrl($locale, $category, $item),
        ];
    }

    private function categorySchema(string $siteUrl, string $locale, MenuCategory $category, string $name, string $description, $items, array $breadcrumbs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                $this->breadcrumbSchema($breadcrumbs['absolute']),
                [
                    '@type' => 'MenuSection',
                    '@id' => $this->localizedPageUrl($siteUrl, $locale, $category).'#menu-section',
                    'name' => $name,
                    'description' => $description,
                    'url' => $this->localizedPageUrl($siteUrl, $locale, $category),
                    'hasMenuItem' => $items->map(fn (array $item) => [
                        '@type' => 'MenuItem',
                        'name' => $item['name'],
                        'description' => $item['description'],
                        'image' => $this->absoluteUrl($item['image'], $siteUrl),
                        'url' => $this->absoluteUrl($item['url'], $siteUrl),
                        'offers' => [
                            '@type' => 'Offer',
                            'priceCurrency' => 'PLN',
                            'price' => $this->priceValue($item['price']),
                        ],
                    ])->values()->all(),
                ],
            ],
        ];
    }

    private function itemSchema(string $siteUrl, string $locale, MenuCategory $category, MenuItem $item, string $name, string $description, string $image, array $breadcrumbs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                $this->breadcrumbSchema($breadcrumbs['absolute']),
                [
                    '@type' => 'MenuItem',
                    '@id' => $this->localizedPageUrl($siteUrl, $locale, $category, $item).'#menu-item',
                    'name' => $name,
                    'description' => $description,
                    'image' => $this->absoluteUrl($image, $siteUrl),
                    'url' => $this->localizedPageUrl($siteUrl, $locale, $category, $item),
                    'menuAddOn' => $this->translated($category, 'name'),
                    'offers' => [
                        '@type' => 'Offer',
                        'priceCurrency' => 'PLN',
                        'price' => $this->priceValue($item->price),
                        'availability' => 'https://schema.org/InStock',
                    ],
                ],
                [
                    '@type' => 'Product',
                    '@id' => $this->localizedPageUrl($siteUrl, $locale, $category, $item).'#product',
                    'name' => $name,
                    'description' => $description,
                    'image' => $this->absoluteUrl($image, $siteUrl),
                    'brand' => [
                        '@type' => 'Brand',
                        'name' => 'Umami Sushi & Food Toruń',
                    ],
                    'offers' => [
                        '@type' => 'Offer',
                        'priceCurrency' => 'PLN',
                        'price' => $this->priceValue($item->price),
                        'availability' => 'https://schema.org/InStock',
                    ],
                ],
            ],
        ];
    }

    private function localeFromRequest(Request $request): string
    {
        $locale = $request->route('locale');

        return in_array($locale, self::SUPPORTED_LOCALES, true) ? $locale : 'pl';
    }

    private function siteUrl(): string
    {
        return rtrim(SiteSetting::query()->where('key', 'site_url')->value('value') ?: 'https://umamisushifood.pl', '/');
    }

    private function homeUrl(string $locale): string
    {
        return $locale === 'pl' ? '/' : '/'.$locale;
    }

    private function translated($model, string $field): string
    {
        if (blank($model->{$field})) {
            return '';
        }

        return (string) ($model->getTranslation($field, app()->getLocale(), false) ?: $model->getTranslation($field, 'pl', false) ?: '');
    }

    private function mediaUrl(?string $path): string
    {
        if (blank($path)) {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/storage/') || str_starts_with($path, '/assets/')) {
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

    private function priceValue(?string $price): string
    {
        return str_replace(',', '.', preg_replace('/[^0-9,\.]/', '', (string) $price)) ?: '0';
    }

    private function breadcrumbs(string $locale, string $siteUrl, MenuCategory $category, ?MenuItem $item = null): array
    {
        $homeLabel = ['pl' => 'Strona główna', 'uk' => 'Головна', 'en' => 'Home'][$locale];
        $menuLabel = ['pl' => 'Menu', 'uk' => 'Меню', 'en' => 'Menu'][$locale];
        $homeUrl = $this->homeUrl($locale);
        $categoryUrl = $this->relativePageUrl($locale, $category);

        $relative = [
            ['label' => $homeLabel, 'url' => $homeUrl],
            ['label' => $menuLabel, 'url' => $homeUrl.'#menu'],
            ['label' => $this->translated($category, 'name'), 'url' => $categoryUrl],
        ];

        if ($item) {
            $relative[] = ['label' => $this->translated($item, 'name'), 'url' => $this->relativePageUrl($locale, $category, $item)];
        }

        return [
            'relative' => $relative,
            'absolute' => collect($relative)
                ->map(fn (array $crumb) => [
                    'label' => $crumb['label'],
                    'url' => str_contains($crumb['url'], '#')
                        ? $this->absoluteUrl(str($crumb['url'])->before('#')->toString(), $siteUrl).'#menu'
                        : $this->absoluteUrl($crumb['url'], $siteUrl),
                ])
                ->all(),
        ];
    }

    private function breadcrumbSchema(array $breadcrumbs): array
    {
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($breadcrumbs)
                ->map(fn (array $crumb, int $index) => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $crumb['label'],
                    'item' => $crumb['url'],
                ])
                ->all(),
        ];
    }

    private function localeLabels(): array
    {
        return ['pl' => 'PL', 'uk' => 'UA', 'en' => 'EN'];
    }

    private function categoryTitle(string $locale, string $name): string
    {
        return [
            'pl' => "{$name} Toruń | Menu Umami Sushi & Food",
            'uk' => "{$name} Торунь | Меню Umami Sushi & Food",
            'en' => "{$name} Toruń | Umami Sushi & Food menu",
        ][$locale];
    }

    private function categoryDescription(string $locale, string $name): string
    {
        return [
            'pl' => "Sprawdź {$name} w Umami Sushi & Food Toruń. Zobacz skład, zdjęcia, ceny i zamów sushi lub dania azjatyckie online.",
            'uk' => "Перегляньте {$name} в Umami Sushi & Food Торунь: склад, фото, ціни та онлайн-замовлення суші й азійських страв.",
            'en' => "Explore {$name} at Umami Sushi & Food Toruń: ingredients, photos, prices and online ordering for sushi and Asian dishes.",
        ][$locale];
    }

    private function itemTitle(string $locale, string $name): string
    {
        return [
            'pl' => "{$name} Toruń | Skład, cena i zamówienie",
            'uk' => "{$name} Торунь | Склад, ціна та замовлення",
            'en' => "{$name} Toruń | Ingredients, price and order",
        ][$locale];
    }

    private function fallbackDescription(string $locale, string $name, string $category): string
    {
        return [
            'pl' => "{$name} z kategorii {$category} w Umami Sushi & Food Toruń. Sprawdź cenę, zdjęcie i szczegóły dania.",
            'uk' => "{$name} з категорії {$category} в Umami Sushi & Food Торунь. Перегляньте ціну, фото та деталі страви.",
            'en' => "{$name} from {$category} at Umami Sushi & Food Toruń. Check price, photo and dish details.",
        ][$locale];
    }

    private function itemMarketingFallback(string $locale, string $name, string $description): string
    {
        return [
            'pl' => "{$name} to świeżo przygotowana propozycja Umami Sushi & Food w Toruniu. {$description} Danie dobrze sprawdza się na lunch, kolację albo zamówienie na wynos z odbiorem przy ul. Gen. Andersa 72.",
            'uk' => "{$name} — свіжа позиція Umami Sushi & Food у Торуні. {$description} Страва добре підходить для обіду, вечері або замовлення з собою з самовивозом на ul. Gen. Andersa 72.",
            'en' => "{$name} is a freshly prepared choice at Umami Sushi & Food in Toruń. {$description} It works well for lunch, dinner or takeaway pickup at Gen. Andersa 72.",
        ][$locale];
    }

    private function copy(string $locale): array
    {
        return [
            'pl' => ['back' => 'Wróć do menu', 'order' => 'Zamów online', 'similar' => 'Podobne dania', 'details' => 'Skład', 'taste' => 'Dlaczego warto spróbować'],
            'uk' => ['back' => 'Назад до меню', 'order' => 'Замовити онлайн', 'similar' => 'Схожі страви', 'details' => 'Склад', 'taste' => 'Чому варто спробувати'],
            'en' => ['back' => 'Back to menu', 'order' => 'Order online', 'similar' => 'Similar dishes', 'details' => 'Ingredients', 'taste' => 'Why it is worth trying'],
        ][$locale];
    }

    private function legalLinks(string $locale): array
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
            ->map(fn (array $link) => ['label' => $link['label'], 'url' => $link['path']])
            ->all();
    }
}
