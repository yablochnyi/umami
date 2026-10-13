<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Support\OptimizedMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaOptimizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_optimization_creates_smaller_variants_without_changing_originals_or_menu_records(): void
    {
        $this->seed();
        Storage::fake('public');
        $disk = Storage::disk('public');
        $disk->makeDirectory('umami');
        $path = 'umami/test-dish.jpg';
        $this->createImage($disk->path($path));
        $item = MenuItem::firstOrFail();
        $item->update(['image' => $path]);
        $before = $item->fresh()->getAttributes();
        $originalHash = hash_file('sha256', $disk->path($path));

        $this->artisan('site:optimize-media', ['--images-only' => true])->assertSuccessful();

        $media = app(OptimizedMedia::class);
        foreach (['card' => 640, 'image' => 1280] as $profile => $width) {
            $variant = $media->derivative($path, $profile);
            $disk->assertExists($variant);
            $this->assertSame($width, getimagesize($disk->path($variant))[0]);
            $this->assertLessThan($disk->size($path), $disk->size($variant));
            $this->assertSame($disk->url($variant), $media->url($path, $profile));
        }
        $this->assertSame($originalHash, hash_file('sha256', $disk->path($path)));
        $this->assertSame($before, $item->fresh()->getAttributes());
        $files = $disk->allFiles('umami/optimized');
        $this->artisan('site:optimize-media', ['--images-only' => true])->assertSuccessful();
        $this->assertSame($files, $disk->allFiles('umami/optimized'));
    }

    public function test_changed_original_does_not_reuse_a_stale_derivative(): void
    {
        Storage::fake('public');
        $disk = Storage::disk('public');
        $disk->put('dish.webp', 'original');
        $media = app(OptimizedMedia::class);
        $old = $media->derivative('dish.webp', 'card');
        $disk->put($old, 'optimized');
        $this->assertNotNull($media->url('dish.webp', 'card'));
        $disk->put('dish.webp', 'new original with another size');
        clearstatcache();
        $this->assertNotSame($old, $media->derivative('dish.webp', 'card'));
        $this->assertNull($media->url('dish.webp', 'card'));
    }

    public function test_external_missing_and_unsafe_paths_fall_back_without_conversion(): void
    {
        Storage::fake('public');
        $media = app(OptimizedMedia::class);
        foreach ([null, '', 'missing.jpg', 'https://example.com/photo.jpg', '//example.com/photo.jpg', '../private.jpg', '/assets/logo.svg'] as $path) {
            $this->assertNull($media->url($path));
        }
        Storage::disk('public')->put('umami/photo.webp', 'source');
        $this->assertSame('umami/photo.webp', $media->source('/storage/umami/photo.png'));
    }

    public function test_failed_image_conversion_keeps_original_and_does_not_publish_partial_files(): void
    {
        $this->seed();
        Storage::fake('public');
        Storage::disk('public')->put('broken.jpg', 'not an image');
        MenuItem::firstOrFail()->update(['image' => 'broken.jpg']);

        $this->artisan('site:optimize-media', ['--images-only' => true])->assertFailed();

        $this->assertSame('not an image', Storage::disk('public')->get('broken.jpg'));
        $this->assertSame([], Storage::disk('public')->allFiles('umami/optimized'));
    }

    public function test_homepage_has_responsive_priority_poster_and_lazy_card_images_in_all_languages(): void
    {
        $this->seed();
        foreach (['/', '/uk', '/en'] as $url) {
            $html = $this->get($url)->assertOk()
                ->assertSee('class="hero-poster"', false)
                ->assertSee('media="(max-width: 900px)" fetchpriority="high"', false)
                ->assertSee('media="(min-width: 901px)" fetchpriority="high"', false)
                ->assertSee('preload="none"', false)
                ->assertSee('data-hero-video-mobile=', false)
                ->assertSee('data-hero-video-desktop=', false)
                ->assertSee('loading="lazy" decoding="async"', false)
                ->getContent();
            $this->assertDoesNotMatchRegularExpression('/<video[^>]+\ssrc=/', $html);
            $this->assertDoesNotMatchRegularExpression('/<video[^>]+\sautoplay/', $html);
        }
    }

    private function createImage(string $path): void
    {
        $image = imagecreatetruecolor(1400, 900);
        for ($x = 0; $x < 1400; $x++) {
            $color = imagecolorallocate($image, $x % 256, ($x * 3) % 256, ($x * 7) % 256);
            imageline($image, $x, 0, $x, 899, $color);
        }
        imagejpeg($image, $path, 100);
        imagedestroy($image);
    }
}
