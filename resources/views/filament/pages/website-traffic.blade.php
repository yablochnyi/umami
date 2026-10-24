<x-filament-panels::page>
    @php($r = $this->report())
    <div class="umami-traffic-periods">
        @foreach([1 => 'today', 7 => 'week', 30 => 'month'] as $days => $label)
            <x-filament::button color="gray" wire:click="setPeriod({{ $days }})">{{ __('traffic.'.$label) }}</x-filament::button>
        @endforeach
        <span>{{ $this->filters['from'] }} / {{ $this->filters['to'] }} · Europe/Warsaw</span>
    </div>
    <form wire:submit="applyFilters" class="umami-traffic-filters">
        {{ $this->form }}
        <x-filament::button type="submit" icon="heroicon-o-funnel">{{ __('traffic.apply') }}</x-filament::button>
    </form>
    <div wire:poll.60s>
        <dl class="umami-metrics umami-analytics-metrics">
            @foreach(['visitors' => 'users', 'visits' => 'arrow-path', 'views' => 'eye', 'orders' => 'shopping-bag'] as $metric => $icon)
                <div>
                    <dt><x-dynamic-component :component="'heroicon-o-'.$icon" />{{ __('traffic.'.$metric) }}</dt>
                    <dd>{{ number_format($r['totals']->$metric, 0, ',', ' ') }}</dd>
                    @if($r['hasComparison'])
                        @php($delta = $r['totals']->$metric - $r['previous']->$metric)
                        <small class="umami-traffic-delta">{{ $delta > 0 ? '+' : '' }}{{ number_format($delta, 0, ',', ' ') }} {{ __('traffic.compared') }}</small>
                    @endif
                </div>
            @endforeach
        </dl>
        @if(!$r['totals']->visitors)
            <div class="umami-traffic-empty"><x-heroicon-o-chart-bar /><h2>{{ __('traffic.empty') }}</h2><p>{{ __('traffic.empty_detail') }}</p></div>
        @endif
        <section class="umami-analytics-section">
            @php($hourly = $this->filters['from'] === $this->filters['to'])
            <h2>{{ __('traffic.'.($hourly ? 'hourly' : 'daily')) }}</h2>
            <div class="umami-analytics-chart umami-traffic-chart" role="img" aria-label="{{ __('traffic.'.($hourly ? 'hourly' : 'daily')) }}">
                @foreach($hourly ? range(0, 23) : $r['daily'] as $point)
                    @php($count = $hourly ? ($r['hours'][$point] ?? 0) : $point->visitors)
                    @php($label = $hourly ? sprintf('%02d', $point) : substr($point->business_date, 5))
                    @php($max = max(1, $hourly ? $r['hours']->max() : $r['daily']->max('visitors')))
                    <div class="umami-analytics-bar" title="{{ $label }}: {{ $count }} {{ __('traffic.visitors') }}">
                        <span>{{ $count }}</span><i style="height: {{ max(2, $count / $max * 150) }}px"></i><small>{{ $label }}</small>
                    </div>
                @endforeach
            </div>
        </section>
        <div class="umami-analytics-grid">
            <section class="umami-analytics-section">
                <h2>{{ __('traffic.funnel') }}</h2>
                <div class="umami-analytics-distribution">
                    @foreach(['page_view', 'menu_view', 'product_view', 'add_to_cart', 'begin_checkout', 'order_submitted'] as $stage)
                        @php($count = $r['funnel'][$stage] ?? 0)
                        <div><span>{{ __('traffic.events.'.$stage) }}</span><meter min="0" max="{{ max(1, $r['totals']->visitors) }}" value="{{ $count }}"></meter><strong>{{ $count }}</strong></div>
                    @endforeach
                </div>
                <p class="umami-analytics-note">{{ __('traffic.conversion') }}: <strong>{{ number_format($r['totals']->visitors ? ($r['funnel']['order_submitted'] ?? 0) / $r['totals']->visitors * 100 : 0, 1, ',', ' ') }}%</strong></p>
            </section>
            <section class="umami-analytics-section">
                <h2>{{ __('traffic.sources') }}</h2>
                <div class="umami-table-scroll"><table class="umami-orders-table">
                    <thead><tr><th>{{ __('traffic.source') }}</th><th>{{ __('traffic.visitors') }}</th><th>{{ __('traffic.orders') }}</th></tr></thead>
                    <tbody>@forelse($r['sources'] as $row)<tr><td>{{ \App\Services\WebsiteAnalyticsReport::sourceLabel($row->source) }}</td><td>{{ $row->visitors }}</td><td>{{ $row->orders }}</td></tr>@empty<tr><td colspan="3">{{ __('traffic.no_data') }}</td></tr>@endforelse</tbody>
                </table></div>
            </section>
            <section class="umami-analytics-section">
                <h2>{{ __('traffic.pages') }}</h2>
                <div class="umami-table-scroll"><table class="umami-orders-table">
                    <thead><tr><th>{{ __('traffic.page') }}</th><th>{{ __('traffic.views') }}</th><th>{{ __('traffic.visitors') }}</th></tr></thead>
                    <tbody>@forelse($r['pages'] as $row)<tr><td class="umami-traffic-path">{{ $row->path }}</td><td>{{ $row->views }}</td><td>{{ $row->visitors }}</td></tr>@empty<tr><td colspan="3">{{ __('traffic.no_data') }}</td></tr>@endforelse</tbody>
                </table></div>
            </section>
            <section class="umami-analytics-section">
                <h2>{{ __('traffic.products') }}</h2>
                <div class="umami-table-scroll"><table class="umami-orders-table">
                    <thead><tr><th>{{ __('traffic.product') }}</th><th>{{ __('traffic.views') }}</th><th>{{ __('traffic.additions') }}</th></tr></thead>
                    <tbody>@forelse($r['products'] as $row)<tr><td class="umami-analytics-name">{{ $row->name }}</td><td>{{ $row->views }}</td><td>{{ $row->additions }}</td></tr>@empty<tr><td colspan="3">{{ __('traffic.no_data') }}</td></tr>@endforelse</tbody>
                </table></div>
            </section>
            <section class="umami-analytics-section">
                <h2>{{ __('traffic.device') }}</h2>
                <div class="umami-analytics-distribution">
                    @foreach(['mobile', 'desktop', 'tablet'] as $device)
                        @php($count = $r['devices']->firstWhere('device', $device)?->visitors ?? 0)
                        <div><span>{{ __('traffic.devices.'.$device) }}</span><meter min="0" max="{{ max(1, $r['totals']->visitors) }}" value="{{ $count }}"></meter><strong>{{ $count }}</strong></div>
                    @endforeach
                </div>
            </section>
            <section class="umami-analytics-section">
                <h2>{{ __('traffic.campaigns') }}</h2>
                <div class="umami-table-scroll"><table class="umami-orders-table">
                    <thead><tr><th>{{ __('traffic.campaign') }}</th><th>{{ __('traffic.visitors') }}</th><th>{{ __('traffic.orders') }}</th></tr></thead>
                    <tbody>@forelse($r['campaigns'] as $row)<tr><td class="umami-traffic-path">{{ $row->campaign }}</td><td>{{ $row->visitors }}</td><td>{{ $row->orders }}</td></tr>@empty<tr><td colspan="3">{{ __('traffic.no_data') }}</td></tr>@endforelse</tbody>
                </table></div>
            </section>
        </div>
        <p class="umami-analytics-note">{{ __('traffic.definition') }}</p>
        <p class="umami-analytics-note">{{ __('traffic.retention', ['days' => config('website_analytics.retention_days')]) }} @if($r['firstDate']) {{ __('traffic.since') }} {{ $r['firstDate'] }}. @endif</p>
    </div>
</x-filament-panels::page>
