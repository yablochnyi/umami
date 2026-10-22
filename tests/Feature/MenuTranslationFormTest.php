<?php

namespace Tests\Feature;

use App\Filament\Resources\MenuCategories\Pages\EditMenuCategory;
use App\Filament\Resources\MenuItems\Pages\EditMenuItem;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MenuTranslationFormTest extends TestCase
{
    use RefreshDatabase;

    private MenuCategory $category;

    private MenuItem $dish;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['role_id' => Role::where('is_admin', true)->sole()->id]));
        $names = ['pl' => 'Ramen', 'uk' => 'Ramen UA', 'en' => 'Ramen EN'];
        $this->category = MenuCategory::create(['name' => $names, 'slug' => 'ramen', 'is_active' => true]);
        $this->dish = MenuItem::create(['menu_category_id' => $this->category->id, 'name' => $names, 'slug' => 'ramen', 'price' => '39', 'is_active' => true]);
    }

    public function test_dish_translations_save_across_all_language_tabs(): void
    {
        $data = [];
        foreach (['description', 'marketing_description', 'seo_title', 'seo_description'] as $field) {
            foreach (['pl', 'uk', 'en'] as $locale) {
                $data[$field][$locale] = $field.' '.$locale;
            }
        }
        Livewire::test(EditMenuItem::class, ['record' => $this->dish->id])
            ->fillForm($data)->call('save')->assertHasNoFormErrors();
        $this->dish->refresh();
        foreach ($data as $field => $translations) {
            $this->assertSame($translations, $this->dish->getTranslations($field));
        }
        Livewire::test(EditMenuItem::class, ['record' => $this->dish->id])
            ->fillForm(['marketing_description' => ['pl' => 'Updated', 'uk' => $data['marketing_description']['uk'], 'en' => $data['marketing_description']['en']]])
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame($data['description'], $this->dish->fresh()->getTranslations('description'));
    }

    public function test_category_translations_save_across_all_language_tabs(): void
    {
        $data = [];
        foreach (['intro_text', 'seo_text'] as $field) {
            foreach (['pl', 'uk', 'en'] as $locale) {
                $data[$field][$locale] = $field.' '.$locale;
            }
        }
        Livewire::test(EditMenuCategory::class, ['record' => $this->category->id])
            ->fillForm($data)->call('save')->assertHasNoFormErrors();
        foreach ($data as $field => $translations) {
            $this->assertSame($translations, $this->category->fresh()->getTranslations($field));
        }
    }

    public function test_meta_limits_apply_to_inactive_language_tabs(): void
    {
        app()->setLocale('pl');
        Livewire::test(EditMenuItem::class, ['record' => $this->dish->id])
            ->fillForm(['seo_title.en' => str_repeat('x', 71), 'seo_description.uk' => str_repeat('x', 171)])
            ->call('save')->assertHasFormErrors(['seo_title.en' => 'max', 'seo_description.uk' => 'max']);
    }

    public function test_edit_pages_render_language_tabs_in_both_admin_locales(): void
    {
        foreach (['pl', 'uk'] as $locale) {
            auth()->user()->forceFill(['admin_locale' => $locale])->save();
            foreach (['menu-items' => $this->dish, 'menu-categories' => $this->category] as $resource => $record) {
                $response = $this->get('/admin/'.$resource.'/'.$record->id.'/edit')->assertOk()
                    ->assertSee('Polski')->assertSee('Українська')->assertSee('English');
                // Optional synthetic HTML fixtures for browser layout QA; never production data.
                if ($directory = getenv('UMAMI_FORM_PREVIEW_DIR')) {
                    if (! is_dir($directory)) {
                        mkdir($directory, 0755, true);
                    }
                    file_put_contents($directory.'/'.$resource.'-'.$locale.'.html', $response->getContent());
                }
            }
        }
    }
}
