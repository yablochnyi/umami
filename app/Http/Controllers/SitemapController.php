<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use XMLWriter;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $siteUrl = rtrim(
            SiteSetting::query()->where('key', 'site_url')->value('value') ?: 'https://www.umamisushifood.pl',
            '/'
        );

        $urlGroups = collect([
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
        );

        $xml = new XMLWriter;
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $xml->writeAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');

        $urlGroups->each(fn (array $group) => $this->writeLocalizedGroup($xml, collect($group['urls']), $group['priority']));

        $xml->endElement();
        $xml->endDocument();

        return response($xml->outputMemory(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    private function writeLocalizedGroup(XMLWriter $xml, Collection $urls, string $priority): void
    {
        $urls->each(function (string $loc) use ($xml, $priority, $urls): void {
            $xml->startElement('url');
            $xml->writeElement('loc', $loc);

            $urls->each(function (string $alternateUrl, string $alternateLocale) use ($xml): void {
                $xml->startElement('xhtml:link');
                $xml->writeAttribute('rel', 'alternate');
                $xml->writeAttribute('hreflang', $alternateLocale);
                $xml->writeAttribute('href', $alternateUrl);
                $xml->endElement();
            });

            $xml->startElement('xhtml:link');
            $xml->writeAttribute('rel', 'alternate');
            $xml->writeAttribute('hreflang', 'x-default');
            $xml->writeAttribute('href', $urls['pl']);
            $xml->endElement();

            $xml->writeElement('changefreq', 'weekly');
            $xml->writeElement('priority', $priority);
            $xml->endElement();
        });
    }
}
