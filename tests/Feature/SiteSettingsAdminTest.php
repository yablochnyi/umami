<?php

namespace Tests\Feature;

use App\Filament\Pages\SiteSettings as RestaurantSettings;
use App\Filament\Resources\SiteSettings\Pages\CreateSiteSetting;
use App\Filament\Resources\SiteSettings\Pages\EditSiteSetting;
use App\Filament\Resources\SiteSettings\Pages\ListSiteSettings;
use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\SiteSettingCatalog;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SiteSettingsAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['role_id' => Role::where('is_admin', true)->sole()->id]));
        app()->setLocale('uk');
    }

    private function setting(string $key, ?string $value = null, string $type = 'text'): SiteSetting
    {
        return SiteSetting::updateOrCreate(['key' => $key], ['group' => 'original', 'label' => 'Original label', 'value' => $value, 'type' => $type, 'sort_order' => 7]);
    }

    public function test_only_values_can_be_changed_even_with_forged_metadata(): void
    {
        $setting = $this->setting('address', 'Old address');
        Livewire::test(EditSiteSetting::class, ['record' => $setting->id])
            ->assertSet('data', ['value' => 'Old address'])
            ->fillForm(['value' => 'New address'])
            ->set('data.key', 'opening_time')->set('data.type', 'image')->set('data.group', 'hacked')
            ->set('data.label', 'Changed label')->set('data.sort_order', 999)
            ->call('save')->assertHasNoFormErrors();
        $this->assertDatabaseHas('site_settings', ['id' => $setting->id, 'key' => 'address', 'group' => 'original', 'type' => 'text', 'label' => 'Original label', 'sort_order' => 7, 'value' => 'New address']);
    }

    public function test_system_settings_cannot_be_created_or_deleted_even_by_admin(): void
    {
        $setting = $this->setting('address');
        $this->assertFalse(Gate::allows('create', SiteSetting::class));
        $this->assertFalse(Gate::allows('delete', $setting));
        $this->assertFalse(Gate::allows('deleteAny', SiteSetting::class));
        Livewire::test(CreateSiteSetting::class)->assertForbidden();
        $this->get('/admin/site-settings/create')->assertNotFound();
    }

    public function test_delivery_and_unknown_technical_settings_are_not_in_content_resource(): void
    {
        $visible = $this->setting('address');
        $hidden = $this->setting('delivery_tier_1_zone_id', '2');
        $unknown = $this->setting('internal_service_key', 'private');
        Livewire::test(ListSiteSettings::class)->assertCanSeeTableRecords([$visible])->assertCanNotSeeTableRecords([$hidden, $unknown]);
        $this->get('/admin/site-settings/'.$hidden->id.'/edit')->assertNotFound();
        $this->get('/admin/site-settings/'.$unknown->id)->assertNotFound();
    }

    public function test_localized_setting_labels_are_searchable(): void
    {
        $logo = $this->setting('logo_image', null, 'image');
        $address = $this->setting('address');
        Livewire::test(ListSiteSettings::class)->searchTable('Логотип')->assertCanSeeTableRecords([$logo])->assertCanNotSeeTableRecords([$address]);
    }

    public function test_every_content_setting_has_a_form_and_translations_in_both_languages(): void
    {
        Storage::fake('public');
        foreach (SiteSettingCatalog::FIELDS as $key => [$type, $group]) {
            $setting = $this->setting($key);
            foreach (['pl', 'uk'] as $locale) {
                app()->setLocale($locale);
                Livewire::test(EditSiteSetting::class, ['record' => $setting->id])->assertSuccessful()->assertSee(__('settings.labels.'.$key));
                $this->assertNotSame('settings.hints.'.$key, __('settings.hints.'.$key));
            }
        }
    }

    public function test_image_upload_updates_the_value_without_changing_its_type(): void
    {
        Storage::fake('public');
        $setting = $this->setting('logo_image', null, 'image');
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC0lEQVR42mP8/x8AAwMCAO+jSZkAAAAASUVORK5CYII=');
        $image = UploadedFile::fake()->createWithContent('logo.png', $png);
        Livewire::test(EditSiteSetting::class, ['record' => $setting->id])->fillForm(['value' => $image])->call('save')->assertHasNoFormErrors();
        Storage::disk('public')->assertExists($setting->fresh()->value);
        $this->assertSame('image', $setting->fresh()->type);
    }

    public function test_video_preview_distinguishes_missing_files_from_existing_files(): void
    {
        Storage::fake('public');
        $setting = $this->setting('hero_video_desktop', 'umami/settings/hero.mp4', 'video');
        $this->view('filament.partials.setting-video', ['setting' => $setting])
            ->assertSee(__('settings.file_missing'))->assertDontSee('<video', false);
        Storage::disk('public')->put($setting->value, 'video fixture');
        $this->view('filament.partials.setting-video', ['setting' => $setting])
            ->assertSee('<video', false)->assertDontSee(__('settings.file_missing'));
    }

    public function test_non_images_are_rejected_for_logo_even_when_type_is_forged(): void
    {
        Storage::fake('public');
        $setting = $this->setting('logo_image', null, 'image');
        $file = UploadedFile::fake()->createWithContent('file.txt', 'not an image');
        Livewire::test(EditSiteSetting::class, ['record' => $setting->id])->set('data.type', 'text')
            ->fillForm(['value' => $file])->call('save')->assertHasFormErrors();
        $this->assertNull($setting->fresh()->value);
    }

    public function test_call_number_is_presented_without_protocol_and_saved_as_tel_link(): void
    {
        $setting = $this->setting('phone_href', 'tel:+48123456789');
        Livewire::test(EditSiteSetting::class, ['record' => $setting->id])->assertSet('data.value', '+48123456789')
            ->fillForm(['value' => '+48 111 222 333'])->call('save')->assertHasNoFormErrors();
        $this->assertSame('tel:+48111222333', $setting->fresh()->value);
    }

    public function test_url_and_analytics_format_validation(): void
    {
        $setting = $this->setting('site_url', 'https://example.test');
        Livewire::test(EditSiteSetting::class, ['record' => $setting->id])->fillForm(['value' => 'javascript:alert(1)'])->call('save')->assertHasFormErrors(['value']);
        $setting = $this->setting('google_analytics_id');
        Livewire::test(EditSiteSetting::class, ['record' => $setting->id])->fillForm(['value' => 'invalid'])->call('save')->assertHasFormErrors(['value']);
    }

    public function test_restaurant_save_preserves_technical_mappings_and_metadata(): void
    {
        $technical = $this->setting('delivery_tier_1_zone_id', '99');
        $baseCost = $this->setting('delivery_cost', '7');
        $price = $this->setting('delivery_tier_1_cost', '9.99', 'number');
        Livewire::test(RestaurantSettings::class)->fillForm(['delivery_tier_1_cost' => '11', 'delivery_tier_1_streets' => "Street one\nStreet two"])
            ->set('data.delivery_tier_1_zone_id', 'hacked')->set('data.delivery_cost', '0')->call('save')->assertHasNoFormErrors();
        $this->assertSame('99', $technical->fresh()->value);
        $this->assertSame('7', $baseCost->fresh()->value);
        $this->assertSame('11', $price->fresh()->value);
        $this->assertSame('Original label', $price->fresh()->label);
        $this->assertSame('original', $price->fresh()->group);
    }

    public function test_zone_boundaries_are_validated_before_any_settings_are_saved(): void
    {
        $before = SiteSetting::query()->orderBy('id')->get()->toArray();
        Livewire::test(RestaurantSettings::class)->fillForm(['delivery_tier_1_max_km' => '8', 'delivery_tier_2_max_km' => '3'])
            ->call('save')->assertHasFormErrors(['delivery_tier_2_max_km']);
        $this->assertSame($before, SiteSetting::query()->orderBy('id')->get()->toArray());
    }

    public function test_read_only_role_cannot_save_content_settings(): void
    {
        $setting = $this->setting('address', 'Original');
        $role = Role::create(['name' => 'Read settings', 'permissions' => ['site_settings.view']]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));
        $this->get('/admin/site-settings/'.$setting->id)->assertOk();
        Livewire::test(EditSiteSetting::class, ['record' => $setting->id])->assertForbidden();
    }
}
