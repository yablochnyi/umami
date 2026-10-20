<?php

namespace Tests\Feature;

use App\Filament\Pages\HolidayDecorations;
use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\HalloweenDecor;
use App\Support\NewYearDecor;
use App\Support\WomensDayDecor;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WomensDayDecorTest extends TestCase
{
    use RefreshDatabase;

    public function test_womens_day_is_opt_in_on_all_storefront_languages(): void
    {
        $this->seed();
        $this->assertFalse(WomensDayDecor::enabled());
        $this->get('/')->assertOk()->assertDontSee('/assets/umami/womens-day/', false);
        WomensDayDecor::setEnabled(true);
        foreach (['', '/uk', '/en'] as $prefix) {
            foreach (['/', '/menu/rameny', '/menu/rameny/chashu-ramen'] as $path) {
                $this->get($prefix.$path)->assertOk()
                    ->assertSee('id="womensDayDecor"', false)
                    ->assertSee('aria-hidden="true" inert', false)
                    ->assertSee('/assets/umami/womens-day/womens-day.css', false)
                    ->assertSee('/assets/umami/womens-day/womens-day.js', false);
            }
        }
        WomensDayDecor::setEnabled(false);
        foreach (['/', '/uk', '/en', '/menu/rameny'] as $path) {
            $this->get($path)->assertOk()->assertDontSee('/assets/umami/womens-day/', false);
        }
    }

    public function test_sensitive_pages_do_not_load_decoration_assets(): void
    {
        $this->seed();
        WomensDayDecor::setEnabled(true);
        foreach (['/koszyk', '/uk/koszyk', '/en/koszyk', '/regulamin', '/en/privacy-policy'] as $path) {
            $this->get($path)->assertOk()->assertDontSee('/assets/umami/womens-day/', false);
        }
    }

    public function test_admin_switches_womens_day_without_changing_other_holidays(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['role_id' => Role::where('is_admin', true)->sole()->id]));
        HalloweenDecor::setEnabled(true);
        NewYearDecor::setEnabled(true);
        $original = SiteSetting::where('key', '!=', WomensDayDecor::KEY)->orderBy('id')->get()->toArray();
        foreach (['pl', 'uk', 'en'] as $locale) {
            app()->setLocale($locale);
            Livewire::test(HolidayDecorations::class)
                ->assertSee(__('holidays.names.womens_day'))
                ->call('setHoliday', 'womens_day', true)->assertHasNoErrors();
            $this->assertTrue(WomensDayDecor::enabled());
            Livewire::test(HolidayDecorations::class)->call('setHoliday', 'womens_day', false);
            $this->assertFalse(WomensDayDecor::enabled());
        }
        $this->assertSame(1, SiteSetting::where('key', WomensDayDecor::KEY)->count());
        $this->assertSame($original, SiteSetting::where('key', '!=', WomensDayDecor::KEY)->orderBy('id')->get()->toArray());
    }

    public function test_read_only_role_cannot_enable_womens_day(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $role = Role::create(['name' => 'Read holidays', 'permissions' => ['site_settings.view']]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));
        Livewire::test(HolidayDecorations::class)->call('setHoliday', 'womens_day', true)->assertForbidden();
        $this->assertFalse(WomensDayDecor::enabled());
    }
}
