<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Services\GoOrder\GoOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class GoOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['goorder.enabled' => true, 'goorder.base_url' => 'https://store.example']);
        Http::preventStrayRequests();
    }

    private function order(): Order
    {
        $category = MenuCategory::create(['name' => ['pl' => 'Rameny'], 'slug' => 'rameny']);
        $item = MenuItem::create([
            'menu_category_id' => $category->id, 'name' => ['pl' => 'Bifu Ramen'],
            'price' => '58', 'goorder_id' => 6, 'goorder_reference_id' => 'bifu-reference',
        ]);
        $customer = Customer::create(['name' => 'Test customer', 'phone' => '+48000000000', 'email' => 'test@example.com']);
        $order = Order::create([
            'customer_id' => $customer->id, 'number' => 'TEST-'.Str::random(10), 'status' => 'new',
            'tracking_token' => Str::random(64), 'submission_key' => (string) Str::uuid(),
            'delivery_type' => 'pickup', 'fulfillment_type' => 'asap', 'payment_type' => 'cash',
            'subtotal' => 58, 'total' => 58, 'locale' => 'pl',
        ]);
        $order->items()->create(['menu_item_id' => $item->id, 'name' => 'Bifu Ramen', 'quantity' => 1, 'unit_price' => 58, 'total' => 58]);

        return $order;
    }

    private function remote(string $status = 'OPEN', int $amount = 58): array
    {
        return [
            'id' => 'remote-order', 'token' => 'private-remote-token', 'status' => $status,
            'total_money' => ['amount' => $amount, 'currency' => 'PLN'],
            'delivery_fee_money' => ['amount' => 0, 'currency' => 'PLN'],
            'items' => [['item_id' => 6, 'name' => 'Bifu Ramen', 'quantity' => 1, 'total_money' => ['amount' => $amount, 'currency' => 'PLN']]],
        ];
    }

    private function fake(int $amount = 58, mixed $pay = null, array $menuOverrides = []): void
    {
        Http::fake([
            'store.example/api/config' => Http::response(['data' => ['ordering' => [
                'mode' => 'ENABLED', 'delivery' => true, 'takeaway' => true, 'rules' => [],
                'payment_methods' => [['id' => 2], ['id' => 3]],
            ]]]),
            'store.example/api/config/menus' => Http::response(['data' => [
                'menus' => [['id' => 2, 'reference_id' => config('goorder.menu_reference'), 'categories' => [['category_id' => 1]]]],
                'categories' => [['id' => 1, 'reference_id' => 'rameny', 'name' => 'Rameny', 'status' => 'ENABLED', 'entities' => [['type' => 'ITEM', 'entity_id' => 6]]]],
                'availabilities' => [], 'item_groups' => [], 'modifier_groups' => [],
                'payment_methods' => [
                ['id' => 2, 'status' => 'ENABLED', 'settings' => [], 'reference_id' => config('goorder.payment_references.cash')],
                ['id' => 3, 'status' => 'ENABLED', 'settings' => [], 'reference_id' => config('goorder.payment_references.card')],
            ], 'items' => [array_replace([
                'id' => 6, 'reference_id' => 'bifu-reference', 'name' => 'Bifu Ramen', 'status' => 'ENABLED', 'modifier_groups' => [], 'price' => ['amount' => 58, 'currency' => 'PLN'],
            ], $menuOverrides)]]]),
            'store.example/api/orders' => Http::response(['data' => $this->remote('OPEN', $amount)]),
            'store.example/api/orders/remote-order/pay' => $pay ?? Http::response(['data' => $this->remote('WAITING_FOR_ACCEPTED', $amount)]),
            'store.example/api/orders/remote-order*' => Http::response(['data' => $this->remote('OPEN', $amount)]),
        ]);
    }

    public function test_sends_through_goorder_once_and_waits_for_staff(): void
    {
        $this->fake();
        $order = $this->order();
        $service = app(GoOrderService::class);
        $service->send($order);
        $service->send($order);
        $this->assertSame('waiting_staff', $order->fresh()->status);
        $this->assertNull($order->fresh()->expected_ready_at);
        Http::assertSentCount(5);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/pay')
            && $request['payment_method_id'] === 2 && ! isset($request['items'])
            && $request->hasHeader('order-token', 'private-remote-token'));
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'gopos'));
        $raw = DB::table('orders')->find($order->id);
        $this->assertStringNotContainsString('private-remote-token', $raw->goorder_token);
        $this->assertStringNotContainsString('test@example.com', $raw->goorder_checkout);
    }

    public function test_price_change_requires_customer_confirmation(): void
    {
        $this->fake(62);
        $order = $this->order();
        $service = app(GoOrderService::class);
        $service->send($order);
        $this->assertSame('quote_review', $order->fresh()->status);
        Http::assertNotSent(fn ($request) => str_ends_with($request->url(), '/pay'));
        $service->confirm($order, $service->quoteHash($order->fresh()->goorder_quote));
        $this->assertSame('waiting_staff', $order->fresh()->status);
        $this->assertSame('62.00', $order->fresh()->total);
        $this->assertSame('62.00', $order->items()->first()->unit_price);
        $service->confirm($order, $service->quoteHash($order->fresh()->goorder_quote));
        $this->assertCount(1, Http::recorded(fn ($request) => str_ends_with($request->url(), '/pay')));
    }

    public function test_changed_quote_after_review_is_not_silently_accepted(): void
    {
        $this->fake(62);
        $order = $this->order();
        $service = app(GoOrderService::class);
        $service->send($order);
        $oldHash = $service->quoteHash($order->fresh()->goorder_quote);
        Http::swap(new Factory);
        Http::fake(['store.example/api/orders/remote-order*' => Http::response(['data' => $this->remote('OPEN', 65)])]);
        $service->confirm($order, $oldHash);
        $this->assertSame('quote_review', $order->fresh()->status);
        $this->assertSame(6500, $order->fresh()->goorder_quote['total']);
        $this->assertNull($order->fresh()->goorder_submit_started_at);
    }

    public function test_lost_submission_response_never_retries_pay_and_can_recover(): void
    {
        $this->fake(pay: fn () => throw new ConnectionException('timeout with secret'));
        $order = $this->order();
        $service = app(GoOrderService::class);
        $service->send($order);
        $this->assertSame('submission_uncertain', $order->fresh()->status);
        $service->send($order);
        $this->assertSame('goorder_connection_error', $order->fresh()->goorder_error);
        Http::swap(new Factory);
        Http::fake(['store.example/api/orders/remote-order*' => Http::response(['data' => $this->remote('WAITING_FOR_ACCEPTED')])]);
        $service->sync($order);
        $this->assertSame('waiting_staff', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->goorder_submit_started_at);
    }

    public function test_missing_mapping_and_modifiers_fail_before_remote_creation(): void
    {
        $this->fake(menuOverrides: ['modifier_groups' => [['modifier_group_id' => 1]]]);
        $order = $this->order();
        app(GoOrderService::class)->send($order);
        $this->assertSame('goorder_modifiers_require_setup', $order->fresh()->goorder_error);
        Http::assertNotSent(fn ($request) => $request->method() === 'POST');
    }

    public function test_reference_change_is_blocked(): void
    {
        $this->fake(menuOverrides: ['reference_id' => 'different-dish']);
        $order = $this->order();
        app(GoOrderService::class)->send($order);
        $this->assertSame('goorder_item_unmapped', $order->fresh()->goorder_error);
        Http::assertNotSent(fn ($request) => $request->method() === 'POST');
    }

    public function test_accepted_deadline_survives_reload_and_missing_eta_updates(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 25)->setTime(10, 0));
        $order = $this->order();
        $order->update(['goorder_id' => 'remote-order', 'goorder_token' => 'private-remote-token', 'status' => 'waiting_staff']);
        $remote = $this->remote('ACCEPTED') + ['estimated_preparation_at' => '2026-09-25T12:45:00', 'tracking_status' => 'PREPARATION'];
        Http::fake(['*' => Http::response(['data' => $remote])]);
        $url = route('orders.status', ['token' => $order->tracking_token]);
        $first = $this->getJson($url)->assertOk()->assertJsonPath('status', 'accepted')->assertJsonPath('expected_ready_at', '2026-09-25T10:45:00+00:00')->json();
        $this->travel(5)->minutes();
        Http::swap(new Factory);
        Http::fake(['*' => Http::response(['data' => $this->remote('ACCEPTED')])]);
        $second = $this->getJson($url)->assertOk()->json();
        $this->assertSame($first['expected_ready_at'], $second['expected_ready_at']);
        $this->assertNotSame($first['server_now'], $second['server_now']);
        $this->get($order->trackingUrl())->assertOk()->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertDontSee('private-remote-token')->assertDontSee('test@example.com')->assertDontSee('googletagmanager.com')
            ->assertDontSee('widget.wenetasystent.ai');
    }

    public function test_waiting_and_payment_confirmation_do_not_expose_an_estimate(): void
    {
        $order = $this->order();
        $order->update(['goorder_id' => 'remote-order', 'goorder_token' => 'secret', 'status' => 'waiting_staff']);
        Http::fake(['*' => Http::response(['data' => $this->remote('CONFIRMED') + ['estimated_preparation_at' => '2026-09-25T12:45:00']])]);
        $this->getJson(route('orders.status', ['token' => $order->tracking_token]))
            ->assertOk()->assertJsonPath('status', 'submission_uncertain')->assertJsonPath('expected_ready_at', null);
    }

    public function test_poll_failure_preserves_status_and_deadline_without_leaking_upstream_errors(): void
    {
        $order = $this->order();
        $order->update(['goorder_id' => 'remote-order', 'goorder_token' => 'secret', 'status' => 'accepted', 'expected_ready_at' => now()->addMinutes(20)]);
        Http::fake(['*' => Http::response(['token' => 'private'], 500)]);
        $this->getJson(route('orders.status', ['token' => $order->tracking_token]))
            ->assertOk()->assertJsonPath('status', 'accepted')->assertJsonPath('stale', true)->assertDontSee('private');
        $this->assertNotNull($order->fresh()->expected_ready_at);
    }

    public function test_invalid_tracking_token_is_404(): void
    {
        $this->get('/zamowienie/1')->assertNotFound();
        $this->getJson('/api/orders/'.str_repeat('x', 64))->assertNotFound();
        Http::assertNothingSent();
    }

    public function test_checkout_repeated_post_and_refresh_recover_the_same_order(): void
    {
        $order = $this->order();
        $this->withSession(['checkout_submission_key' => $order->submission_key])
            ->post('/koszyk', ['submission_key' => $order->submission_key])->assertRedirect($order->trackingUrl());
        $this->get('/koszyk')->assertRedirect($order->trackingUrl());
        $this->assertDatabaseCount('orders', 1);
        Http::assertNothingSent();
    }

    public function test_price_confirmation_requires_the_checkout_session(): void
    {
        $order = $this->order();
        $this->post(route('orders.confirm', ['token' => $order->tracking_token]), ['quote_hash' => str_repeat('a', 64)])->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_disabled_adapter_does_not_create_remote_orders(): void
    {
        config(['goorder.enabled' => false]);
        app(GoOrderService::class)->send($this->order());
        Http::assertNothingSent();
    }

    public function test_full_checkout_creates_one_tracked_order(): void
    {
        $this->fake();
        $fixture = $this->order();
        $menuId = $fixture->items->first()->menu_item_id;
        $fixture->delete();
        $key = (string) Str::uuid();
        $data = [
            'submission_key' => $key, 'cart_json' => json_encode([['id' => $menuId, 'quantity' => 1]]),
            'name' => 'Test checkout', 'email' => 'checkout@example.com', 'phone' => '+48000000001',
            'delivery_type' => 'pickup', 'fulfillment_type' => 'asap', 'payment_type' => 'cash',
        ];
        $response = $this->withSession(['checkout_submission_key' => $key])->post('/koszyk', $data);
        $order = Order::sole();
        $response->assertRedirect($order->trackingUrl());
        $this->assertSame('waiting_staff', $order->status);
        $this->post('/koszyk', $data)->assertRedirect($order->trackingUrl());
        $this->assertDatabaseCount('orders', 1);
        Http::assertSentCount(5);
    }

    public function test_ready_delivering_received_rejected_and_canceled_are_distinct(): void
    {
        $order = $this->order();
        $order->update(['goorder_id' => 'remote-order']);
        $service = app(GoOrderService::class);
        foreach ([['ACCEPTED', 'READY', 'ready'], ['ACCEPTED', 'DELIVERY', 'delivering'], ['CLOSED', 'RECEIVED', 'completed'], ['REJECTED', 'NEW', 'rejected'], ['CANCELED', 'NEW', 'canceled']] as [$remoteStatus, $tracking, $local]) {
            $service->applyStatus($order, $this->remote($remoteStatus) + ['tracking_status' => $tracking]);
            $this->assertSame($local, $order->fresh()->status);
        }
    }

    public function test_scheduled_delivery_and_offline_card_are_sent_without_inventing_a_staff_eta(): void
    {
        $this->fake();
        $order = $this->order();
        $order->update([
            'delivery_type' => 'delivery', 'city' => 'Toruń', 'street' => 'Andersa', 'building_number' => '72',
            'fulfillment_type' => 'scheduled', 'scheduled_at' => '2026-09-26 10:30:00',
            'payment_type' => 'card', 'wants_invoice' => true, 'nip' => '1234567890',
        ]);
        app(GoOrderService::class)->send($order);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/pay')
            && $request['type'] === 'DELIVERY' && $request['address']['build_nr'] === '72'
            && $request['payment_method_id'] === 3 && $request['pickup_at'] === '2026-09-26T12:30:00'
            && $request['tax_id_no'] === '1234567890');
        $this->assertNull($order->fresh()->expected_ready_at);
    }

    public function test_server_issued_key_is_required_and_bad_cart_is_not_partially_submitted(): void
    {
        $this->post('/koszyk', ['submission_key' => (string) Str::uuid()])->assertStatus(419);
        $key = (string) Str::uuid();
        $this->withSession(['checkout_submission_key' => $key])->post('/koszyk', [
            'submission_key' => $key, 'cart_json' => '[1]', 'name' => 'Test', 'email' => 'test@example.com',
            'phone' => '000000000', 'delivery_type' => 'pickup', 'fulfillment_type' => 'asap', 'payment_type' => 'cash',
        ])->assertSessionHas('checkout_error');
        Http::assertNothingSent();
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_staff_can_change_the_saved_deadline(): void
    {
        $order = $this->order();
        $order->update(['goorder_id' => 'remote-order']);
        $service = app(GoOrderService::class);
        $service->applyStatus($order, $this->remote('ACCEPTED') + ['estimated_preparation_at' => '2026-09-25T12:45:00']);
        $service->applyStatus($order, $this->remote('ACCEPTED') + ['estimated_preparation_at' => '2026-09-25T13:00:00']);
        $this->assertSame('2026-09-25T11:00:00+00:00', $order->fresh()->expected_ready_at->toIso8601String());
    }

    public function test_scheduled_checkout_preserves_warsaw_wall_time_after_database_reload(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 25)->setTime(10, 0));
        $this->fake();
        $fixture = $this->order();
        $menuId = $fixture->items->first()->menu_item_id;
        $fixture->delete();
        $key = (string) Str::uuid();
        $this->withSession(['checkout_submission_key' => $key])->post('/koszyk', [
            'submission_key' => $key, 'cart_json' => json_encode([['id' => $menuId, 'quantity' => 1]]),
            'name' => 'Test', 'email' => 'test@example.com', 'phone' => '000000000',
            'delivery_type' => 'pickup', 'fulfillment_type' => 'scheduled', 'payment_type' => 'cash',
            'scheduled_day' => '2026-09-26', 'scheduled_time' => '15:00',
        ])->assertRedirect(Order::sole()->trackingUrl());
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/pay') && $request['pickup_at'] === '2026-09-26T15:00:00');
        $this->assertSame('2026-09-26 13:00:00', Order::sole()->scheduled_at->format('Y-m-d H:i:s'));
    }
}
