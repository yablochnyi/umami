<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RequestValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['goorder.enabled' => false]);
        Http::preventStrayRequests();
    }

    private function checkout(array $overrides = []): array
    {
        return array_replace([
            'cart_json' => '[{"id":1,"quantity":1}]', 'name' => 'Test Customer',
            'email' => 'test@example.com', 'phone' => '000000000',
            'delivery_type' => 'pickup', 'fulfillment_type' => 'asap', 'payment_type' => 'cash',
        ], $overrides);
    }

    public static function malformedCarts(): array
    {
        return [
            'invalid JSON' => ['{'],
            'scalar' => ['1'],
            'non-list' => ['{"item":{"id":1,"quantity":1}}'],
            'scalar row' => ['[1]'],
            'missing id' => ['[{"quantity":1}]'],
            'negative id' => ['[{"id":-1,"quantity":1}]'],
            'duplicate id' => ['[{"id":1,"quantity":1},{"id":"1","quantity":2}]'],
            'too many rows' => [json_encode(array_fill(0, 101, ['id' => 1, 'quantity' => 1]))],
            'zero quantity' => ['[{"id":1,"quantity":0}]'],
            'fractional quantity' => ['[{"id":1,"quantity":1.5}]'],
            'excess quantity' => ['[{"id":1,"quantity":101}]'],
            'invalid modifiers' => ['[{"id":1,"quantity":1,"modifiers":"bad"}]'],
        ];
    }

    #[DataProvider('malformedCarts')]
    public function test_bad_carts_are_rejected_before_creating_orders(string $cart): void
    {
        $this->postJson('/koszyk', $this->checkout(['cart_json' => $cart]))
            ->assertUnprocessable()->assertJsonValidationErrors('cart_json');
        $this->assertDatabaseCount('orders', 0);
        Http::assertNothingSent();
    }

    public function test_checkout_error_and_old_input_survive_html_validation(): void
    {
        $this->from('/uk/koszyk')->post('/uk/koszyk', $this->checkout(['cart_json' => '[1]']))
            ->assertRedirect('/uk/koszyk')->assertSessionHasErrors('cart_json')
            ->assertSessionHas('checkout_error', 'Не вдалося прочитати кошик.')
            ->assertSessionHasInput('name', 'Test Customer');
    }

    public function test_validation_uses_the_route_language_before_the_controller_runs(): void
    {
        foreach (['pl' => '', 'uk' => '/uk', 'en' => '/en'] as $locale => $prefix) {
            $expected = __('validation.required', ['attribute' => __('checkout.name', [], $locale)], $locale);
            $this->postJson($prefix.'/koszyk', $this->checkout(['name' => '']))
                ->assertUnprocessable()->assertJsonPath('errors.name.0', $expected);
        }
    }

    public function test_schedule_requires_the_exact_date_format_used_by_the_parser(): void
    {
        foreach (['22 October 2026', '2026-02-30', '2026-10-22T12:00:00'] as $day) {
            $this->postJson('/koszyk', $this->checkout([
                'fulfillment_type' => 'scheduled', 'scheduled_day' => $day, 'scheduled_time' => '15:00',
            ]))->assertUnprocessable()->assertJsonValidationErrors('scheduled_day');
        }
        $this->assertDatabaseCount('orders', 0);
        Http::assertNothingSent();
    }

    public function test_conditional_delivery_and_invoice_fields_are_required(): void
    {
        foreach ([true, 1, '1'] as $invoice) {
            $this->postJson('/koszyk', $this->checkout(['delivery_type' => 'delivery', 'wants_invoice' => $invoice]))
                ->assertUnprocessable()->assertJsonValidationErrors(['city', 'street', 'building_number', 'nip']);
        }
        Http::assertNothingSent();
    }

    public function test_address_apis_reject_non_string_and_oversized_inputs(): void
    {
        $this->getJson('/api/torun-streets?q[]=x')->assertUnprocessable()->assertJsonValidationErrors('q');
        $this->getJson('/api/torun-streets?q=Mi&city[]=x')->assertUnprocessable()->assertJsonValidationErrors('city');
        $this->getJson('/api/torun-streets?q='.str_repeat('a', 256))->assertUnprocessable()->assertJsonValidationErrors('q');
        $this->getJson('/api/delivery-quote?street[]=x')->assertUnprocessable()->assertJsonValidationErrors('street');
        $this->getJson('/api/delivery-quote?city=Warszawa')->assertUnprocessable()->assertJsonValidationErrors('city');
        $this->getJson('/api/delivery-quote?building='.str_repeat('a', 51))->assertUnprocessable()->assertJsonValidationErrors('building');
        Http::assertNothingSent();
    }

    public function test_empty_address_queries_keep_their_existing_defaults(): void
    {
        $this->getJson('/api/torun-streets')->assertOk()->assertExactJson([]);
        $this->getJson('/api/delivery-quote')->assertOk()->assertJsonPath('cost', 9.99);
        Http::assertNothingSent();
    }

    public function test_availability_requires_matching_day_and_time(): void
    {
        $this->getJson('/api/menu-availability?day=2026-10-22')->assertUnprocessable()->assertJsonValidationErrors('time');
        $this->getJson('/api/menu-availability?time=12:00')->assertUnprocessable()->assertJsonValidationErrors('day');
        $this->getJson('/api/menu-availability?locale=invalid')->assertUnprocessable()->assertJsonValidationErrors('locale');
    }

    public function test_locale_request_rejects_invalid_languages_without_changing_session(): void
    {
        $this->withSession(['admin_locale' => 'uk'])->postJson('/admin/language', ['locale' => 'en'])
            ->assertUnprocessable()->assertJsonValidationErrors('locale')->assertSessionHas('admin_locale', 'uk');
    }

    public function test_confirmation_authorization_precedes_validation(): void
    {
        $customer = Customer::create(['name' => 'Test', 'phone' => '+48000000000']);
        $order = Order::create([
            'customer_id' => $customer->id, 'number' => 'VALIDATION-TEST', 'status' => 'quote_review',
            'tracking_token' => Str::random(64), 'submission_key' => (string) Str::uuid(),
            'delivery_type' => 'pickup', 'fulfillment_type' => 'asap', 'payment_type' => 'cash',
            'subtotal' => 1, 'total' => 1, 'locale' => 'uk',
        ]);
        $url = route('orders.confirm', ['token' => $order->tracking_token]);
        $this->postJson($url, [])->assertForbidden();
        $this->withSession(['checkout_submission_key' => $order->submission_key])->postJson($url, [])
            ->assertUnprocessable()->assertJsonValidationErrors('quote_hash');
        $this->postJson('/zamowienie/'.str_repeat('x', 64).'/confirm')->assertNotFound();
        Http::assertNothingSent();
    }
}
