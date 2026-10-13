<?php

namespace Tests\Feature;

use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class PublicMarkupTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_data_and_accessible_regions_have_valid_html(): void
    {
        $this->seed();

        foreach (['', '/uk', '/en'] as $prefix) {
            foreach (['/', '/menu/rameny', '/menu/rameny/chashu-ramen', '/koszyk'] as $path) {
                $html = $this->get($prefix.$path)->assertOk()->getContent();
                // Check the raw source: DOM parsers silently discard duplicate attributes.
                $this->assertSame(1, substr_count($html, 'data-free-delivery-from='));
                $xpath = $this->xpath($html);
                $body = $xpath->query('//body')->item(0);
                $this->assertTrue(is_numeric($body->getAttribute('data-free-delivery-from')));
                $panel = $xpath->query('//*[@id="cartPopover"]')->item(0);
                if ($path !== '/koszyk') {
                    $this->assertNotNull($panel);
                    $this->assertSame('region', $panel->getAttribute('role'));
                    $this->assertSame('cartPopoverTitle', $panel->getAttribute('aria-labelledby'));
                    $this->assertNotEmpty($xpath->query('//*[@id="cartPopoverTitle"]')->item(0)->textContent);
                }
                $cookies = $xpath->query('//section[@id="cookieConsent"]')->item(0);
                $this->assertSame('cookieConsentTitle', $cookies->getAttribute('aria-labelledby'));
                $this->assertFalse($cookies->hasAttribute('role'));
            }
        }
    }

    public function test_public_templates_use_external_styles_and_preserve_the_order_link(): void
    {
        $this->seed();

        foreach (['', '/uk', '/en'] as $prefix) {
            foreach (['/', '/menu/rameny', '/menu/rameny/chashu-ramen', '/koszyk'] as $path) {
                $html = $this->get($prefix.$path)->assertOk()->getContent();
                $xpath = $this->xpath($html);
                $this->assertSame(0, $xpath->query('//*[@style] | //style')->length);
                $link = $xpath->query('//a[@id="activeOrderLink"]')->item(0);
                $this->assertNotNull($link);
                $this->assertSame('active-order-link', $link->getAttribute('class'));
                $this->assertTrue($link->hasAttribute('hidden'));
                $this->assertArrayHasKey('states', json_decode($link->getAttribute('data-copy'), true, flags: JSON_THROW_ON_ERROR));
            }
        }
    }

    public function test_homepage_json_ld_describes_the_restaurant_in_each_locale(): void
    {
        $this->seed();

        foreach (['pl' => '/', 'uk' => '/uk', 'en' => '/en'] as $locale => $url) {
            $graph = $this->graph($url);
            $restaurant = $graph->get('Restaurant');
            $website = $graph->get('WebSite');
            $this->assertSame('Umami Sushi & Food Toruń', $restaurant['name']);
            $this->assertSame('PostalAddress', $restaurant['address']['@type']);
            $this->assertSame('PL', $restaurant['address']['addressCountry']);
            $this->assertSame('+48513233722', $restaurant['telephone']);
            $this->assertSame($restaurant['@id'], $website['publisher']['@id']);
            $this->assertSame($locale, $website['inLanguage']);
            $this->assertSame($locale, $restaurant['potentialAction']['target']['inLanguage']);
            $this->assertStringStartsWith('https://', $restaurant['hasMenu']);
        }
    }

    public function test_menu_json_ld_has_categories_prices_and_breadcrumbs_without_false_add_ons(): void
    {
        $this->seed();

        foreach (['', '/uk', '/en'] as $prefix) {
            $category = $this->graph($prefix.'/menu/rameny');
            $page = $this->graph($prefix.'/menu/rameny/chashu-ramen');
            $item = $page->get('MenuItem');
            $this->assertArrayNotHasKey('menuAddOn', $item);
            $this->assertSame('Offer', $item['offers']['@type']);
            $this->assertSame('PLN', $item['offers']['priceCurrency']);
            $this->assertTrue(is_numeric($item['offers']['price']));
            $this->assertGreaterThan(0, (float) $item['offers']['price']);
            $this->assertStringEndsWith($prefix.'/menu/rameny/chashu-ramen', $item['url']);
            $members = collect($category->get('MenuSection')['hasMenuItem']);
            $this->assertTrue($members->contains('url', $item['url']));
            foreach ([$category, $page] as $graph) {
                $crumbs = $graph->get('BreadcrumbList')['itemListElement'];
                $this->assertSame(range(1, count($crumbs)), array_column($crumbs, 'position'));
                foreach ($crumbs as $crumb) {
                    $this->assertNotEmpty($crumb['name']);
                    $this->assertStringStartsWith('https://', $crumb['item']);
                }
            }
        }
    }

    private function graph(string $url): Collection
    {
        $xpath = $this->xpath($this->get($url)->assertOk()->getContent());
        $scripts = $xpath->query('//script[@type="application/ld+json"]');
        $this->assertSame(1, $scripts->length);
        $schema = json_decode($scripts->item(0)->textContent, true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('https://schema.org', $schema['@context']);

        return collect($schema['@graph'])->keyBy('@type');
    }

    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$html);

        return new DOMXPath($document);
    }
}
