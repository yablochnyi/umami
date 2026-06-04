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
        $response->assertSee('<link rel="canonical" href="https://umamisushifood.pl/">', false);
        $response->assertSee('<link rel="alternate" hreflang="uk" href="https://umamisushifood.pl/uk">', false);
        $response->assertSee('<meta property="og:title"', false);
        $response->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
        $response->assertSee('id="cookieConsent"', false);
        $response->assertSee('Prywatność i cookies', false);
        $response->assertSee('Zgadzam się', false);
        $response->assertSee('Polityka prywatności', false);
        $response->assertSee('Polityka plików cookie', false);
        $response->assertSee('Regulamin', false);
    }

    public function test_localized_pages_use_clean_urls(): void
    {
        $this->get('/uk')
            ->assertStatus(200)
            ->assertSee('<html lang="uk">', false)
            ->assertSee('<link rel="canonical" href="https://umamisushifood.pl/uk">', false)
            ->assertSee('Приватність і cookies', false);

        $this->get('/en')
            ->assertStatus(200)
            ->assertSee('<html lang="en">', false)
            ->assertSee('<link rel="canonical" href="https://umamisushifood.pl/en">', false)
            ->assertSee('Privacy and cookies', false);
    }

    public function test_old_query_language_urls_redirect_to_clean_urls(): void
    {
        $this->get('/?lang=uk')->assertRedirect('https://umamisushifood.pl/uk');
        $this->get('/?lang=en')->assertRedirect('https://umamisushifood.pl/en');
        $this->get('/?lang=pl')->assertRedirect('https://umamisushifood.pl/');
    }

    public function test_the_sitemap_returns_public_language_urls(): void
    {
        $sitemap = file_get_contents(public_path('sitemap.xml'));

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $sitemap);
        $this->assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"', $sitemap);
        $this->assertStringContainsString('<loc>https://umamisushifood.pl/</loc>', $sitemap);
        $this->assertStringContainsString('<loc>https://umamisushifood.pl/uk</loc>', $sitemap);
        $this->assertStringContainsString('<loc>https://umamisushifood.pl/en</loc>', $sitemap);
        $this->assertStringContainsString('<loc>https://umamisushifood.pl/polityka-prywatnosci</loc>', $sitemap);
        $this->assertStringContainsString('<loc>https://umamisushifood.pl/uk/polityka-konfidentsiynosti</loc>', $sitemap);
        $this->assertStringContainsString('<loc>https://umamisushifood.pl/en/privacy-policy</loc>', $sitemap);
        $this->assertStringContainsString('xmlns:xhtml="http://www.w3.org/1999/xhtml"', $sitemap);
        $this->assertStringContainsString('hreflang="x-default"', $sitemap);
    }

    public function test_static_legal_pages_are_localized_and_indexable(): void
    {
        $this->get('/polityka-prywatnosci')
            ->assertStatus(200)
            ->assertSee('<html lang="pl">', false)
            ->assertSee('<link rel="canonical" href="https://umamisushifood.pl/polityka-prywatnosci">', false)
            ->assertSee('Polityka prywatności', false)
            ->assertSee('Administratorem danych', false);

        $this->get('/uk/polityka-cookie')
            ->assertStatus(200)
            ->assertSee('<html lang="uk">', false)
            ->assertSee('<link rel="canonical" href="https://umamisushifood.pl/uk/polityka-cookie">', false)
            ->assertSee('Політика cookie', false);

        $this->get('/en/terms')
            ->assertStatus(200)
            ->assertSee('<html lang="en">', false)
            ->assertSee('<link rel="canonical" href="https://umamisushifood.pl/en/terms">', false)
            ->assertSee('Terms of website use', false);
    }

    public function test_menu_category_and_item_pages_are_indexable(): void
    {
        $this->seed();

        $this->get('/')
            ->assertStatus(200)
            ->assertSee('data-url="/menu/rameny/chashu-ramen"', false)
            ->assertDontSee('data-url="https://umamisushifood.pl/menu/rameny/chashu-ramen"', false);

        $this->get('/menu/rameny')
            ->assertStatus(200)
            ->assertSee('<link rel="canonical" href="https://umamisushifood.pl/menu/rameny">', false)
            ->assertSee('"@type": "MenuSection"', false)
            ->assertSee('"@type": "BreadcrumbList"', false)
            ->assertSee('Rameny są sycącą częścią menu', false)
            ->assertSee('Ramen w Toruniu często wybierają osoby', false)
            ->assertSee('Chashu Ramen', false);

        $this->get('/menu/rameny/chashu-ramen')
            ->assertStatus(200)
            ->assertSee('<link rel="canonical" href="https://umamisushifood.pl/menu/rameny/chashu-ramen">', false)
            ->assertSee('"@type": "MenuItem"', false)
            ->assertSee('"@type": "Product"', false)
            ->assertSee('"@type": "BreadcrumbList"', false)
            ->assertSee('Skład', false)
            ->assertSee('Dlaczego warto spróbować', false)
            ->assertSee('Chashu Ramen jest dla osób', false)
            ->assertDontSee('Skład:', false)
            ->assertSee('Chashu Ramen Toruń', false);

        $this->get('/menu/rameny')
            ->assertDontSee('wpisujesz w Google', false);

        $this->get('/uk/menu/rameny/chashu-ramen')
            ->assertStatus(200)
            ->assertSee('<html lang="uk">', false)
            ->assertSee('<link rel="canonical" href="https://umamisushifood.pl/uk/menu/rameny/chashu-ramen">', false);

        $sitemap = file_get_contents(public_path('sitemap.xml'));

        $this->assertStringContainsString('<loc>https://umamisushifood.pl/menu/rameny</loc>', $sitemap);
        $this->assertStringContainsString('<loc>https://umamisushifood.pl/menu/rameny/chashu-ramen</loc>', $sitemap);
        $this->assertStringContainsString('<loc>https://umamisushifood.pl/en/menu/rameny/chashu-ramen</loc>', $sitemap);
    }

    public function test_the_robots_file_points_to_the_sitemap(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee('User-agent: *', false);
        $response->assertSee('Sitemap: https://umamisushifood.pl/sitemap.xml', false);
    }
}
