<div class="umami-analytics-section">
    <p>{{ $order->source }} · {{ $order->ordered_at->timezone('Europe/Warsaw')->format('d.m.Y H:i') }} · {{ __('analytics.statuses')[$order->status] ?? $order->status }}</p>
    <div class="umami-table-scroll"><table class="umami-orders-table">
        <thead><tr><th>{{ __('analytics.dish') }}</th><th>{{ __('analytics.quantity') }}</th><th>{{ __('analytics.line_total') }}</th></tr></thead>
        <tbody>@foreach($order->items as $item)<tr><td class="umami-analytics-name">{{ $item->name }}</td><td>{{ (float) $item->quantity }}</td><td>{{ \App\Filament\Pages\SalesAnalytics::money($item->total_cents, $order->currency) }}</td></tr>@endforeach</tbody>
    </table></div>
    <p>{{ __('analytics.total') }}: <strong>{{ \App\Filament\Pages\SalesAnalytics::money($order->total_cents, $order->currency) }}</strong></p>
    <p>{{ __('analytics.paid') }}: <strong>{{ \App\Filament\Pages\SalesAnalytics::money($order->paid_cents, $order->currency) }}</strong></p>
    @foreach($order->payments as $payment)
        <p>{{ $payment['method'] }} · {{ $payment['status'] }} · {{ \App\Filament\Pages\SalesAnalytics::money($payment['amount_cents'], $order->currency) }}</p>
    @endforeach
</div>
