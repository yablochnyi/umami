<?php

namespace App\Filament\Pages;

use App\Models\AnalyticsItem;
use App\Models\AnalyticsOrder;
use App\Models\AnalyticsSyncRun;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesAnalytics extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $slug = 'sales-analytics';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.sales-analytics';

    public static function getNavigationLabel(): string
    {
        return __('analytics.title');
    }

    public function getTitle(): string
    {
        return __('analytics.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.management');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdministrator() ?? false;
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    public function hydrate(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')->label(__('analytics.export'))->icon(Heroicon::OutlinedArrowDownTray)->action(fn () => $this->export()),
            Action::make('sync')->label(__('analytics.sync'))->icon(Heroicon::OutlinedArrowPath)->requiresConfirmation()->action(fn () => $this->requestSync()),
        ];
    }

    public function requestSync(): void
    {
        abort_unless(static::canAccess(), 403);
        Cache::put('analytics.sync.requested', true, now()->addDay());
        Notification::make()->success()->title(__('analytics.queued'))->send();
    }

    public function table(Table $table): Table
    {
        return $table->query(AnalyticsOrder::query()->where('organization_id', config('gopos.organization_id')))
            ->defaultSort('ordered_at', 'desc')->deferFilters(false)->poll('60s')
            ->columns([
                TextColumn::make('number')->label(__('analytics.order'))->searchable(),
                TextColumn::make('ordered_at')->label(__('analytics.date'))->dateTime('d.m.Y H:i')->timezone('Europe/Warsaw')->sortable(),
                TextColumn::make('source')->label(__('analytics.channel'))->formatStateUsing(fn ($state) => self::channel($state))->badge()->color('gray'),
                TextColumn::make('status')->label(__('analytics.status'))->formatStateUsing(fn ($state) => __('analytics.statuses')[$state] ?? $state),
                TextColumn::make('order_type')->label(__('analytics.type'))->formatStateUsing(fn ($state) => __('analytics.types')[$state] ?? $state),
                TextColumn::make('total_cents')->label(__('analytics.total'))->formatStateUsing(fn ($state, $record) => self::money($state, $record->currency))->sortable(),
                TextColumn::make('paid_cents')->label(__('analytics.paid'))->formatStateUsing(fn ($state, $record) => self::money($state, $record->currency)),
            ])->filters([
                Filter::make('period')->label(__('analytics.period'))->schema([
                    DatePicker::make('from')->label(__('analytics.from'))->default(now('Europe/Warsaw')->subDays(29)->toDateString())->required(),
                    DatePicker::make('to')->label(__('analytics.to'))->default(now('Europe/Warsaw')->toDateString())->required()->afterOrEqual('from'),
                ])->query(function (Builder $query, array $data): Builder {
                    $from = $data['from'] ?? '';
                    $to = $data['to'] ?? '';
                    if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) || $to < $from) {
                        return $query->whereRaw('1 = 0');
                    }

                    return $query->whereBetween('business_date', [$from, $to]);
                }),
                SelectFilter::make('source')->label(__('analytics.channel'))->multiple()->options(fn () => $this->options('source')),
                SelectFilter::make('status')->label(__('analytics.status'))->options(__('analytics.statuses'))->default('CLOSED'),
                SelectFilter::make('order_type')->label(__('analytics.type'))->options(__('analytics.types')),
                SelectFilter::make('weekday')->label(__('analytics.weekday'))->options(__('analytics.days')),
                SelectFilter::make('payment_status')->label(__('analytics.payment_status'))->options(fn () => $this->options('payment_status')),
                SelectFilter::make('currency')->label(__('analytics.currency'))->options(fn () => $this->options('currency') + ['PLN' => 'PLN'])->default('PLN')
                    ->query(fn (Builder $query, array $data) => $query->where('currency', ($data['value'] ?? null) ?: 'PLN')),
                Filter::make('amount')->label(__('analytics.amount'))->schema([
                    TextInput::make('min')->label(__('analytics.min'))->numeric()->minValue(0),
                    TextInput::make('max')->label(__('analytics.max'))->numeric()->minValue(0),
                ])->query(fn (Builder $query, array $data) => $query
                    ->when(is_numeric($data['min'] ?? null), fn ($q) => $q->where('total_cents', '>=', round($data['min'] * 100)))
                    ->when(is_numeric($data['max'] ?? null), fn ($q) => $q->where('total_cents', '<=', round($data['max'] * 100)))),
                Filter::make('dish')->label(__('analytics.dish'))->schema([TextInput::make('name')->label(__('analytics.dish'))])
                    ->query(fn (Builder $query, array $data) => $query->when(filled($data['name'] ?? null), fn ($q) => $q->whereHas('items', fn ($items) => $items->where('name', 'like', '%'.$data['name'].'%')))),
            ], layout: FiltersLayout::Hidden)
            ->filtersFormColumns(['default' => 1, 'md' => 3, 'xl' => 4])
            ->recordActions([
                Action::make('details')->label(__('analytics.details'))->icon(Heroicon::OutlinedEye)->iconButton()
                    ->modalHeading(fn (AnalyticsOrder $record) => __('analytics.order').' '.$record->number)
                    ->modalContent(function (AnalyticsOrder $record) {
                        abort_unless(static::canAccess(), 403);

                        return view('filament.partials.analytics-order', ['order' => $record->load('items')]);
                    })->modalSubmitAction(false)->modalCancelActionLabel(__('analytics.close')),
            ]);
    }

    private function options(string $field): array
    {
        $values = AnalyticsOrder::where('organization_id', config('gopos.organization_id'))->distinct()->orderBy($field)->pluck($field, $field);

        return $values->map(fn ($value) => $field === 'source' ? self::channel($value) : $value)->all();
    }

    public static function channel(string $source): string
    {
        return __('analytics.sources')[$source] ?? $source;
    }

    public static function money(int|float|string|null $cents, string $currency = 'PLN'): string
    {
        return number_format((float) $cents / 100, 2, ',', ' ').' '.$currency;
    }

    public function report(): array
    {
        abort_unless(static::canAccess(), 403);
        $query = $this->getFilteredTableQuery();
        $totals = (clone $query)->selectRaw('COUNT(*) as orders_count, COALESCE(SUM(total_cents),0) as total, COALESCE(SUM(paid_cents),0) as paid')->first();
        $group = fn ($field) => (clone $query)->selectRaw("{$field}, COUNT(*) as orders_count, SUM(total_cents) as total")->groupBy($field)->orderBy($field)->get();
        $items = AnalyticsItem::whereIn('analytics_order_id', (clone $query)->select('analytics_orders.id'))
            ->selectRaw('name, SUM(quantity) as quantity, SUM(total_cents) as total')->groupBy('name')->orderByDesc('total')->limit(12)->get();

        return [
            'totals' => $totals, 'daily' => $group('business_date'), 'channels' => $group('source')->sortByDesc('total'),
            'weekdays' => $group('weekday'), 'hours' => $group('hour'), 'items' => $items,
            'currency' => ($this->getTableFilterState('currency')['value'] ?? null) ?: 'PLN',
            'lastRun' => AnalyticsSyncRun::latest('id')->first(),
            'lastSuccess' => AnalyticsSyncRun::where('status', 'completed')->latest('id')->first(),
            'queued' => Cache::has('analytics.sync.requested'),
        ];
    }

    public function export(): StreamedResponse
    {
        abort_unless(static::canAccess(), 403);
        $query = $this->getFilteredSortedTableQuery();

        return response()->streamDownload(function () use ($query): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Order', 'Date Europe/Warsaw', 'Channel', 'Status', 'Type', 'Currency', 'Total', 'Paid'], ';', '"', '');
            foreach ($query->cursor() as $order) {
                $row = [$order->number, $order->ordered_at->timezone('Europe/Warsaw')->format('Y-m-d H:i:s'), $order->source, $order->status, $order->order_type, $order->currency, number_format($order->total_cents / 100, 2, '.', ''), number_format($order->paid_cents / 100, 2, '.', '')];
                $row = array_map(fn ($value) => preg_match('/^[\s]*[=+@\-]/u', (string) $value) ? "'".$value : $value, $row);
                fputcsv($out, $row, ';', '"', '');
            }
            fclose($out);
        }, 'umami-analytics-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'no-store']);
    }
}
