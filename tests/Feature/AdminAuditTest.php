<?php

namespace Tests\Feature;

use App\Filament\Pages\ActivityLog;
use App\Models\AdminAuditLog;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\AdminAudit;
use App\Support\AdminPermissions;
use Filament\Facades\Filament;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        app()->setLocale('pl');
    }

    private function user(bool $admin = false): User
    {
        $role = $admin ? Role::where('is_admin', true)->sole() : Role::create(['name' => 'Marketing', 'permissions' => ['menu_items.update', 'site_texts.update']]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function context(User $user): void
    {
        $this->actingAs($user);
        request()->attributes->set('admin_audit_panel', true);
        request()->attributes->set('admin_audit_actor', app(AdminAudit::class)->actor($user));
    }

    private function dish(): MenuItem
    {
        $category = MenuCategory::create(['name' => ['pl' => 'Ramen'], 'slug' => 'ramen']);

        return MenuItem::create(['menu_category_id' => $category->id, 'name' => ['pl' => 'Miso', 'uk' => 'Місо', 'en' => 'Miso'], 'slug' => 'miso', 'price' => '39', 'is_active' => true]);
    }

    public function test_only_administrator_can_read_journal_and_no_role_permission_can_grant_it(): void
    {
        $admin = $this->user(true);
        $staff = $this->user();
        $this->get('/admin/activity-log')->assertRedirect('/admin/login');
        $this->actingAs($staff)->get('/admin/activity-log')->assertForbidden();
        Livewire::test(ActivityLog::class)->assertForbidden();
        $this->get('/admin/menu-items')->assertOk()->assertDontSee('/admin/activity-log');
        $this->actingAs($admin)->get('/admin/activity-log')->assertOk();
        $this->assertNotContains('activity_log.view', AdminPermissions::keys());
    }

    public function test_authenticated_views_capture_resource_record_and_not_query_parameters(): void
    {
        $dish = $this->dish();
        $user = $this->user();
        $this->actingAs($user)->get('/admin/menu-items/'.$dish->id.'/edit?token=do-not-store')->assertOk();
        $entry = AdminAuditLog::sole();
        $this->assertSame('viewed', $entry->action);
        $this->assertSame('edit', $entry->page);
        $this->assertSame((string) $dish->id, $entry->subject_id);
        $this->assertSame('Miso', $entry->subject_label);
        $this->assertSame($user->email, $entry->actor_email);
        $this->assertStringNotContainsString('do-not-store', $entry->toJson());
        $this->get('/admin/users')->assertForbidden();
        $this->get('/')->assertOk();
        $this->assertDatabaseCount('admin_audit_logs', 1);
    }

    public function test_login_logout_are_recorded_without_credentials_and_public_events_are_ignored(): void
    {
        $user = $this->user();
        event(new Login('web', $user, false));
        $this->assertDatabaseCount('admin_audit_logs', 0);
        $this->context($user);
        event(new Login('web', $user, false));
        event(new Logout('web', $user));
        $this->assertSame(['login', 'logout'], AdminAuditLog::orderBy('id')->pluck('action')->all());
        $this->assertStringNotContainsString($user->password, AdminAuditLog::get()->toJson());
    }

    public function test_updates_keep_original_and_new_translations_with_only_changed_fields(): void
    {
        $dish = $this->dish();
        $this->context($this->user());
        $dish->update(['price' => '42', 'name' => ['pl' => 'Nowe Miso', 'uk' => 'Нове місо']]);
        $changes = AdminAuditLog::sole()->changes;
        $this->assertEqualsCanonicalizing(['name', 'price'], array_keys($changes));
        $this->assertSame('39', $changes['price']['before']);
        $this->assertSame('42', $changes['price']['after']);
        $this->assertSame('Місо', $changes['name']['before']['uk']);
        $this->assertSame('Нове місо', $changes['name']['after']['uk']);
        $dish->save();
        $this->assertDatabaseCount('admin_audit_logs', 1);
    }

    public function test_secrets_and_integration_payloads_are_never_copied(): void
    {
        $dish = $this->dish();
        $user = $this->user(true);
        $secret = SiteSetting::create(['key' => 'gopos_client_secret', 'label' => 'Secret', 'value' => 'old-private-token']);
        $this->context($user);
        $user->update(['password' => 'NeverStoreThisPassword123!']);
        $secret->update(['value' => 'new-private-token']);
        $dish->update(['gopos_payload' => ['token' => 'hidden-in-payload']]);
        $this->assertDatabaseCount('admin_audit_logs', 2);
        $json = AdminAuditLog::get()->toJson();
        foreach (['NeverStoreThisPassword', 'old-private-token', 'new-private-token', 'hidden-in-payload', $user->password] as $secretValue) {
            $this->assertStringNotContainsString($secretValue, $json);
        }
        $this->assertTrue(AdminAuditLog::where('resource', 'users')->sole()->changes['password']['redacted']);
    }

    public function test_public_settings_show_changes_and_rolled_back_changes_leave_no_log(): void
    {
        $setting = SiteSetting::create(['key' => 'opening_time', 'label' => 'Opening', 'value' => '12:00']);
        $this->context($this->user(true));
        $setting->update(['value' => '13:00']);
        $this->assertSame('12:00', AdminAuditLog::sole()->changes['value']['before']);
        DB::beginTransaction();
        $setting->update(['value' => '14:00']);
        DB::rollBack();
        $this->assertSame('13:00', $setting->fresh()->value);
        $this->assertDatabaseCount('admin_audit_logs', 1);
    }

    public function test_create_delete_and_account_removal_preserve_actor_snapshot(): void
    {
        $user = $this->user();
        $this->context($user);
        $category = MenuCategory::create(['name' => ['pl' => 'Lunch'], 'slug' => 'lunch']);
        $category->delete();
        $user->delete();
        $this->assertSame(['created', 'deleted', 'deleted'], AdminAuditLog::orderBy('id')->pluck('action')->all());
        $this->assertSame($user->email, AdminAuditLog::oldest('id')->first()->actor_email);
        $this->assertDatabaseCount('admin_audit_logs', 3);
    }

    public function test_filters_use_actor_id_and_warsaw_day_boundaries(): void
    {
        $staff = $this->user();
        $admin = $this->user(true);
        $audit = app(AdminAudit::class);
        $one = AdminAuditLog::create([...$audit->actor($staff), 'action' => 'updated', 'resource' => 'menu_items', 'created_at' => '2026-09-28 22:00:00']);
        $other = AdminAuditLog::create([...$audit->actor($admin), 'action' => 'viewed', 'resource' => 'users', 'created_at' => '2026-09-29 10:00:00']);
        $previous = AdminAuditLog::create([...$audit->actor($staff), 'action' => 'updated', 'resource' => 'menu_items', 'created_at' => '2026-09-28 21:59:59']);
        $this->actingAs($admin);
        Livewire::test(ActivityLog::class)->filterTable('actor_id', $staff->id)
            ->filterTable('action', 'updated')->filterTable('resource', 'menu_items')
            ->filterTable('period', ['from' => '2026-09-29', 'to' => '2026-09-29'])
            ->assertCanSeeTableRecords([$one])->assertCanNotSeeTableRecords([$other, $previous]);
    }

    public function test_revocation_blocks_open_journal_and_stored_values_are_html_escaped(): void
    {
        $admin = $this->user(true);
        $this->actingAs($admin);
        $entry = AdminAuditLog::create([...app(AdminAudit::class)->actor($admin), 'action' => 'updated', 'resource' => 'site_texts', 'changes' => [
            'value' => ['before' => 'Old', 'after' => '<script>alert(1)</script>', 'redacted' => false],
        ]]);
        $page = Livewire::test(ActivityLog::class)->mountTableAction('details', $entry);
        $this->assertSame('details', $page->instance()->getMountedAction()->getName());
        $html = $page->instance()->getMountedAction()->getModalContent()->render();
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $replacement = $this->user(true);
        $admin->forceFill(['role_id' => $this->user()->role_id])->save();
        $page->call('$refresh')->assertForbidden();
    }

    public function test_background_changes_are_not_attributed_to_a_staff_account(): void
    {
        $dish = $this->dish();
        $this->actingAs($this->user());
        $dish->update(['price' => '44']);
        $this->assertDatabaseCount('admin_audit_logs', 0);
    }

    private function snapshot(string $html, string $component): string
    {
        preg_match_all('/wire:snapshot="([^"]+)"/', $html, $matches);
        foreach ($matches[1] as $candidate) {
            $snapshot = html_entity_decode($candidate, ENT_QUOTES);
            $name = preg_replace('/[^a-z]/', '', strtolower(json_decode($snapshot, true)['memo']['name']));
            if (str_contains($name, str_replace('-', '', $component))) {
                return $snapshot;
            }
        }
        throw new \RuntimeException('Missing component snapshot: '.$component);
    }

    public function test_real_livewire_request_records_save_once_without_polluting_view_history(): void
    {
        $dish = $this->dish();
        $user = $this->user();
        $html = $this->actingAs($user)->get('/admin/menu-items/'.$dish->id.'/edit')->assertOk()->getContent();
        $snapshot = $this->snapshot($html, 'edit-menu-item');
        $this->postJson(route('default-livewire.update'), ['components' => [[
            'snapshot' => $snapshot, 'updates' => ['data.price' => '45'],
            'calls' => [['path' => '', 'method' => 'save', 'params' => []]],
        ]]], ['X-Livewire' => ''])->assertOk();
        $this->assertSame('45', $dish->fresh()->price);
        $this->assertSame(1, AdminAuditLog::where('action', 'viewed')->count());
        $this->assertSame(1, AdminAuditLog::where('action', 'updated')->count());
        $this->assertSame($user->id, AdminAuditLog::where('action', 'updated')->sole()->actor_id);
    }

    public function test_real_panel_login_and_logout_are_audited(): void
    {
        $user = $this->user();
        $html = $this->get('/admin/login')->assertOk()->getContent();
        $this->postJson(route('default-livewire.update'), ['components' => [[
            'snapshot' => $this->snapshot($html, 'login'),
            'updates' => ['data.email' => $user->email, 'data.password' => 'password'],
            'calls' => [['path' => '', 'method' => 'authenticate', 'params' => []]],
        ]]], ['X-Livewire' => ''])->assertOk();
        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, AdminAuditLog::where('action', 'login')->count());
        $this->post('/admin/logout')->assertRedirect();
        $this->assertGuest();
        $this->assertSame(1, AdminAuditLog::where('action', 'logout')->count());
    }
}
