<?php

namespace Tests\Feature;

use App\Filament\Resources\SiteTexts\Pages\CreateSiteText;
use App\Filament\Resources\SiteTexts\Pages\EditSiteText;
use App\Filament\Resources\SiteTexts\Pages\ListSiteTexts;
use App\Filament\Resources\SiteTexts\Pages\ViewSiteText;
use App\Models\Role;
use App\Models\SiteText;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class SiteTextsAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['role_id' => Role::where('is_admin', true)->sole()->id]));
        app()->setLocale('uk');
    }

    private function text(string $key = 'aboutText'): SiteText
    {
        return SiteText::create([
            'key' => $key, 'group' => 'about', 'label' => 'Internal label', 'type' => 'textarea', 'sort_order' => 7,
            'value' => ['pl' => 'Polski tekst', 'uk' => 'Український текст', 'en' => 'English text', 'de' => 'Preserved'],
        ]);
    }

    public function test_metadata_and_other_languages_are_preserved_even_with_forged_payload(): void
    {
        $text = $this->text();
        Livewire::test(EditSiteText::class, ['record' => $text->id])
            ->assertSet('data', ['value' => $text->getTranslations('value')])
            ->fillForm(['value' => ['pl' => 'Nowy tekst', 'uk' => 'Новий текст', 'en' => 'New text']])
            ->set('data.key', 'hacked')->set('data.group', 'hacked')->set('data.type', 'html')
            ->set('data.label', 'Hacked')->set('data.sort_order', 0)->set('data.value.de', 'Hacked')
            ->call('save')->assertHasNoFormErrors();
        $this->assertDatabaseHas('site_texts', ['id' => $text->id, 'key' => 'aboutText', 'group' => 'about', 'label' => 'Internal label', 'type' => 'textarea', 'sort_order' => 7]);
        $this->assertSame(['pl' => 'Nowy tekst', 'uk' => 'Новий текст', 'en' => 'New text', 'de' => 'Preserved'], $text->fresh()->getTranslations('value'));
    }

    public function test_system_texts_cannot_be_created_or_deleted(): void
    {
        $text = $this->text();
        $this->assertFalse(Gate::allows('create', SiteText::class));
        $this->assertFalse(Gate::allows('delete', $text));
        $this->assertFalse(Gate::allows('deleteAny', SiteText::class));
        $this->get('/admin/site-texts/create')->assertNotFound();
        Livewire::test(CreateSiteText::class)->assertForbidden();
        Livewire::test(ListSiteTexts::class)->assertActionDoesNotExist('create');
        Livewire::test(EditSiteText::class, ['record' => $text->id])->assertActionDoesNotExist('delete');
    }

    public function test_human_labels_and_only_translation_fields_are_present_in_both_locales(): void
    {
        $text = $this->text();
        foreach (['pl', 'uk'] as $locale) {
            app()->setLocale($locale);
            Livewire::test(ListSiteTexts::class)->assertSee(__('texts.blocks.aboutText'))
                ->assertDontSee('Internal label')->assertDontSee('aboutText');
            foreach ([EditSiteText::class, ViewSiteText::class] as $page) {
                $component = Livewire::test($page, ['record' => $text->id])->assertSee(__('texts.blocks.aboutText'));
                foreach (['key', 'group', 'label', 'type', 'sort_order'] as $field) {
                    $component->assertFormFieldDoesNotExist($field);
                }
                foreach (['pl', 'uk', 'en'] as $language) {
                    $component->assertFormFieldExists('value.'.$language)->assertSee(__('texts.languages.'.$language));
                }
            }
        }
    }

    public function test_search_uses_localized_block_names(): void
    {
        $about = $this->text();
        $hero = $this->text('heroOrder');
        Livewire::test(ListSiteTexts::class)->searchTable('кнопка замовлення')->assertCanSeeTableRecords([$hero])->assertCanNotSeeTableRecords([$about]);
    }

    public function test_read_only_roles_cannot_save_texts(): void
    {
        $text = $this->text();
        $role = Role::create(['name' => 'Reader', 'permissions' => ['site_texts.view']]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));
        Livewire::test(ViewSiteText::class, ['record' => $text->id])->assertSuccessful();
        Livewire::test(EditSiteText::class, ['record' => $text->id])->assertForbidden();
    }

    public function test_editor_role_can_update_existing_texts_without_create_or_delete_access(): void
    {
        $text = $this->text();
        $role = Role::create(['name' => 'Editor', 'permissions' => ['site_texts.view', 'site_texts.update']]);
        // Previously stored permissions must not bypass the new resource restrictions.
        DB::table('roles')->where('id', $role->id)->update([
            'permissions' => json_encode(['site_texts.view', 'site_texts.update', 'site_texts.create', 'site_texts.delete']),
        ]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));
        Livewire::test(EditSiteText::class, ['record' => $text->id])->fillForm(['value.pl' => 'Edited'])
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame('Edited', $text->fresh()->getTranslation('value', 'pl'));
        $this->assertFalse(Gate::allows('create', SiteText::class));
        $this->assertFalse(Gate::allows('delete', $text));
    }
}
