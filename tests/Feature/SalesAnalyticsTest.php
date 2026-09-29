<?php

namespace Tests\Feature;

use App\Filament\Pages\SalesAnalytics;
use App\Models\AnalyticsItem;
use App\Models\AnalyticsOrder;
use App\Models\AnalyticsSyncRun;
use App\Models\Role;
use App\Models\User;
use App\Services\GoPos\GoPosAnalyticsSync;
use App\Services\GoPos\GoPosClient;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class SalesAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['gopos.organization_id' => 'test']);
        $this->travelTo(now()->setDate(2026, 9, 29)->startOfDay());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        app()->setLocale('pl');
        $this->actingAs(User::factory()->create(['role_id' => Role::where('is_admin', true)->sole()->id]));
    }

    private function remote(int $id = 1, array $overrides = []): array
    {
        return array_replace([
            'id' => $id, 'number' => '000'.$id, 'created_at' => '2026-09-28T23:30:00',
            'source' => 'Wolt', 'status' => 'CLOSED', 'payment_status' => 'PAID', 'type' => 'DELIVERY',
            'total_price' => ['amount' => 119.99, 'currency' => 'PLN'],
            'total_paid_price' => ['amount' => 119.99, 'currency' => 'PLN'],
            'total_price_net' => ['amount' => 111.1, 'currency' => 'PLN'],
            'contact' => ['email' => 'do-not-store@example.test'], 'comment' => 'private',
            'items' => [['id' => 'line-1', 'item_id' => 3, 'name' => 'Shoyu Ramen', 'quantity' => 2, 'status' => 'ACTIVE', 'total_price' => ['amount' => 100]]],
            'transactions' => [['payment_method_name' => 'Card', 'status' => 'PAID', 'price' => ['amount' => 119.99]]],
        ], $overrides);
    }

    private function import(array $orders): void
    {
        $client = Mockery::mock(GoPosClient::class);
        $client->shouldReceive('organizationId')->andReturn('test');
        $client->shouldReceive('get')->once()->with('/api/v3/test/orders', Mockery::on(fn ($q) => $q['sort'] === 'id,desc' && $q['created_at_from'] === '2026-05-01T00:00:00'))->andReturn(['data' => [end($orders)]]);
        $client->shouldReceive('get')->once()->with('/api/v3/test/orders', Mockery::on(fn ($q) => $q['id_from'] === 0 && $q['size'] === 100 && $q['include'] === 'items,items.product,transactions'))->andReturn(['data' => $orders]);
        (new GoPosAnalyticsSync($client))->sync('2026-05-01');
    }

    public function test_import_is_idempotent_and_preserves_source_money_and_local_date_without_pii(): void
    {
        $this->import([$this->remote()]);
        $this->import([$this->remote()]);
        $this->assertDatabaseCount('analytics_orders', 1);
        $this->assertDatabaseCount('analytics_items', 1);
        $this->assertDatabaseHas('analytics_orders', ['total_cents' => 11999, 'paid_cents' => 11999, 'source' => 'Wolt', 'business_date' => '2026-09-28', 'hour' => 23]);
        $this->assertStringNotContainsString('do-not-store', AnalyticsOrder::first()->toJson());
        $this->assertSame('2026-09-28 21:30:00', AnalyticsOrder::first()->ordered_at->format('Y-m-d H:i:s'));
        $this->assertSame(2, AnalyticsSyncRun::where('status', 'completed')->count());
    }

    public function test_corrections_and_cancellations_replace_previous_snapshot(): void
    {
        $this->import([$this->remote()]);
        $this->import([$this->remote(1, ['status' => 'REMOVED', 'items' => [], 'total_price' => ['amount' => 0, 'currency' => 'PLN'], 'total_paid_price' => ['amount' => 0, 'currency' => 'PLN']])]);
        $this->assertDatabaseCount('analytics_items', 0);
        $this->assertDatabaseHas('analytics_orders', ['remote_id' => 1, 'status' => 'REMOVED', 'total_cents' => 0]);
    }

    public function test_nonadvancing_pagination_fails_instead_of_reporting_success(): void
    {
        $client = Mockery::mock(GoPosClient::class);
        $client->shouldReceive('organizationId')->andReturn('test');
        $client->shouldReceive('get')->andReturn(['data' => [['id' => 2]]], ['data' => [$this->remote()]], ['data' => [$this->remote()]]);
        try {
            (new GoPosAnalyticsSync($client))->sync('2026-05-01');
            $this->fail('Expected a failed pagination check.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Unexpected GoPOS pagination order.', $e->getMessage());
        }
        $this->assertDatabaseHas('analytics_sync_runs', ['status' => 'failed', 'orders_count' => 1]);
        $this->assertDatabaseCount('analytics_orders', 1);
    }

    public function test_bad_item_does_not_destroy_previous_order_or_lines(): void
    {
        $this->import([$this->remote()]);
        try {
            $this->import([$this->remote(1, ['items' => [['name' => 'Bad', 'id' => 'bad', 'quantity' => 1, 'total_price' => []]]])]);
            $this->fail('Missing monetary amount must fail.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Missing GoPOS monetary amount.', $e->getMessage());
        }
        $this->assertSame('Shoyu Ramen', AnalyticsItem::sole()->name);
    }

    public function test_page_filters_and_totals_match_only_selected_orders_and_currency(): void
    {
        $this->import([$this->remote(), $this->remote(2, ['source' => 'GoPOS']), $this->remote(3, ['status' => 'REMOVED']), $this->remote(4, ['total_price' => ['amount' => 30, 'currency' => 'EUR']])]);
        $page = Livewire::test(SalesAnalytics::class)->assertSuccessful();
        $this->assertSame(2, (int) $page->instance()->report()['totals']->orders_count);
        $this->assertSame(23998, (int) $page->instance()->report()['totals']->total);
        $page->filterTable('source', ['Wolt']);
        $this->assertSame(11999, (int) $page->instance()->report()['totals']->total);
        $page->searchTable('0002');
        $this->assertSame(0, (int) $page->instance()->report()['totals']->orders_count);
        $page->searchTable('')->filterTable('period', ['from' => '2026-05-01', 'to' => '2026-05-31']);
        $this->assertSame(0, (int) $page->instance()->report()['totals']->orders_count);
    }

    public function test_only_protected_administrator_can_access_export_and_request_sync(): void
    {
        $role = Role::create(['name' => 'Marketing', 'permissions' => ['orders.view', 'site_texts.update']]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));
        $this->get('/admin/sales-analytics')->assertForbidden();
        Livewire::test(SalesAnalytics::class)->assertForbidden();
        foreach (['export', 'requestSync', 'report'] as $method) {
            try {
                (new SalesAnalytics)->$method();
                $this->fail('Expected forbidden access.');
            } catch (HttpException $e) {
                $this->assertSame(403, $e->getStatusCode());
            }
        }
        $this->assertFalse(Cache::has('analytics.sync.requested'));
    }

    public function test_admin_can_request_sync_and_export_filtered_csv(): void
    {
        $this->import([$this->remote(1, ['source' => '=HYPERLINK("evil")'])]);
        $page = Livewire::test(SalesAnalytics::class)->call('requestSync')->assertNotified();
        $this->assertTrue(Cache::has('analytics.sync.requested'));
        $response = $page->instance()->export();
        ob_start();
        $response->sendContent();
        $csv = ob_get_clean();
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString('119.99', $csv);
        Cache::forget('analytics.sync.requested');
    }

    public function test_iso_utc_time_is_grouped_in_warsaw_and_removed_lines_are_excluded(): void
    {
        $remote = $this->remote(1, ['created_at' => '2026-09-27T23:30:00Z']);
        $remote['items'][] = array_replace($remote['items'][0], ['status' => 'REMOVED', 'name' => 'Removed']);
        $this->import([$remote]);
        $this->assertDatabaseHas('analytics_orders', ['business_date' => '2026-09-28', 'hour' => 1, 'weekday' => 1]);
        $this->assertDatabaseCount('analytics_items', 1);
    }

    public function test_background_request_without_a_request_does_not_contact_gopos(): void
    {
        Cache::forget('analytics.sync.requested');
        $this->mock(GoPosAnalyticsSync::class)->shouldNotReceive('sync');
        $this->artisan('analytics:sync', ['--requested' => true])->assertSuccessful();
    }

    public function test_running_sync_keeps_manual_request_for_a_later_retry(): void
    {
        Cache::put('analytics.sync.requested', true, 300);
        $lock = Cache::lock('analytics.sync.lock', 300);
        $lock->get();
        try {
            $this->mock(GoPosAnalyticsSync::class)->shouldNotReceive('sync');
            $this->artisan('analytics:sync', ['--requested' => true])->assertSuccessful();
            $this->assertTrue(Cache::has('analytics.sync.requested'));
        } finally {
            $lock->release();
            Cache::forget('analytics.sync.requested');
        }
    }
}
