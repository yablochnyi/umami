<?php

namespace Tests\Feature;

use App\Filament\Pages\HolidayDecorations;
use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\HalloweenDecor;
use App\Support\NewYearDecor;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NewYearDecorTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_year_is_opt_in_and_available_on_all_storefront_languages(): void
    {
        $this->seed();
        $this->assertFalse(NewYearDecor::enabled());
        $this->get('/')->assertOk()->assertDontSee('/assets/umami/newyear/', false);
        NewYearDecor::setEnabled(true);
        foreach (['', '/uk', '/en'] as $prefix) {
            foreach (['/', '/menu/rameny', '/menu/rameny/chashu-ramen'] as $path) {
                $this->get($prefix.$path)->assertOk()
                    ->assertSee('id="newyearDecor" aria-hidden="true" inert', false)
                    ->assertSee('/assets/umami/newyear/newyear.css', false)
                    ->assertSee('/assets/umami/newyear/newyear.js', false);
            }
        }
        NewYearDecor::setEnabled(false);
        foreach (['/', '/uk', '/en', '/menu/rameny'] as $path) {
            $this->get($path)->assertOk()->assertDontSee('/assets/umami/newyear/', false);
        }
    }

    public function test_sensitive_pages_never_load_new_year_assets(): void
    {
        $this->seed();
        NewYearDecor::setEnabled(true);
        foreach (['/koszyk', '/uk/koszyk', '/en/koszyk', '/regulamin', '/en/privacy-policy'] as $path) {
            $this->get($path)->assertOk()->assertDontSee('/assets/umami/newyear/', false);
        }
    }

    public function test_admin_can_switch_new_year_independently(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['role_id' => Role::where('is_admin', true)->sole()->id]));
        HalloweenDecor::setEnabled(true);
        $original = SiteSetting::where('key', '!=', NewYearDecor::KEY)->orderBy('id')->get()->toArray();
        foreach (['pl', 'uk', 'en'] as $locale) {
            app()->setLocale($locale);
            Livewire::test(HolidayDecorations::class)
                ->assertSee(__('holidays.names.newyear'))
                ->call('setHoliday', 'newyear', true)->assertHasNoErrors();
            $this->assertTrue(NewYearDecor::enabled());
            Livewire::test(HolidayDecorations::class)->call('setHoliday', 'newyear', false);
            $this->assertFalse(NewYearDecor::enabled());
        }
        $this->assertTrue(HalloweenDecor::enabled());
        $this->assertSame(1, SiteSetting::where('key', NewYearDecor::KEY)->count());
        $this->assertSame($original, SiteSetting::where('key', '!=', NewYearDecor::KEY)->orderBy('id')->get()->toArray());
    }

    public function test_read_only_role_cannot_enable_new_year(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $role = Role::create(['name' => 'Read holidays', 'permissions' => ['site_settings.view']]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));
        Livewire::test(HolidayDecorations::class)->call('setHoliday', 'newyear', true)->assertForbidden();
        $this->assertFalse(NewYearDecor::enabled());
    }
}
