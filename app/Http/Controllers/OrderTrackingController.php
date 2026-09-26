<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\GoOrder\GoOrderService;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    private const HEADERS = [
        'Cache-Control' => 'no-store, private',
        'Referrer-Policy' => 'no-referrer',
        'X-Robots-Tag' => 'noindex, nofollow',
    ];

    public function show(string $token, GoOrderService $service)
    {
        $order = $this->find($token);
        $service->sync($order);
        $order->refresh()->load('items');
        app()->setLocale($order->locale);

        return response()->view('order-tracking', [
            'order' => $order,
            'locale' => $order->locale,
            'state' => $this->state($order),
            'quoteHash' => $order->goorder_quote ? $service->quoteHash($order->goorder_quote) : null,
            'ownsCheckout' => hash_equals((string) session('checkout_submission_key'), (string) $order->submission_key),
        ])->withHeaders(self::HEADERS);
    }

    public function status(string $token, GoOrderService $service)
    {
        $order = $this->find($token);
        $service->sync($order);

        return response()->json($this->state($order->refresh()))->withHeaders(self::HEADERS);
    }

    public function confirm(Request $request, string $token, GoOrderService $service)
    {
        $order = $this->find($token);
        abort_unless(hash_equals((string) session('checkout_submission_key'), (string) $order->submission_key), 403);
        $data = $request->validate(['quote_hash' => ['required', 'string', 'size:64']]);
        $service->confirm($order, $data['quote_hash']);

        return redirect($order->trackingUrl())->withHeaders(self::HEADERS);
    }

    private function find(string $token): Order
    {
        return Order::query()->where('tracking_token', $token)->firstOrFail();
    }

    private function state(Order $order): array
    {
        $accepted = in_array($order->status, ['accepted', 'ready', 'delivering', 'completed'], true);

        return [
            'status' => $order->status,
            'number' => $order->number,
            'delivery_type' => $order->delivery_type,
            'expected_ready_at' => $accepted ? $order->expected_ready_at?->toIso8601String() : null,
            'server_now' => now()->toIso8601String(),
            'last_synced_at' => $order->goorder_synced_at?->toIso8601String(),
            'stale' => ! $order->trackingFinished() && ((bool) $order->goorder_error || ($order->goorder_synced_at && $order->goorder_synced_at->lt(now()->subMinutes(2)))),
            'finished' => $order->trackingFinished(),
            'submitted' => (bool) $order->goorder_submit_started_at,
            'poll_seconds' => config('goorder.poll_seconds'),
        ];
    }
}
