<?php

namespace Tests\Feature;

use App\Filament\Pages\WebsiteTraffic;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use App\Models\WebsiteVisit;
use App\Services\AdminAudit;
use App\Services\WebsiteAnalytics;
use App\Services\WebsiteAnalyticsReport;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class WebsiteAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private string $visitor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->visitor = (string) Str::uuid();
        $this->travelTo(CarbonImmutable::parse('2026-10-24 14:00:00', 'UTC'));
        config(['website_analytics.enabled' => true]);
    }

    private function event(array $data = []): array
    {
        return [...['name' => 'page_view', 'event_id' => (string) Str::uuid(), 'path' => '/', 'referrer_host' => 'www.google.com'], ...$data];
    }

    private function consent(string $choice = 'accepted'): static
    {
        return $this->withCredentials()->withUnencryptedCookies(['umami_cookie_consent' => $choice, 'umami_visitor_id' => $this->visitor])
            ->withHeader('User-Agent', 'Mozilla/5.0 iPhone Mobile');
    }

    private function totals(): object
    {
        return app(WebsiteAnalyticsReport::class)->build(['from' => '2026-10-24', 'to' => '2026-10-24'])['totals'];
    }

    public function test_no_collection_without_consent_or_valid_identifier(): void
    {
        $this->postJson('/api/site-events', $this->event())->assertNoContent();
        $this->consent('declined')->postJson('/api/site-events', $this->event())->assertNoContent();
        $this->visitor = 'not-a-uuid';
        $this->consent()->postJson('/api/site-events', $this->event())->assertNoContent();
        $this->assertDatabaseCount('website_events', 0);
        $this->assertDatabaseCount('website_visits', 0);
    }

    public function test_retries_are_idempotent_and_reload_is_not_a_new_visitor(): void
    {
        $event = $this->event();
        $this->consent()->postJson('/api/site-events', $event)->assertNoContent();
        $this->postJson('/api/site-events', $event)->assertNoContent();
        $this->postJson('/api/site-events', $this->event(['path' => '/uk']))->assertNoContent();
        $this->assertDatabaseCount('website_visits', 1);
        $this->assertDatabaseCount('website_events', 2);
        $this->assertSame(1, (int) $this->totals()->visitors);
        $this->assertSame(2, (int) $this->totals()->views);
        $this->assertDatabaseHas('website_events', ['business_date' => '2026-10-24', 'hour' => 16]);
        $json = WebsiteVisit::first()->toJson();
        $this->assertStringNotContainsString($this->visitor, $json);
        $this->assertStringNotContainsString('Mozilla', $json);
        $this->assertStringNotContainsString('127.0.0.1', $json);
    }

    public function test_thirty_minutes_creates_new_visit_but_same_daily_visitor(): void
    {
        $this->consent()->postJson('/api/site-events', $this->event());
        $this->travel(31)->minutes();
        $this->postJson('/api/site-events', $this->event());
        $this->assertSame(1, (int) $this->totals()->visitors);
        $this->assertSame(2, (int) $this->totals()->visits);
        $this->visitor = (string) Str::uuid();
        $this->consent()->postJson('/api/site-events', $this->event());
        $this->assertSame(2, (int) $this->totals()->visitors);
    }

    public function test_private_paths_query_strings_and_purchase_payloads_are_not_stored(): void
    {
        foreach (['/admin', '/zamowienie/'.str_repeat('a', 64), '/api/orders/'.str_repeat('a', 64)] as $path) {
            $this->consent()->postJson('/api/site-events', $this->event(['path' => $path]))->assertNoContent();
        }
        $this->postJson('/api/site-events', $this->event(['path' => '/?email=private@example.test']))->assertUnprocessable();
        $this->postJson('/api/site-events', $this->event(['name' => 'order_submitted']))->assertUnprocessable();
        $this->postJson('/api/site-events', $this->event(['utm_source' => 'private@example.test']))->assertUnprocessable();
        $this->postJson('/api/site-events', $this->event(['name' => 'add_to_cart']))->assertUnprocessable();
        $this->assertDatabaseCount('website_events', 0);
    }

    public function test_bots_admin_users_and_disabled_collection_are_excluded(): void
    {
        $this->consent()->withHeader('User-Agent', 'Googlebot')->postJson('/api/site-events', $this->event());
        $this->assertDatabaseCount('website_events', 0);
        config(['website_analytics.enabled' => false]);
        $this->consent()->postJson('/api/site-events', $this->event());
        $this->assertDatabaseCount('website_events', 0);
        config(['website_analytics.enabled' => true]);
        $this->actingAs(User::factory()->create());
        $this->consent()->postJson('/api/site-events', $this->event());
        $this->assertDatabaseCount('website_events', 0);
        $this->get('/')->assertOk()->assertDontSee('data-website-analytics-url', false);
    }

    public function test_first_touch_attribution_and_report_filters(): void
    {
        $this->consent()->postJson('/api/site-events', $this->event(['utm_source' => 'Instagram', 'utm_campaign' => 'autumn']));
        $this->postJson('/api/site-events', $this->event(['referrer_host' => 'umamisushifood.pl']));
        $this->assertDatabaseHas('website_visits', ['source' => 'utm:instagram', 'campaign' => 'autumn', 'device' => 'mobile']);
        $this->visitor = (string) Str::uuid();
        $this->consent()->postJson('/api/site-events', $this->event());
        $report = app(WebsiteAnalyticsReport::class)->build(['from' => '2026-10-24', 'to' => '2026-10-24', 'source' => 'utm:instagram']);
        $this->assertSame(1, (int) $report['totals']->visitors);
        $this->assertSame(2, (int) $report['totals']->views);
        $this->assertSame('autumn', $report['campaigns']->first()->campaign);
        $this->assertFalse($report['hasComparison']);
    }

    public function test_local_midnight_and_dst_are_bucketed_in_warsaw(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-24 22:15:00', 'UTC'));
        $this->consent()->postJson('/api/site-events', $this->event());
        $this->assertDatabaseHas('website_events', ['business_date' => '2026-10-25', 'hour' => 0]);
        $this->travelTo(CarbonImmutable::parse('2026-10-25 01:15:00', 'UTC'));
        $this->postJson('/api/site-events', $this->event());
        $this->assertDatabaseHas('website_events', ['business_date' => '2026-10-25', 'hour' => 2]);
    }

    public function test_only_successful_server_orders_are_counted_once_and_consent_is_required(): void
    {
        $this->consent()->postJson('/api/site-events', $this->event());
        $request = Request::create('/koszyk', 'POST', [], ['umami_cookie_consent' => 'accepted', 'umami_visitor_id' => $this->visitor]);
        $order = new Order;
        $order->id = 99;
        $order->status = 'gopos_error';
        $service = app(WebsiteAnalytics::class);
        $service->orderSubmitted($request, $order);
        $this->assertSame(0, (int) $this->totals()->orders);
        $order->status = 'sent_to_gopos';
        $service->orderSubmitted($request, $order);
        $service->orderSubmitted($request, $order);
        $this->assertSame(1, (int) $this->totals()->orders);
        $order->id = 100;
        $request->cookies->set('umami_cookie_consent', 'declined');
        $service->orderSubmitted($request, $order);
        $this->assertSame(1, (int) $this->totals()->orders);
    }

    public function test_only_superadmin_can_open_report_filter_and_export(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->get('/admin/website-traffic')->assertRedirect('/admin/login');
        $role = Role::create(['name' => 'Marketing', 'permissions' => ['orders.view']]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));
        $this->get('/admin/website-traffic')->assertForbidden();
        Livewire::test(WebsiteTraffic::class)->assertForbidden();
        $this->actingAs(User::factory()->create(['role_id' => Role::where('is_admin', true)->sole()->id]));
        $this->get('/admin/website-traffic')->assertOk();
        $this->assertDatabaseHas('admin_audit_logs', ['resource' => 'website_traffic', 'action' => 'viewed']);
        $page = Livewire::test(WebsiteTraffic::class)->assertSuccessful();
        $page->call('setPeriod', 7)->assertSuccessful();
        $this->assertCount(7, $page->instance()->report()['daily']);
        $page->set('data.from', '2020-01-01')->call('applyFilters')->assertHasErrors(['data.from']);
        $page->call('setPeriod', 1)->call('export')->assertFileDownloaded();
        request()->attributes->set('admin_audit_actor', app(AdminAudit::class)->actor(auth()->user()));
        $page->instance()->export();
        $this->assertDatabaseHas('admin_audit_logs', ['resource' => 'website_traffic', 'action' => 'exported']);
    }

    public function test_retention_removes_old_events_and_empty_visits(): void
    {
        $this->travel(-370)->days();
        $this->consent()->postJson('/api/site-events', $this->event());
        $this->travel(370)->days();
        $this->postJson('/api/site-events', $this->event());
        $this->artisan('website-analytics:prune')->assertSuccessful();
        $this->assertDatabaseCount('website_events', 1);
        $this->assertDatabaseCount('website_visits', 1);
    }
}
