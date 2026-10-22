<?php

namespace Tests\Feature;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TranslationCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalogues_have_the_same_keys_and_placeholders(): void
    {
        foreach (['checkout', 'menu', 'site'] as $group) {
            $reference = Arr::dot(require lang_path('pl/'.$group.'.php'));
            foreach (['uk', 'en'] as $locale) {
                $translated = Arr::dot(require lang_path($locale.'/'.$group.'.php'));
                $this->assertSame(array_keys($reference), array_keys($translated), $group.' '.$locale);
                foreach ($reference as $key => $text) {
                    $this->assertNotEmpty($translated[$key]);
                    preg_match_all('/:[a-zA-Z_]+/', $text, $expected);
                    preg_match_all('/:[a-zA-Z_]+/', $translated[$key], $actual);
                    $this->assertEqualsCanonicalizing($expected[0], $actual[0], $group.'.'.$key.' '.$locale);
                }
            }
        }
    }

    public function test_admin_labels_resolve_without_json_catalogues(): void
    {
        $this->assertFileDoesNotExist(lang_path('pl.json'));
        $this->assertFileDoesNotExist(lang_path('uk.json'));
        $pl = require lang_path('pl/labels.php');
        $uk = require lang_path('uk/labels.php');
        $this->assertSame(array_keys($pl), array_keys($uk));
        foreach (['pl', 'uk'] as $locale) {
            $this->assertSame(${$locale}['price'], __('labels.price', [], $locale));
            foreach (['asap', 'scheduled', 'cash', 'card', 'pickup', 'delivery'] as $state) {
                $this->assertNotEmpty(${$locale}[$state]);
            }
            foreach (['pl', 'ua', 'en'] as $language) {
                $this->assertTrue(app('translator')->hasForLocale('labels.meta_title_'.$language, $locale));
                $this->assertTrue(app('translator')->hasForLocale('labels.meta_description_'.$language, $locale));
            }
        }
        foreach (File::allFiles(app_path('Filament')) as $file) {
            preg_match_all('/[\'\"](labels\.[a-z_0-9]+)[\'\"]/', $file->getContents(), $matches);
            foreach ($matches[1] as $key) {
                if (str_ends_with($key, '_')) {
                    continue; // Dynamic meta labels are covered above.
                }
                foreach (['pl', 'uk'] as $locale) {
                    $this->assertTrue(app('translator')->hasForLocale($key, $locale), $key.' '.$locale);
                }
            }
        }
    }

    public function test_checkout_and_menu_render_localized_copy_without_changing_ingredients(): void
    {
        $category = MenuCategory::create(['name' => ['pl' => 'Ramen'], 'slug' => 'ramen', 'is_active' => true]);
        $dish = MenuItem::create([
            'menu_category_id' => $category->id, 'slug' => 'test-ramen', 'name' => ['pl' => 'Test Ramen'],
            'description' => ['pl' => 'Makaron, jajko', 'uk' => 'Локшина, яйце', 'en' => 'Noodles, egg'],
            'price' => '39', 'is_active' => true,
        ]);
        foreach (['pl' => '', 'uk' => '/uk', 'en' => '/en'] as $locale => $prefix) {
            $this->get($prefix.'/koszyk')->assertOk()->assertSee(__('checkout.title', [], $locale));
            $this->get($prefix.'/menu/ramen/test-ramen')->assertOk()
                ->assertSee($dish->getTranslation('description', $locale))
                ->assertSee(__('menu.copy.details', [], $locale))
                ->assertDontSee('menu.itemTitle')->assertDontSee(':name')->assertDontSee(':ingredients');
        }
    }

    public function test_controllers_do_not_validate_http_input_inline(): void
    {
        foreach (File::allFiles(app_path('Http/Controllers')) as $file) {
            $this->assertStringNotContainsString('$request->validate(', $file->getContents(), $file->getFilename());
        }
    }
}
