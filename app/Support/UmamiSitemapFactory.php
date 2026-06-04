<?php

namespace App\Support;

use App\Http\Controllers\LegalPageController;
use App\Http\Controllers\MenuPageController;
use App\Models\SiteSetting;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class UmamiSitemapFactory
{
    public function build(): Sitemap
    {
        $sitemap = Sitemap::create();

        $this->urlGroups()->each(function (array $group) use ($sitemap): void {
            foreach ($group['urls'] as $loc) {
                $url = Url::create($loc)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority((float) $group['priority']);

                foreach ($group['urls'] as $alternateLocale => $alternateUrl) {
                    $url->addAlternate($alternateUrl, $alternateLocale);
                }

                $url->addAlternate($group['urls']['pl'], 'x-default');

                $sitemap->add($url);
            }
        });

        return $sitemap;
    }

    private function urlGroups()
    {
        $siteUrl = rtrim(
            SiteSetting::query()->where('key', 'site_url')->value('value') ?: 'https://umamisushifood.pl',
            '/'
        );

        return collect([
            [
                'priority' => '1.0',
                'urls' => [
                    'pl' => $siteUrl.'/',
                    'uk' => $siteUrl.'/uk',
                    'en' => $siteUrl.'/en',
                ],
            ],
        ])->merge(
            collect(LegalPageController::pageUrls($siteUrl))
                ->map(fn (array $urls) => [
                    'priority' => '0.4',
                    'urls' => $urls,
                ])
                ->values()
        )->merge(
            collect(MenuPageController::pageUrls($siteUrl))
                ->map(fn (array $urls, string $key) => [
                    'priority' => str_starts_with($key, 'category-') ? '0.7' : '0.6',
                    'urls' => $urls,
                ])
                ->values()
        );
    }
}
