<x-filament-panels::page>
    @php($r = $this->report())
    <div class="umami-analytics-meta" wire:poll.60s>
        <span>{{ __('analytics.archive') }}: {{ config('analytics.from') }} · Europe/Warsaw</span>
        <span>{{ __('analytics.nightly') }}: {{ config('analytics.time') }}</span>
        <span>{{ __('analytics.updated') }}: {{ $r['lastSuccess']?->finished_at?->timezone('Europe/Warsaw')->format('d.m.Y H:i') ?? '—' }}</span>
        @if($r['queued']) <strong>{{ __('analytics.queued') }}</strong> @endif
        @if($r['lastRun'] && $r['lastRun']->status !== 'completed')
            <strong role="status">{{ __('analytics.sync_states.'.$r['lastRun']->status) }} · {{ $r['lastRun']->orders_count }}</strong>
        @endif
    </div>
    <details class="umami-analytics-filters"><summary>{{ __('analytics.filters') }} · {{ $this->getTableFilterState('period')['from'] ?? '' }} — {{ $this->getTableFilterState('period')['to'] ?? '' }}</summary>{{ $this->getTableFiltersForm() }}</details>
    <p class="umami-analytics-note">{{ __('analytics.definition') }}</p>
    <dl class="umami-metrics umami-analytics-metrics">
        <div><dt><x-heroicon-o-shopping-bag />{{ __('analytics.orders') }}</dt><dd>{{ number_format($r['totals']->orders_count, 0, ',', ' ') }}</dd></div>
        <div><dt><x-heroicon-o-banknotes />{{ __('analytics.total') }}</dt><dd>{{ $this::money($r['totals']->total, $r['currency']) }}</dd></div>
        <div><dt><x-heroicon-o-credit-card />{{ __('analytics.paid') }}</dt><dd>{{ $this::money($r['totals']->paid, $r['currency']) }}</dd></div>
        <div><dt><x-heroicon-o-calculator />{{ __('analytics.average') }}</dt><dd>{{ $this::money($r['totals']->orders_count ? $r['totals']->total / $r['totals']->orders_count : 0, $r['currency']) }}</dd></div>
    </dl>
    <section class="umami-analytics-section">
        <h2>{{ __('analytics.daily') }}</h2>
        @if($r['daily']->isEmpty()) <p>{{ __('analytics.empty') }}</p> @else
        <div class="umami-analytics-chart" role="img" aria-label="{{ __('analytics.daily') }}">
            @foreach($r['daily'] as $day)
                <div class="umami-analytics-bar" title="{{ $day->business_date }}: {{ $this::money($day->total, $r['currency']) }} · {{ $day->orders_count }} {{ __('analytics.orders') }}">
                    <span>{{ $day->orders_count }}</span>
                    <i style="height: {{ max(2, (float) $day->total / max(1, $r['daily']->max('total')) * 150) }}px"></i>
                    <small>{{ substr($day->business_date, 5) }}</small>
                </div>
            @endforeach
        </div>
        @endif
    </section>
    <div class="umami-analytics-grid">
        <section class="umami-analytics-section">
            <h2>{{ __('analytics.channels') }}</h2>
            <div class="umami-table-scroll"><table class="umami-orders-table">
                <thead><tr><th>{{ __('analytics.channel') }}</th><th>{{ __('analytics.orders') }}</th><th>{{ __('analytics.total') }}</th><th>%</th></tr></thead>
                <tbody>@forelse($r['channels'] as $channel)<tr>
                    <td>{{ $this::channel($channel->source) }}</td><td>{{ $channel->orders_count }}</td><td>{{ $this::money($channel->total, $r['currency']) }}</td>
                    <td>{{ number_format($r['totals']->total > 0 ? $channel->total / $r['totals']->total * 100 : 0, 1) }}%</td>
                </tr>@empty<tr><td colspan="4">{{ __('analytics.empty') }}</td></tr>@endforelse</tbody>
            </table></div>
        </section>
        <section class="umami-analytics-section">
            <h2>{{ __('analytics.products') }}</h2>
            <div class="umami-table-scroll"><table class="umami-orders-table">
                <thead><tr><th>{{ __('analytics.dish') }}</th><th>{{ __('analytics.quantity') }}</th><th>{{ __('analytics.line_total') }}</th></tr></thead>
                <tbody>@forelse($r['items'] as $item)<tr><td class="umami-analytics-name">{{ $item->name }}</td><td>{{ (float) $item->quantity }}</td><td>{{ $this::money($item->total, $r['currency']) }}</td></tr>
                @empty<tr><td colspan="3">{{ __('analytics.empty') }}</td></tr>@endforelse</tbody>
            </table></div>
        </section>
        <section class="umami-analytics-section">
            <h2>{{ __('analytics.weekdays') }}</h2>
            <div class="umami-analytics-distribution">
                @foreach(range(1,7) as $weekday)
                    @php($count = $r['weekdays']->firstWhere('weekday', $weekday)?->orders_count ?? 0)
                    <div><span>{{ __('analytics.days.'.$weekday) }}</span><meter min="0" max="{{ max(1, $r['weekdays']->max('orders_count')) }}" value="{{ $count }}"></meter><strong>{{ $count }}</strong></div>
                @endforeach
            </div>
        </section>
        <section class="umami-analytics-section">
            <h2>{{ __('analytics.hours') }}</h2>
            <div class="umami-analytics-hour-grid">
                @foreach(range(0,23) as $hour)
                    @php($count = $r['hours']->firstWhere('hour', $hour)?->orders_count ?? 0)
                    <div style="--activity: {{ $count / max(1, $r['hours']->max('orders_count')) }}"><span>{{ sprintf('%02d:00', $hour) }}</span><strong>{{ $count }}</strong></div>
                @endforeach
            </div>
        </section>
    </div>
    <section class="umami-analytics-section"><h2>{{ __('analytics.orders') }}</h2>{{ $this->table }}</section>
</x-filament-panels::page>
