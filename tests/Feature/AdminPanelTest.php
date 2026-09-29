<?php

namespace Tests\Feature;

use App\Filament\Pages\SiteSettings;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Filament\Resources\Customers\RelationManagers\OrdersRelationManager;
use App\Filament\Resources\MenuItems\Pages\EditMenuItem;
use App\Filament\Resources\MenuItems\Pages\ListMenuItems;
use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\Customer;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    private function administrator(): User
    {
        return User::factory()->create(['role_id' => Role::where('is_admin', true)->sole()->id]);
    }

    private function staff(array $permissions): User
    {
        $role = Role::create(['name' => 'Staff '.Role::count(), 'permissions' => $permissions]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function dish(): MenuItem
    {
        $category = MenuCategory::create(['name' => ['pl' => 'Ramen', 'uk' => 'Рамен', 'en' => 'Ramen'], 'slug' => 'ramen', 'is_active' => true]);

        return MenuItem::create(['name' => ['pl' => 'Miso ramen', 'uk' => 'Місо рамен', 'en' => 'Miso ramen'], 'slug' => 'miso-ramen', 'price' => '39', 'menu_category_id' => $category->id, 'is_active' => true]);
    }

    public function test_unassigned_account_cannot_enter_panel_even_with_old_admin_email(): void
    {
        $user = User::factory()->create(['email' => config('app.admin_email')]);
        $this->actingAs($user)->get('/admin')->assertForbidden();
        $this->get('/admin/roles')->assertForbidden();
    }

    public function test_grant_command_changes_only_target_role_and_preserves_password(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $password = $user->password;
        $this->artisan('admin:grant', ['email' => $user->email])->assertSuccessful();
        $this->assertTrue($user->fresh()->isAdministrator());
        $this->assertSame($password, $user->fresh()->password);
        $this->assertNull($other->fresh()->role_id);
        $this->artisan('admin:grant', ['email' => 'missing@example.test'])->assertFailed();
    }

    public function test_migration_preserves_access_only_for_configured_administrator(): void
    {
        $migration = require database_path('migrations/2026_09_28_000001_add_admin_roles.php');
        $migration->down();
        $owner = User::factory()->create(['email' => config('app.admin_email')]);
        $other = User::factory()->create();
        $password = $owner->password;
        $migration->up();
        $this->assertTrue($owner->fresh()->isAdministrator());
        $this->assertNull($other->fresh()->role_id);
        $this->assertSame($password, $owner->fresh()->password);
    }

    public function test_admin_routes_render_in_both_languages(): void
    {
        $user = $this->administrator();
        foreach (['pl', 'uk'] as $locale) {
            $user->forceFill(['admin_locale' => $locale])->save();
            $this->actingAs($user);
            foreach (['', '/menu-items', '/menu-categories', '/orders', '/customers', '/gallery-images', '/site-texts', '/social-links', '/site-settings', '/ustawienia-strony', '/users', '/roles', '/roles/create', '/users/create', '/profile'] as $path) {
                $this->get('/admin'.$path)->assertOk()->assertSee('lang="'.$locale.'"', false);
            }
        }
    }

    public function test_view_only_role_can_read_but_not_modify_or_access_other_resources(): void
    {
        $dish = $this->dish();
        $user = $this->staff(['menu_items.view']);
        $this->actingAs($user)->get('/admin/menu-items')->assertOk();
        $this->get('/admin/menu-items/'.$dish->id)->assertOk();
        $this->get('/admin/menu-items/'.$dish->id.'/edit')->assertForbidden();
        $this->get('/admin/menu-items/create')->assertForbidden();
        $this->get('/admin/orders')->assertForbidden();
        $this->get('/admin/roles')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
        $this->assertFalse(Gate::forUser($user)->allows('update', $dish));
        $this->assertFalse(Gate::forUser($user)->allows('deleteAny', MenuItem::class));
        Livewire::test(EditMenuItem::class, ['record' => $dish->id])->assertForbidden();
        Livewire::test(ListMenuItems::class)->assertTableActionHidden('edit', $dish)->assertTableActionVisible('view', $dish);
    }

    public function test_update_permissions_do_not_grant_create_delete_or_security_access(): void
    {
        $dish = $this->dish();
        $user = $this->staff(['menu_items.update']);
        $this->actingAs($user);
        $this->assertTrue(Gate::allows('view', $dish));
        $this->assertTrue(Gate::allows('update', $dish));
        $this->assertFalse(Gate::allows('create', MenuItem::class));
        $this->assertFalse(Gate::allows('delete', $dish));
        $this->assertFalse(Gate::allows('create', Role::class));
        Livewire::test(EditMenuItem::class, ['record' => $dish->id])->fillForm(['price' => '42'])->call('save')->assertHasNoFormErrors();
        $this->assertSame('42', $dish->fresh()->price);
    }

    public function test_settings_view_permission_cannot_be_used_to_invoke_save(): void
    {
        $this->actingAs($this->staff(['site_settings.view']));
        $this->get('/admin/ustawienia-strony')->assertOk();
        Livewire::test(SiteSettings::class)->call('save')->assertForbidden();
        $this->assertDatabaseMissing('site_settings', ['key' => 'opening_time']);
    }

    public function test_roles_are_created_with_scoped_permissions_and_users_can_be_assigned(): void
    {
        $admin = $this->administrator();
        $this->actingAs($admin);
        Livewire::test(CreateRole::class)->fillForm(['name' => 'Kitchen', 'permission_groups' => ['menu_items' => ['update'], 'orders' => ['view']]])
            ->call('create')->assertHasNoFormErrors();
        $role = Role::where('name', 'Kitchen')->sole();
        $this->assertEqualsCanonicalizing(['menu_items.view', 'menu_items.update', 'orders.view'], $role->permissions);
        $this->assertFalse($role->is_admin);
        Livewire::test(CreateUser::class)->fillForm(['name' => 'Kitchen user', 'email' => 'kitchen@example.test', 'password' => 'Secure-Test-Password-42', 'role_id' => $role->id, 'admin_locale' => 'uk'])
            ->call('create')->assertHasNoFormErrors();
        $user = User::where('email', 'kitchen@example.test')->sole();
        $this->assertSame($role->id, $user->role_id);
        $this->assertTrue(Hash::check('Secure-Test-Password-42', $user->password));
    }

    public function test_admin_cannot_remove_own_role_or_delete_system_role(): void
    {
        $admin = $this->administrator();
        $staff = Role::create(['name' => 'Read only', 'permissions' => ['menu_items.view']]);
        $this->actingAs($admin);
        Livewire::test(EditUser::class, ['record' => $admin->id])->fillForm(['role_id' => $staff->id])->call('save')->assertHasNoFormErrors();
        $this->assertTrue($admin->fresh()->isAdministrator());
        $this->assertFalse(Gate::allows('delete', $admin));
        $this->assertFalse(Gate::allows('update', $admin->role));
        $this->assertFalse(Gate::allows('delete', $admin->role));
        $this->assertFalse(Gate::allows('deleteAny', User::class));
    }

    public function test_assigned_roles_cannot_be_deleted_and_unknown_permissions_are_rejected(): void
    {
        $admin = $this->administrator();
        $staff = $this->staff(['orders.view']);
        $this->assertFalse(Gate::forUser($admin)->allows('delete', $staff->role));
        $this->expectException(ValidationException::class);
        Role::create(['name' => 'Escalation', 'permissions' => ['roles.update']]);
    }

    public function test_last_administrator_is_protected_at_model_level(): void
    {
        $admin = $this->administrator();
        $this->expectException(ValidationException::class);
        $admin->forceFill(['role_id' => null])->save();
    }

    public function test_customer_relation_does_not_expose_orders_without_permission(): void
    {
        $this->actingAs($this->staff(['customers.view']));
        $customer = new Customer;
        $this->assertFalse(OrdersRelationManager::canViewForRecord($customer, ViewCustomer::class));
    }

    public function test_role_revocation_applies_to_open_livewire_edit_forms(): void
    {
        $dish = $this->dish();
        $user = $this->staff(['menu_items.update']);
        $this->actingAs($user);
        $form = Livewire::test(EditMenuItem::class, ['record' => $dish->id]);
        $user->role->update(['permissions' => ['menu_items.view']]);
        $user->unsetRelation('role');
        $form->call('save')->assertForbidden();
    }

    public function test_language_is_saved_and_used_after_reload_with_safe_redirect(): void
    {
        $admin = $this->administrator();
        $this->actingAs($admin)->from('/admin/roles')->post('/admin/language', ['locale' => 'uk'])->assertRedirect('/admin/roles');
        $this->assertSame('uk', $admin->fresh()->admin_locale);
        $this->get('/admin/roles')->assertOk()->assertSee('Ролі та права');
        $this->post('/admin/language', ['locale' => 'ru'])->assertSessionHasErrors('locale');
        $this->from('https://evil.example/admin')->post('/admin/language', ['locale' => 'pl'])->assertRedirect('/admin');
    }

    public function test_guest_login_is_localized_and_validation_messages_are_translated(): void
    {
        $this->withSession(['admin_locale' => 'uk'])->get('/admin/login')->assertOk()->assertSee('lang="uk"', false);
        foreach (['pl', 'uk'] as $locale) {
            app()->setLocale($locale);
            $this->assertNotSame('The :attribute field is required.', __('validation.required'));
            $this->assertNotSame('These credentials do not match our records.', __('auth.failed'));
        }
    }

    public function test_dashboard_does_not_leak_order_information_to_menu_only_staff(): void
    {
        $this->actingAs($this->staff(['menu_items.view']))->get('/admin')->assertOk()->assertDontSee('Ostatnie zamówienia ze strony');
    }
}
