<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\SiteSetting;
use App\Support\OptimizedMedia;
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
            ->visible()
            ->with(['items' => fn ($query) => $query->visible()->orderBy('sort_order')])
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
            ->visible()
            ->firstOrFail();

        $item = MenuItem::query()
            ->where('menu_category_id', $category->id)
            ->where('slug', $itemSlug)
            ->visible()
            ->firstOrFail();

        app()->setLocale($locale);

        $categoryName = $this->translated($category, 'name');
        $name = $this->translated($item, 'name');
        $ingredients = $this->translated($item, 'description') ?: $this->fallbackDescription($locale, $name, $categoryName);
        $marketingDescription = $this->translated($item, 'marketing_description') ?: $this->itemMarketingFallback($locale, $name, $ingredients);
        $title = $this->translated($item, 'seo_title') ?: $this->itemTitle($locale, $name, $categoryName);
        $description = $this->translated($item, 'seo_description') ?: $this->itemMetaDescription($locale, $name, $categoryName, $ingredients, $item->price);
        $localizedUrls = $this->localizedUrls($siteUrl, 'item', $category, $item);
        $similarItems = MenuItem::query()
            ->where('menu_category_id', $category->id)
            ->where('id', '!=', $item->id)
            ->visible()
            ->orderByDesc('is_bestseller')
            ->orderBy('sort_order')
            ->limit(4)
            ->get()
            ->map(fn (MenuItem $similar) => $this->itemCard($similar, $category, $locale));
        $image = $this->mediaUrl($item->image);
        $breadcrumbs = $this->breadcrumbs($locale, $siteUrl, $category, $item);
        $schema = $this->itemSchema($siteUrl, $locale, $category, $item, $name, $ingredients, $image, $breadcrumbs);

        return response()->view('menu-item', [
            'locale' => $locale,
            'title' => $title,
            'description' => $description,
            'ingredients' => $ingredients,
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
            'menuUrl' => $this->homeUrl($locale).'#menu',
            'schemaJson' => json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            'copy' => $this->copy($locale),
        ]);
    }

    public static function pageUrls(string $siteUrl): array
    {
        $siteUrl = rtrim($siteUrl, '/');

        $categories = MenuCategory::query()
            ->visible()
            ->with(['items' => fn ($query) => $query->visible()])
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
            'image' => $this->mediaUrl($item->image, 'card'),
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

    private function mediaUrl(?string $path, string $profile = 'image'): string
    {
        if (blank($path)) {
            return '';
        }

        if ($optimized = app(OptimizedMedia::class)->url($path, $profile)) {
            return $optimized;
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
        $homeLabel = __('menu.home', [], $locale);
        $menuLabel = __('menu.menu', [], $locale);
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

    private function categoryTitle(string $locale, string $name): string
    {
        return __('menu.categoryTitle', ['name' => $name], $locale);
    }

    private function categoryDescription(string $locale, string $name): string
    {
        return __('menu.categoryDescription', ['name' => $name], $locale);
    }

    private function itemTitle(string $locale, string $name, string $category): string
    {
        return __('menu.itemTitle', ['name' => $name, 'category' => $category], $locale);
    }

    private function itemMetaDescription(string $locale, string $name, string $category, string $ingredients, ?string $price): string
    {
        return str(__('menu.itemMetaDescription', [
            'name' => $name,
            'ingredients' => str($ingredients)->squish()->limit(82, '')->toString(),
            'price' => filled($price) ? " {$price}." : '',
        ], $locale))->limit(160, '')->toString();
    }

    private function fallbackDescription(string $locale, string $name, string $category): string
    {
        return __('menu.fallbackDescription', ['name' => $name, 'category' => $category], $locale);
    }

    private function itemMarketingFallback(string $locale, string $name, string $description): string
    {
        return __('menu.itemMarketingFallback', ['name' => $name, 'description' => $description], $locale);
    }

    private function copy(string $locale): array
    {
        return trans('menu.copy', [], $locale);
    }
}
