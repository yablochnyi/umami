@extends('layouts.site', [
    'metaTitle' => __('tracking.title').' | Umami Sushi & Food',
    'robots' => 'noindex, nofollow',
    'privatePage' => true,
    'layoutCss' => '/assets/umami/checkout.css',
    'layoutJs' => '/assets/umami/order-tracking.js',
    'showCart' => false,
])

@section('csrf')
@endsection

@push('head')
    <meta name="referrer" content="no-referrer">
    <link rel="stylesheet" href="/assets/umami/order-tracking.css?v=1">
@endpush

@section('content')
    <main id="orderTracking" class="tracking-page"
        data-status-url="{{ route('orders.status', ['token' => $order->tracking_token]) }}"
        data-initial="{{ json_encode($state) }}"
        data-owns-checkout="{{ $ownsCheckout ? '1' : '0' }}"
        data-copy="{{ json_encode(__('tracking')) }}">
        <div class="tracking-heading">
            <span class="tracking-eyebrow">UMAMI SUSHI & FOOD</span>
            <p>{{ __('tracking.number') }} <strong>{{ $order->number }}</strong></p>
        </div>
        <div class="tracking-layout">
            <section class="tracking-status" aria-labelledby="statusTitle">
                <span id="statusSymbol" class="status-symbol" aria-hidden="true"></span>
                <h1 id="statusTitle" aria-live="polite">{{ __('tracking.states.'.$order->status) }}</h1>
                <p id="statusDescription"></p>
                <div id="timeBlock" class="tracking-time" hidden>
                    <span>{{ __('tracking.remaining') }}</span>
                    <div id="countdown" class="countdown" role="timer" aria-live="off"></div>
                    <p>{{ __('tracking.eta') }}: <time id="readyTime"></time></p>
                </div>
                <p id="overdue" class="tracking-warning" hidden>{{ __('tracking.overdue') }}</p>
                <p id="connectionWarning" class="tracking-warning" role="status" hidden>{{ __('tracking.offline') }}</p>
                <p class="tracking-updated" id="lastUpdated"></p>
                @if($order->status === 'quote_review' && $ownsCheckout)
                    <form action="{{ route('orders.confirm', ['token' => $order->tracking_token]) }}" method="post" id="confirmOrder">
                        @csrf
                        <input type="hidden" name="quote_hash" value="{{ $quoteHash }}">
                        <button class="submit-button" type="submit">{{ __('tracking.confirm') }}</button>
                    </form>
                @endif
                <a class="tracking-contact" href="tel:+48513233722">{{ __('tracking.call') }}: +48 513 233 722</a>
                <a class="tracking-new" href="{{ $locale === 'pl' ? '/koszyk?new=1' : '/'.$locale.'/koszyk?new=1' }}">{{ __('tracking.new') }}</a>
            </section>
            <section class="tracking-summary" aria-labelledby="summaryTitle">
                <h2 id="summaryTitle">{{ __('tracking.summary') }}</h2>
                <p class="tracking-method">{{ __('tracking.'.($order->delivery_type === 'delivery' ? 'delivery' : 'pickup')) }}</p>
                @php
                    $quote = $order->goorder_quote;
                    $lines = $quote['items'] ?? $order->items->map(fn ($item) => ['name' => $item->name, 'quantity' => $item->quantity, 'total' => (int) round($item->total * 100)])->all();
                    $total = $quote['total'] ?? (int) round($order->total * 100);
                    $delivery = $quote['delivery'] ?? (int) round($order->delivery_cost * 100);
                    $fees = $total - $delivery - array_sum(array_column($lines, 'total'));
                @endphp
                <ul class="tracking-items">
                    @foreach($lines as $item)
                        <li><span>{{ $item['quantity'] }} × {{ $item['name'] }}</span><strong>{{ number_format($item['total'] / 100, 2, ',', ' ') }} zł</strong></li>
                    @endforeach
                </ul>
                @if($delivery)
                    <p class="tracking-line"><span>{{ __('tracking.delivery') }}</span><span>{{ number_format($delivery / 100, 2, ',', ' ') }} zł</span></p>
                @endif
                @if($fees)
                    <p class="tracking-line"><span>{{ __('tracking.fees') }}</span><span>{{ number_format($fees / 100, 2, ',', ' ') }} zł</span></p>
                @endif
                <p class="tracking-line tracking-total"><span>{{ __('tracking.total') }}</span><strong>{{ number_format($total / 100, 2, ',', ' ') }} zł</strong></p>
            </section>
        </div>
        <noscript><p>{{ __('tracking.offline') }}</p></noscript>
    </main>
@endsection
