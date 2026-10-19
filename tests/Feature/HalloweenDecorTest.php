<?php

namespace Tests\Feature;

use App\Filament\Pages\ActivityLog;
use App\Filament\Pages\HolidayDecorations;
use App\Models\AdminAuditLog;
use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\HalloweenDecor;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HalloweenDecorTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // Real Livewire update requests retain asset flags between PHPUnit requests.
        Livewire::flushState();
        parent::tearDown();
    }

    public function test_decor_is_opt_in_and_removes_all_assets_when_disabled(): void
    {
        $this->seed();
        $this->assertFalse(HalloweenDecor::enabled());
        $this->get('/')->assertOk()->assertDontSee('/assets/umami/halloween/', false);

        HalloweenDecor::setEnabled(true);
        $this->assertSame(16, substr_count($this->get('/')->assertOk()->getContent(), 'class="hw-leaf hw-leaf-'));
        foreach (['', '/uk', '/en'] as $prefix) {
            foreach (['/', '/menu/rameny', '/menu/rameny/chashu-ramen'] as $path) {
                $this->get($prefix.$path)->assertOk()
                    ->assertSee('data-halloween="true"', false)
                    ->assertSee('id="halloweenDecor" aria-hidden="true" inert', false)
                    ->assertSee('/assets/umami/halloween/halloween.css', false)
                    ->assertSee('/assets/umami/halloween/halloween.js', false);
            }
        }

        HalloweenDecor::setEnabled(false);
        foreach (['/', '/uk', '/en', '/menu/rameny'] as $path) {
            $this->get($path)->assertOk()->assertDontSee('/assets/umami/halloween/', false)
                ->assertDontSee('data-halloween=', false);
        }
    }

    public function test_checkout_and_legal_pages_stay_undecorated(): void
    {
        $this->seed();
        HalloweenDecor::setEnabled(true);
        foreach (['/koszyk', '/uk/koszyk', '/en/koszyk', '/regulamin', '/en/privacy-policy'] as $path) {
            $this->get($path)->assertOk()->assertDontSee('/assets/umami/halloween/', false);
        }
    }

    public function test_admin_switch_saves_in_one_action_and_preserves_other_settings(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['role_id' => Role::where('is_admin', true)->sole()->id]));
        $original = SiteSetting::where('key', '!=', HalloweenDecor::KEY)->orderBy('id')->get()->toArray();
        foreach (['pl', 'uk'] as $locale) {
            app()->setLocale($locale);
            Livewire::test(HolidayDecorations::class)
                ->assertSee(__('holidays.disabled'))
                ->call('setHoliday', 'halloween', true)
                ->assertSee(__('holidays.enabled'));
            $this->assertTrue(HalloweenDecor::enabled());
            Livewire::test(HolidayDecorations::class)->call('setHoliday', 'halloween', false);
            $this->assertFalse(HalloweenDecor::enabled());
        }
        $this->assertSame(1, SiteSetting::where('key', HalloweenDecor::KEY)->count());
        $this->assertSame($original, SiteSetting::where('key', '!=', HalloweenDecor::KEY)->orderBy('id')->get()->toArray());
    }

    public function test_read_only_staff_cannot_change_the_switch(): void
    {
        app()->setLocale('pl');
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $role = Role::create(['name' => 'Read settings', 'permissions' => ['site_settings.view']]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));
        Livewire::test(HolidayDecorations::class)->assertOk()
            ->call('setHoliday', 'halloween', true)->assertForbidden();
        $this->assertFalse(HalloweenDecor::enabled());
    }

    public function test_staff_without_permission_cannot_access_page(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $role = Role::create(['name' => 'No settings', 'permissions' => ['menu_items.view']]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));
        $this->assertFalse(HolidayDecorations::canAccess());
        $this->get('/admin/holiday-decorations')->assertForbidden();
        Livewire::test(HolidayDecorations::class)->assertForbidden();
    }

    public function test_unknown_holiday_cannot_write_arbitrary_settings(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['role_id' => Role::where('is_admin', true)->sole()->id]));
        $original = SiteSetting::orderBy('id')->get()->toArray();
        Livewire::test(HolidayDecorations::class)->call('setHoliday', 'goorder_enabled', true)->assertStatus(422);
        $this->assertSame($original, SiteSetting::orderBy('id')->get()->toArray());
    }

    public function test_page_view_and_switch_changes_are_audited(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $user = User::factory()->create(['role_id' => Role::where('is_admin', true)->sole()->id]);
        HalloweenDecor::setEnabled(false);
        $html = $this->actingAs($user)->get('/admin/holiday-decorations')->assertOk()->getContent();
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'viewed', 'resource' => 'holiday_decorations']);
        preg_match_all('/wire:snapshot="([^"]+)"/', $html, $matches);
        $snapshot = collect($matches[1])->map(fn ($value) => html_entity_decode($value, ENT_QUOTES))
            ->first(fn ($value) => str_contains(preg_replace('/[^a-z]/', '', strtolower(json_decode($value, true)['memo']['name'])), 'holidaydecorations'));
        $this->assertNotNull($snapshot);
        $this->postJson(route('default-livewire.update'), ['components' => [[
            'snapshot' => $snapshot, 'updates' => [],
            'calls' => [['path' => '', 'method' => 'setHoliday', 'params' => ['halloween', true]]],
        ]]], ['X-Livewire' => ''])->assertOk();
        $entry = AdminAuditLog::where('action', 'updated')->where('resource', 'site_settings')->latest('id')->firstOrFail();
        $this->assertSame($user->id, $entry->actor_id);
        $this->assertSame('Halloween', ActivityLog::subjectLabel($entry));
        $this->assertSame('1', $entry->changes['value']['after']);
    }
}
