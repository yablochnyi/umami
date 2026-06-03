<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Response;
use XMLWriter;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $siteUrl = rtrim(
            SiteSetting::query()->where('key', 'site_url')->value('value') ?: 'https://www.umamisushifood.pl',
            '/'
        );

        $urls = [
            'pl' => ['loc' => $siteUrl.'/', 'priority' => '1.0'],
            'uk' => ['loc' => $siteUrl.'/uk', 'priority' => '0.8'],
            'en' => ['loc' => $siteUrl.'/en', 'priority' => '0.8'],
        ];

        $xml = new XMLWriter;
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $xml->writeAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');

        foreach ($urls as $locale => $url) {
            $xml->startElement('url');
            $xml->writeElement('loc', $url['loc']);

            foreach ($urls as $alternateLocale => $alternateUrl) {
                $xml->startElement('xhtml:link');
                $xml->writeAttribute('rel', 'alternate');
                $xml->writeAttribute('hreflang', $alternateLocale);
                $xml->writeAttribute('href', $alternateUrl['loc']);
                $xml->endElement();
            }

            $xml->startElement('xhtml:link');
            $xml->writeAttribute('rel', 'alternate');
            $xml->writeAttribute('hreflang', 'x-default');
            $xml->writeAttribute('href', $urls['pl']['loc']);
            $xml->endElement();

            $xml->writeElement('changefreq', 'weekly');
            $xml->writeElement('priority', $url['priority']);
            $xml->endElement();
        }

        $xml->endElement();
        $xml->endDocument();

        return response($xml->outputMemory(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
