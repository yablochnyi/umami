<?php

namespace Tests\Feature;

use App\Filament\Resources\MenuCategories\Pages\EditMenuCategory;
use App\Filament\Resources\MenuItems\Pages\EditMenuItem;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\User;
use App\Services\GoOrder\GoOrderCatalog;
use App\Services\GoOrder\GoOrderMenuSynchronizer;
use App\Services\GoOrder\GoOrderModifiers;
use App\Services\GoOrder\GoOrderService;
use App\Services\GoOrder\MenuAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class GoOrderMenuSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['goorder.enabled' => true]);
        Http::preventStrayRequests();
    }

    private function catalog(): array
    {
        return [
            'menus' => [['id' => 2, 'reference_id' => config('goorder.menu_reference'), 'categories' => [['category_id' => 37, 'position' => 17]]]],
            'categories' => [['id' => 37, 'reference_id' => 'lunch-category', 'name' => 'LUNCH', 'status' => 'ENABLED', 'entities' => [['type' => 'ITEM', 'entity_id' => 109, 'position' => 0]]]],
            'items' => [['id' => 109, 'reference_id' => 'lunch-item', 'name' => 'Lunch Bowl kurczak', 'description' => 'Chicken', 'price' => ['amount' => 47, 'currency' => 'PLN'], 'status' => 'ENABLED', 'availability_id' => 3, 'modifier_groups' => []]],
            'item_groups' => [], 'modifier_groups' => [],
            'availabilities' => [['id' => 3, 'name' => 'Lunch', 'status' => 'ENABLED', 'dates' => [], 'hours' => [
                ['day_from' => 'MONDAY', 'day_to' => 'FRIDAY', 'hour_from' => '12:00', 'hour_to' => '16:00'],
            ]]],
        ];
    }

    private function sync(?array $catalog = null, bool $dryRun = false, bool $allowLargeRemoval = false): array
    {
        return app(GoOrderMenuSynchronizer::class)->sync($catalog ?? $this->catalog(), [], $dryRun, $allowLargeRemoval);
    }

    public function test_import_is_idempotent_and_preserves_all_editorial_fields_and_manual_disabling(): void
    {
        $this->assertSame(1, $this->sync()['items_created']);
        $item = MenuItem::first();
        $item->update(['name' => ['pl' => 'Our custom name', 'en' => 'Our translation'], 'description' => ['pl' => 'Our copy'], 'price' => '39 zł', 'slug' => 'custom-slug', 'image' => 'own.jpg', 'is_active' => false, 'sort_order' => 71, 'seo_title' => ['pl' => 'SEO'], 'schedule_enabled' => true, 'schedule_hours' => [['days' => [1], 'from' => '13:00', 'to' => '14:00']]]);
        $before = $item->fresh()->getRawOriginal();
        $this->assertSame(0, $this->sync()['items_created']);
        $after = $item->fresh()->getRawOriginal();
        foreach (['name', 'description', 'price', 'slug', 'image', 'is_active', 'sort_order', 'seo_title', 'schedule_enabled', 'schedule_hours', 'menu_category_id'] as $field) {
            $this->assertSame($before[$field], $after[$field], $field);
        }
        $this->assertDatabaseCount('menu_items', 1);
        $this->assertDatabaseCount('menu_categories', 1);
    }

    public function test_dry_run_rolls_back_categories_items_and_flags(): void
    {
        $this->assertSame(1, $this->sync(dryRun: true)['items_created']);
        $this->assertDatabaseCount('menu_items', 0);
        $this->assertDatabaseCount('menu_categories', 0);
        Http::assertNothingSent();
    }

    public function test_missing_dishes_are_hidden_but_manual_active_state_is_preserved(): void
    {
        $this->sync();
        $old = MenuItem::first();
        $catalog = $this->catalog();
        $catalog['items'][0]['id'] = 110;
        $catalog['items'][0]['reference_id'] = 'replacement';
        $catalog['items'][0]['name'] = 'New Lunch';
        $catalog['categories'][0]['entities'][0]['entity_id'] = 110;
        $this->sync($catalog, allowLargeRemoval: true);
        $this->assertFalse($old->fresh()->goorder_published);
        $this->assertTrue($old->fresh()->is_active);
        $this->assertCount(1, MenuItem::visible()->get());
        $this->get('/menu/lunch/'.$old->slug)->assertNotFound();
        $this->sync(allowLargeRemoval: true);
        $this->assertTrue($old->fresh()->goorder_published);
        $this->assertDatabaseCount('menu_items', 2);
    }

    public function test_partial_or_empty_catalog_never_unpublishes_items(): void
    {
        $this->sync();
        $bad = $this->catalog();
        $bad['items'] = [];
        try {
            $this->sync($bad);
            $this->fail('Expected validation failure.');
        } catch (RuntimeException) {
            $this->assertTrue(MenuItem::first()->goorder_published);
        }
    }

    public function test_large_removal_aborts_atomically(): void
    {
        $this->sync();
        $bad = $this->catalog();
        $bad['categories'][0]['status'] = 'DISABLED';
        try {
            $this->sync($bad);
            $this->fail('Expected removal guard.');
        } catch (RuntimeException) {
            $this->assertTrue(MenuItem::first()->goorder_published);
            $this->assertTrue(MenuCategory::first()->goorder_published);
        }
    }

    public function test_modifier_only_items_are_not_imported(): void
    {
        $catalog = $this->catalog();
        $catalog['items'][] = ['id' => 999, 'name' => 'Sauce', 'reference_id' => 'sauce'];
        $this->sync($catalog);
        $this->assertDatabaseCount('menu_items', 1);
    }

    public function test_category_exclusion_survives_sync_and_blocks_ordering(): void
    {
        $this->sync();
        MenuCategory::first()->update(['goorder_import_enabled' => false]);
        $this->sync(allowLargeRemoval: true);
        $this->assertFalse(MenuCategory::first()->goorder_import_enabled);
        $this->assertCount(0, MenuItem::visible()->get());
        $this->assertFalse(app(MenuAvailability::class)->check(MenuItem::first(), CarbonImmutable::parse('2026-09-28 13:00', 'Europe/Warsaw')));
    }

    public function test_lunch_boundaries_weekends_and_warsaw_timezone(): void
    {
        $this->sync();
        $item = MenuItem::first();
        $availability = app(MenuAvailability::class);
        foreach (['2026-09-28 11:59' => false, '2026-09-28 12:00' => true, '2026-09-28 15:59' => true, '2026-09-28 16:00' => false, '2026-09-26 13:00' => false] as $time => $expected) {
            $this->assertSame($expected, $availability->check($item, CarbonImmutable::parse($time, 'Europe/Warsaw')), $time);
        }
        $this->assertTrue($availability->check($item, CarbonImmutable::parse('2026-09-28 10:00', 'UTC')));
        $this->assertTrue($availability->check($item, CarbonImmutable::parse('2026-10-26 11:00', 'UTC')));
    }

    public function test_local_category_and_item_hours_can_only_narrow_goorder_hours(): void
    {
        $this->sync();
        $item = MenuItem::first();
        $item->category->update(['schedule_enabled' => true, 'schedule_hours' => [['days' => [1], 'from' => '13:00', 'to' => '17:00']]]);
        $item->update(['schedule_enabled' => true, 'schedule_hours' => [['days' => [1], 'from' => '14:00', 'to' => '18:00']]]);
        foreach (['13:00' => false, '14:00' => true, '16:00' => false] as $time => $expected) {
            $this->assertSame($expected, app(MenuAvailability::class)->check($item, CarbonImmutable::parse('2026-09-28 '.$time, 'Europe/Warsaw')));
        }
    }

    public function test_overnight_local_window_includes_previous_day(): void
    {
        $hours = [['days' => [5], 'from' => '22:00', 'to' => '02:00']];
        $service = app(MenuAvailability::class);
        $this->assertTrue($service->within($hours, CarbonImmutable::parse('2026-09-26 01:59', 'Europe/Warsaw')));
        $this->assertFalse($service->within($hours, CarbonImmutable::parse('2026-09-26 02:00', 'Europe/Warsaw')));
    }

    private function checkoutData(MenuItem $item): array
    {
        return ['submission_key' => (string) Str::uuid(), 'cart_json' => json_encode([['id' => $item->id, 'quantity' => 1]]),
            'name' => 'Test', 'email' => 'test@example.com', 'phone' => '+48000000000', 'delivery_type' => 'pickup',
            'fulfillment_type' => 'asap', 'payment_type' => 'cash'];
    }

    public function test_checkout_rejects_stale_cart_outside_lunch_hours_without_external_calls(): void
    {
        $this->sync();
        $this->travelTo(CarbonImmutable::parse('2026-09-28 16:00', 'Europe/Warsaw'));
        $data = $this->checkoutData(MenuItem::first());
        $this->withSession(['checkout_submission_key' => $data['submission_key']])->from('/koszyk')->post('/koszyk', $data)
            ->assertRedirect('/koszyk')->assertSessionHas('checkout_error');
        $this->assertDatabaseCount('orders', 0);
        Http::assertNothingSent();
    }

    public function test_checkout_uses_requested_fulfillment_time_for_scheduled_lunch(): void
    {
        $this->sync();
        $this->travelTo(CarbonImmutable::parse('2026-09-26 18:00', 'Europe/Warsaw'));
        $this->mock(GoOrderService::class)->shouldReceive('send')->once();
        $data = array_merge($this->checkoutData(MenuItem::first()), ['fulfillment_type' => 'scheduled', 'scheduled_day' => '2026-09-28', 'scheduled_time' => '13:00']);
        $this->withSession(['checkout_submission_key' => $data['submission_key']])->post('/koszyk', $data)->assertRedirect();
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_availability_endpoint_respects_scheduled_time_without_mutation(): void
    {
        $this->sync();
        $id = MenuItem::first()->id;
        $this->getJson('/api/menu-availability?day=2026-09-28&time=13:00&locale=en')->assertOk()->assertJsonPath('items.'.$id.'.available', true)->assertHeader('Cache-Control', 'no-store, private');
        $this->getJson('/api/menu-availability?day=2026-09-26&time=13:00')->assertOk()->assertJsonPath('items.'.$id.'.available', false);
        $this->getJson('/api/menu-availability?day=bad&time=13:00')->assertUnprocessable();
        Http::assertNothingSent();
    }

    public function test_required_sauce_is_validated_and_payload_uses_selected_items(): void
    {
        $service = app(GoOrderModifiers::class);
        $choices = [['id' => 1, 'name' => 'Sauce', 'options' => [['id' => 113, 'name' => 'Chili', 'reference_id' => 'chili']]]];
        $selected = $service->validate($choices, [1 => 113]);
        $this->assertSame([['modifier_group_id' => 1, 'selected_items' => [['item_id' => 113, 'quantity' => 1, 'modifier_groups' => []]]]], $service->payload($selected));
        $this->expectException(ValidationException::class);
        $service->validate($choices, [1 => 999]);
    }

    public function test_unknown_schedule_format_aborts_sync(): void
    {
        $data = $this->catalog();
        $data['availabilities'][0]['dates'] = [['unknown' => true]];
        $this->expectException(RuntimeException::class);
        app(GoOrderCatalog::class)->parse($data);
    }

    public function test_admin_can_edit_category_and_item_hours(): void
    {
        $this->sync();
        $this->actingAs(User::factory()->create());
        $item = MenuItem::first();
        $hours = [['days' => [1, 2, 3], 'from' => '12:30', 'to' => '15:30']];
        Livewire::test(EditMenuItem::class, ['record' => $item->id])
            ->fillForm(['schedule_enabled' => true, 'schedule_hours' => $hours])->call('save')->assertHasNoFormErrors();
        $this->assertTrue($item->fresh()->schedule_enabled);
        $this->assertSame($hours, $item->fresh()->schedule_hours);
        Livewire::test(EditMenuCategory::class, ['record' => $item->menu_category_id])
            ->fillForm(['schedule_enabled' => true, 'schedule_hours' => $hours])->call('save')->assertHasNoFormErrors();
        $this->assertTrue($item->category->fresh()->schedule_enabled);
    }

    public function test_image_contents_are_validated_independently_of_nonstandard_mime_header(): void
    {
        Storage::fake('public');
        $data = $this->catalog();
        $data['items'][0]['image_link']['default'] = 'https://test.cloudfront.net/image';
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jSZkAAAAASUVORK5CYII=');
        Http::fake(['https://test.cloudfront.net/image' => Http::response($png, 200, ['Content-Type' => 'application/octet-stream'])]);
        $this->sync($data);
        Storage::disk('public')->assertExists(MenuItem::first()->image);
    }

    public function test_invalid_image_rolls_back_new_dishes_and_publication(): void
    {
        $data = $this->catalog();
        $data['items'][0]['image_link']['default'] = 'https://test.cloudfront.net/image';
        Http::fake(['https://test.cloudfront.net/image' => Http::response('<html>error</html>', 200, ['Content-Type' => 'image/jpeg'])]);
        try {
            $this->sync($data);
            $this->fail('Expected invalid image.');
        } catch (\Throwable) {
            $this->assertDatabaseCount('menu_items', 0);
            $this->assertDatabaseCount('menu_categories', 0);
        }
    }

    public function test_command_network_failure_leaves_menu_untouched(): void
    {
        $this->sync();
        config(['goorder.base_url' => 'https://store.example', 'gopos.organization_id' => null]);
        Http::fake(['https://store.example/api/config/menus' => Http::response([], 503)]);
        $this->artisan('goorder:sync-menu')->assertFailed();
        $this->assertTrue(MenuItem::first()->goorder_published);
    }

    public function test_unpublished_item_cannot_be_ordered_from_an_old_cart(): void
    {
        $this->sync();
        $item = MenuItem::first();
        $item->update(['goorder_published' => false]);
        $data = $this->checkoutData($item);
        $this->withSession(['checkout_submission_key' => $data['submission_key']])->from('/koszyk')->post('/koszyk', $data)
            ->assertRedirect('/koszyk')->assertSessionHas('checkout_error');
        $this->assertDatabaseCount('orders', 0);
        Http::assertNothingSent();
    }

    public function test_lunch_requires_a_sauce_and_forwards_the_selected_option_to_goorder(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-09-28 13:00', 'Europe/Warsaw'));
        config(['goorder.base_url' => 'https://store.example']);
        $catalog = $this->catalog();
        $catalog['items'][0]['modifier_groups'] = [['modifier_group_id' => 1]];
        $catalog['items'][] = ['id' => 113, 'reference_id' => 'chili', 'name' => 'Chili', 'status' => 'ENABLED', 'price' => ['amount' => 0, 'currency' => 'PLN'], 'modifier_groups' => []];
        $catalog['modifier_groups'] = [['id' => 1, 'name' => 'Sauce', 'status' => 'ENABLED', 'quantity_info' => ['min_permitted' => 1, 'max_permitted' => 1], 'options' => [['type' => 'ITEM', 'entity_id' => 113]]]];
        $catalog['payment_methods'] = [['id' => 2, 'status' => 'ENABLED', 'settings' => [], 'reference_id' => config('goorder.payment_references.cash')]];
        $this->sync($catalog);
        $item = MenuItem::first();
        $data = $this->checkoutData($item);
        $this->withSession(['checkout_submission_key' => $data['submission_key']])->from('/koszyk')->post('/koszyk', $data)
            ->assertRedirect('/koszyk')->assertSessionHasErrors('cart_json');
        $this->assertDatabaseCount('orders', 0);
        Http::assertNothingSent();
        $remote = ['id' => 'lunch-order', 'token' => 'test-token', 'status' => 'OPEN',
            'total_money' => ['amount' => 47, 'currency' => 'PLN'], 'delivery_fee_money' => ['amount' => 0, 'currency' => 'PLN'],
            'items' => [['item_id' => 109, 'name' => 'Lunch Bowl kurczak', 'quantity' => 1, 'total_money' => ['amount' => 47, 'currency' => 'PLN']]]];
        Http::fake([
            'store.example/api/config' => Http::response(['data' => ['ordering' => ['mode' => 'ENABLED', 'takeaway' => true, 'delivery' => true, 'rules' => [], 'payment_methods' => [['id' => 2]]]]]),
            'store.example/api/config/menus' => Http::response(['data' => $catalog]),
            'store.example/api/orders/lunch-order/pay' => Http::response(['data' => array_merge($remote, ['status' => 'WAITING_FOR_ACCEPTED'])]),
            'store.example/api/orders*' => Http::response(['data' => $remote]),
        ]);
        $data['cart_json'] = json_encode([['id' => $item->id, 'quantity' => 1, 'modifiers' => [1 => 113]]]);
        $this->withSession(['checkout_submission_key' => $data['submission_key']])->post('/koszyk', $data)->assertRedirect();
        $this->assertDatabaseHas('orders', ['status' => 'waiting_staff']);
        Http::assertSent(fn ($request) => $request->url() === 'https://store.example/api/orders'
            && $request['items'][0]['modifier_groups'][0]['selected_items'][0]['item_id'] === 113);
    }
}
