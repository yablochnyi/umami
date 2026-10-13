<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_map_is_visible_without_a_confirmation_button_in_all_languages(): void
    {
        $this->seed();
        $mapUrl = SiteSetting::where('key', 'map_embed_url')->value('value');

        foreach (['pl' => '/', 'uk' => '/uk', 'en' => '/en'] as $locale => $url) {
            $html = $this->get($url)->assertOk()
                ->assertDontSee('data-map-load', false)
                ->assertDontSee('data-map-notice', false)
                ->assertDontSee('data-map-src', false)
                ->assertSee('id="cookieConsent"', false)
                ->getContent();
            $document = new DOMDocument;
            @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
            $frames = (new DOMXPath($document))->query('//section[@id="contact"]//iframe');
            $this->assertSame(1, $frames->length);
            $frame = $frames->item(0);
            $this->assertSame($mapUrl, $frame->getAttribute('src'));
            $this->assertFalse($frame->hasAttribute('hidden'));
            $this->assertSame('lazy', $frame->getAttribute('loading'));
            $this->assertSame('no-referrer', $frame->getAttribute('referrerpolicy'));
            $this->assertSame(__('legal.map_title', [], $locale), $frame->getAttribute('title'));
        }
    }

    public function test_no_empty_map_is_rendered_without_a_configured_url(): void
    {
        $this->seed();
        SiteSetting::where('key', 'map_embed_url')->update(['value' => '']);

        $this->get('/')->assertOk()->assertDontSee('class="map-frame"', false);
    }
}
