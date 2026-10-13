<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutboundLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_outbound_links_use_https_and_protect_the_opener(): void
    {
        $this->seed();

        foreach (['/', '/uk', '/en'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $document = new DOMDocument;
            @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
            $xpath = new DOMXPath($document);
            $links = $xpath->query('//a[@target="_blank"]');
            $this->assertGreaterThanOrEqual(3, $links->length);
            foreach ($links as $link) {
                $this->assertStringStartsWith('https://', $link->getAttribute('href'));
                $this->assertContains('noopener', explode(' ', $link->getAttribute('rel')));
            }
            $this->assertSame(2, $xpath->query('//a[@href="https://umamisushifood.goorder.pl/"]')->length);
            $schema = json_decode($xpath->query('//script[@type="application/ld+json"]')->item(0)->textContent, true, flags: JSON_THROW_ON_ERROR);
            $restaurant = collect($schema['@graph'])->firstWhere('@type', 'Restaurant');
            $this->assertSame('https://umamisushifood.goorder.pl/', $restaurant['potentialAction']['target']['urlTemplate']);
        }
    }

    public function test_migration_only_upgrades_the_known_legacy_setting(): void
    {
        $this->seed();
        $migration = require database_path('migrations/2026_10_13_000001_upgrade_goorder_public_link_to_https.php');
        $setting = SiteSetting::where('key', 'order_url')->firstOrFail();
        foreach (['http://umamisushifood.goorder.pl', 'http://umamisushifood.goorder.pl/'] as $legacy) {
            $setting->update(['value' => $legacy]);
            $migration->up();
            $this->assertSame('https://umamisushifood.goorder.pl/', $setting->fresh()->value);
            $migration->up();
            $migration->down();
            $this->assertSame('https://umamisushifood.goorder.pl/', $setting->fresh()->value);
        }
        foreach (['https://umamisushifood.goorder.pl/', 'https://example.com/order', '/koszyk'] as $custom) {
            $setting->update(['value' => $custom]);
            $migration->up();
            $this->assertSame($custom, $setting->fresh()->value);
        }
    }

    public function test_missing_order_setting_uses_the_secure_default(): void
    {
        $this->seed();
        SiteSetting::where('key', 'order_url')->delete();

        $this->get('/')->assertOk()->assertSee('href="https://umamisushifood.goorder.pl/"', false);
    }
}
