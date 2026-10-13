<?php

namespace Tests\Feature;

use App\Models\GalleryImage;
use App\Support\ImageAlternativeText;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImageAlternativeTextTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_images_have_alternative_text_or_are_explicitly_decorative(): void
    {
        $this->seed();

        foreach (['pl' => '', 'uk' => '/uk', 'en' => '/en'] as $locale => $prefix) {
            foreach (['/', '/menu/rameny', '/menu/rameny/chashu-ramen'] as $path) {
                $html = $this->get($prefix.$path)->assertOk()->getContent();
                $document = new DOMDocument;
                @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
                $xpath = new DOMXPath($document);
                $this->assertSame(0, $xpath->query('//img[not(@alt)]')->length);
                $this->assertSame(0, $xpath->query('//img[normalize-space(@alt)="" and not(@hidden) and not(ancestor::*[@aria-hidden="true"])]')->length);
                $this->assertSame(0, $xpath->query('//img[contains(@src,"cart-svgrepo")]')->length);
                $this->assertSame(0, $xpath->query('//*[@data-cart-add][not(normalize-space(@aria-label))]')->length);
                $this->assertStringContainsString(e(__('images.logo', [], $locale)), $html);

                if ($path === '/') {
                    $gallery = $xpath->query('//button[contains(@class,"gallery-slide")]/img');
                    $this->assertSame(15, $gallery->length);
                    foreach ($gallery as $image) {
                        $this->assertDoesNotMatchRegularExpression('/^(Galeria|Gallery|Галерея)\s+\d+$/u', $image->getAttribute('alt'));
                        $this->assertSame($image->getAttribute('alt'), $image->parentNode->getAttribute('data-image-alt'));
                    }
                    $this->assertSame(__('images.res8', [], $locale), $xpath->query('//section[@id="about"]//img')->item(0)->getAttribute('alt'));
                }
            }
        }
    }

    public function test_custom_gallery_text_is_preserved_and_escaped(): void
    {
        $this->seed();
        GalleryImage::where('image', 'umami/res1.png')->firstOrFail()->update([
            'alt' => ['pl' => 'Opis "autorski" <zdjęcia>', 'uk' => 'Авторський опис', 'en' => 'Custom description'],
        ]);

        $this->get('/')->assertSee('alt="Opis &quot;autorski&quot; &lt;zdjęcia&gt;"', false);
        $this->get('/uk')->assertSee('alt="Авторський опис"', false);
        $this->get('/en')->assertSee('alt="Custom description"', false);
    }

    public function test_fallback_describes_only_known_photos_and_respects_custom_titles(): void
    {
        $text = new ImageAlternativeText;
        foreach (['pl', 'uk', 'en'] as $locale) {
            app()->setLocale($locale);
            foreach (range(1, 15) as $number) {
                $expected = __('images.res'.$number);
                $this->assertNotSame('images.res'.$number, $expected);
                $this->assertSame($expected, $text->forImage('umami/res'.$number.'.png', 'Galeria '.$number));
                $this->assertSame($expected, $text->forImage('/storage/umami/res'.$number.'.webp', ' ', 'Gallery '.$number));
            }
            $this->assertSame('Custom title', $text->forImage('umami/res8.png', '', 'Custom title'));
            foreach (['umami/gallery/res8.png', 'https://example.com/umami/res8.png', null] as $path) {
                $this->assertSame(__('images.gallery'), $text->forImage($path, null, 'Gallery 1'));
            }
        }
    }
}
