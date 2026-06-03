<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type": "Restaurant"', false);
        $response->assertSee('"telephone": "+48513233722"', false);
        $response->assertSee('<link rel="canonical" href="https://www.umamisushifood.pl/">', false);
        $response->assertSee('<link rel="alternate" hreflang="uk" href="https://www.umamisushifood.pl/uk">', false);
        $response->assertSee('<meta property="og:title"', false);
        $response->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
    }

    public function test_localized_pages_use_clean_urls(): void
    {
        $this->get('/uk')
            ->assertStatus(200)
            ->assertSee('<html lang="uk">', false)
            ->assertSee('<link rel="canonical" href="https://www.umamisushifood.pl/uk">', false);

        $this->get('/en')
            ->assertStatus(200)
            ->assertSee('<html lang="en">', false)
            ->assertSee('<link rel="canonical" href="https://www.umamisushifood.pl/en">', false);
    }

    public function test_old_query_language_urls_redirect_to_clean_urls(): void
    {
        $this->get('/?lang=uk')->assertRedirect('https://www.umamisushifood.pl/uk');
        $this->get('/?lang=en')->assertRedirect('https://www.umamisushifood.pl/en');
        $this->get('/?lang=pl')->assertRedirect('https://www.umamisushifood.pl/');
    }

    public function test_the_sitemap_returns_public_language_urls(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee('<loc>https://www.umamisushifood.pl/</loc>', false);
        $response->assertSee('<loc>https://www.umamisushifood.pl/uk</loc>', false);
        $response->assertSee('<loc>https://www.umamisushifood.pl/en</loc>', false);
        $response->assertSee('xmlns:xhtml="http://www.w3.org/1999/xhtml"', false);
        $response->assertSee('hreflang="x-default"', false);
    }

    public function test_the_robots_file_points_to_the_sitemap(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee('User-agent: *', false);
        $response->assertSee('Sitemap: https://www.umamisushifood.pl/sitemap.xml', false);
    }
}
