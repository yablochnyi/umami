<?php

namespace Tests\Feature;

use App\Http\Controllers\LegalPageController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public static function pages(): array
    {
        $cases = [];
        foreach (LegalPageController::pageUrls('') as $page => $urls) {
            foreach ($urls as $locale => $url) {
                $cases[$page.'-'.$locale] = [$page, $locale, $url, $urls];
            }
        }

        return $cases;
    }

    #[DataProvider('pages')]
    public function test_legal_documents_render_in_each_language(string $page, string $locale, string $url, array $urls): void
    {
        $response = $this->get($url)->assertOk()
            ->assertSee('<html lang="'.$locale.'">', false)
            ->assertSee('<link rel="canonical" href="https://umamisushifood.pl'.$url.'">', false)
            ->assertSee('<a class="legal-back" href="'.($locale === 'pl' ? '/' : '/'.$locale).'">', false)
            ->assertSee(trans('legal.'.$page.'.title', [], $locale))
            ->assertSee(trans('legal.updated_label', [], $locale))
            ->assertSee('datetime="2026-09-29"', false)
            ->assertSee('data-cookie-settings', false)
            ->assertSee('id="cookieConsent"', false)
            ->assertSee('/assets/umami/privacy.js', false)
            ->assertSee('/assets/umami/privacy.css', false)
            ->assertSee('<script async src="https://widget.wenetasystent.ai/?code=rBAad0rd"></script>', false)
            ->assertDontSee('<script async src="https://www.googletagmanager.com', false);

        foreach ($urls as $language => $alternate) {
            $response->assertSee('hreflang="'.$language.'" href="https://umamisushifood.pl'.$alternate.'"', false);
        }
        foreach (trans('legal.'.$page.'.sections', [], $locale) as $index => $section) {
            $response->assertSee('id="section-'.($index + 1).'"', false)->assertSee($section['heading']);
        }
        if ($page !== 'cookies') {
            $response->assertSee('9562405793')->assertSee('DARIA JANZ, MARYNA ZASLAVSKA')
                ->assertSee('Kniaziewicza 52A/3')->assertSee('Andersa 72');
        }
        if ($locale !== 'pl') {
            $response->assertDontSee('Ostatnia aktualizacja:');
        }
    }

    public function test_mismatched_locale_and_slug_are_not_served(): void
    {
        $this->get('/uk/privacy-policy')->assertNotFound();
        $this->get('/en/polityka-cookie')->assertNotFound();
    }

    public function test_translations_have_matching_document_structure(): void
    {
        foreach (['privacy', 'cookies', 'terms'] as $page) {
            $polish = trans('legal.'.$page.'.sections', [], 'pl');
            foreach (['uk', 'en'] as $locale) {
                $translated = trans('legal.'.$page.'.sections', [], $locale);
                $this->assertCount(count($polish), $translated);
                foreach ($translated as $index => $section) {
                    $this->assertNotEmpty($section['heading']);
                    $this->assertCount(count($polish[$index]['body']), $section['body']);
                }
            }
        }
    }
}
