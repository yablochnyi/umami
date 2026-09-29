<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;

class LegalPageController extends Controller
{
    private const SUPPORTED_LOCALES = ['pl', 'uk', 'en'];

    private const SLUGS = [
        'privacy' => ['pl' => 'polityka-prywatnosci', 'uk' => 'polityka-konfidentsiynosti', 'en' => 'privacy-policy'],
        'cookies' => ['pl' => 'polityka-plikow-cookie', 'uk' => 'polityka-cookie', 'en' => 'cookie-policy'],
        'terms' => ['pl' => 'regulamin', 'uk' => 'pravila-korystuvannya', 'en' => 'terms'],
    ];

    public function __invoke(Request $request)
    {
        $locale = $request->route('locale') ?? 'pl';
        abort_unless(in_array($locale, self::SUPPORTED_LOCALES, true), 404);
        $pageKey = collect(self::SLUGS)->search(fn (array $slugs) => $slugs[$locale] === $request->route('slug'));
        abort_if($pageKey === false, 404);

        app()->setLocale($locale);
        $siteUrl = SiteSetting::query()->where('key', 'site_url')->value('value') ?: 'https://umamisushifood.pl';
        $localizedUrls = self::pageUrls($siteUrl)[$pageKey];
        $content = trans('legal.'.$pageKey, [], $locale);

        return view('legal', [
            ...$content,
            'locale' => $locale,
            'updatedAt' => trans('legal.updated', [], $locale),
            'localizedUrls' => $localizedUrls,
            'canonicalUrl' => $localizedUrls[$locale],
        ]);
    }

    public static function pageUrls(string $siteUrl): array
    {
        return collect(self::SLUGS)->map(fn (array $slugs) => collect($slugs)
            ->map(fn (string $slug, string $locale) => rtrim($siteUrl, '/').($locale === 'pl' ? '' : '/'.$locale).'/'.$slug)
            ->all())->all();
    }
}
