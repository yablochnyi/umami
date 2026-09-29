<x-filament-panels::page>
    <div class="umami-overview">
        @if ($canSeeOrders || $canSeeMenu)
            <dl class="umami-metrics">
                @if ($canSeeOrders)
                    <div><dt><x-heroicon-o-shopping-bag />{{ __('admin.website_orders') }}</dt><dd>{{ $ordersToday }}</dd><span>{{ __('admin.today') }}</span></div>
                    <div><dt><x-heroicon-o-banknotes />{{ __('admin.website_total') }}</dt><dd>{{ number_format($totalToday, 2, ',', ' ') }} <small>zł</small></dd><span>{{ __('admin.today') }}</span></div>
                @endif
                @if ($canSeeMenu)
                    <div><dt><x-heroicon-o-cake />{{ __('admin.active_dishes') }}</dt><dd>{{ $activeDishes }}</dd><span>UMAMI</span></div>
                    <div><dt><x-heroicon-o-check-badge />{{ __('admin.storefront') }}</dt><dd>{{ $publishedDishes }}</dd><span>GoOrder</span></div>
                @endif
            </dl>
        @endif

        @if ($canSeeOrders)
            <section class="umami-orders">
                <div class="umami-section-heading"><h2>{{ __('admin.latest_orders') }}</h2><a href="{{ \App\Filament\Resources\Orders\OrderResource::getUrl() }}">{{ __('admin.resources.orders') }} <x-heroicon-o-arrow-right /></a></div>
                <div class="umami-table-scroll">
                    <table class="umami-orders-table">
                        <thead><tr><th>{{ __('Numer wewnętrzny') }}</th><th>{{ __('Data') }}</th><th>{{ __('Typ') }}</th><th>{{ __('Status') }}</th><th>{{ __('Razem') }}</th><th><span class="sr-only">{{ __('Podgląd') }}</span></th></tr></thead>
                        <tbody>
                        @forelse ($latestOrders as $order)
                            <tr><td class="umami-order-number"><span title="{{ $order->number }}">{{ $order->number }}</span></td><td>{{ $order->created_at->timezone('Europe/Warsaw')->format('d.m H:i') }}</td><td>{{ __($order->delivery_type) }}</td>
                                <td><span class="umami-status">{{ match ($order->status) { 'waiting_gopos_acceptance' => __('Czeka w GoPOS'), 'sent_to_gopos' => __('Wysłane'), 'gopos_error' => __('Błąd GoPOS'), default => __('tracking.states.'.$order->status) } }}</span></td>
                                <td>{{ number_format($order->total, 2, ',', ' ') }} zł</td><td><a href="{{ \App\Filament\Resources\Orders\OrderResource::getUrl('view', ['record' => $order]) }}" aria-label="{{ __('Podgląd') }} {{ $order->number }}" title="{{ __('Podgląd') }}"><x-heroicon-o-arrow-up-right /></a></td></tr>
                        @empty
                            <tr><td colspan="6" class="umami-empty">{{ __('filament-tables::table.empty.heading', ['model' => __('admin.resources.orders')]) }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if ($canSeeMenu)
            <section class="umami-menu-preview">
                <div class="umami-section-heading"><div><h2>{{ __('admin.menu_overview') }}</h2><p>{{ __('admin.recently_added') }}</p></div><a href="{{ \App\Filament\Resources\MenuItems\MenuItemResource::getUrl() }}">{{ __('admin.all_dishes') }} <x-heroicon-o-arrow-right /></a></div>
                <div class="umami-dishes">
                    @foreach ($dishes as $dish)
                        <a class="umami-dish" href="{{ \App\Filament\Resources\MenuItems\MenuItemResource::getUrl('view', ['record' => $dish]) }}">
                            <div class="umami-dish-image">
                                @if ($dish->image)<img src="{{ asset('storage/'.$dish->image) }}" alt="{{ $dish->name }}" loading="lazy">@else<x-heroicon-o-photo />@endif
                                <span>{{ __('admin.active') }}</span>
                            </div>
                            <div class="umami-dish-details"><p>{{ $dish->category?->name }}</p><h3>{{ $dish->name }}</h3><strong>{{ $dish->price }}{{ is_numeric(str_replace(',', '.', (string) $dish->price)) ? ' zł' : '' }}</strong></div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-filament-panels::page>
