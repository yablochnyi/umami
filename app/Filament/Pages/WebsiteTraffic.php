<?php

namespace App\Filament\Pages;

use App\Services\AdminAudit;
use App\Services\WebsiteAnalyticsReport;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WebsiteTraffic extends Page
{
    protected static ?string $slug = 'website-traffic';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.website-traffic';

    public ?array $data = [];

    #[Locked]
    public array $filters = [];

    public static function getNavigationLabel(): string
    {
        return __('traffic.title');
    }

    public function getTitle(): string
    {
        return __('traffic.title');
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
        $this->setPeriod(1);
    }

    public function hydrate(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->columns(['default' => 1, 'md' => 2, 'xl' => 4])->components([
            DatePicker::make('from')->label(__('traffic.from'))->required()->maxDate(now('Europe/Warsaw'))->rules(['date_format:Y-m-d']),
            DatePicker::make('to')->label(__('traffic.to'))->required()->maxDate(now('Europe/Warsaw'))->afterOrEqual('from')->rules(['date_format:Y-m-d']),
            Select::make('source')->label(__('traffic.source'))->placeholder(__('traffic.all'))->options(fn () => app(WebsiteAnalyticsReport::class)->sources()),
            Select::make('device')->label(__('traffic.device'))->placeholder(__('traffic.all'))->options(__('traffic.devices')),
        ]);
    }

    public function setPeriod(int $days): void
    {
        abort_unless(static::canAccess(), 403);
        abort_unless(in_array($days, [1, 7, 30], true), 422);
        $this->filters = ['from' => now('Europe/Warsaw')->subDays($days - 1)->toDateString(), 'to' => now('Europe/Warsaw')->toDateString(), 'source' => null, 'device' => null];
        $this->form->fill($this->filters);
    }

    public function applyFilters(): void
    {
        abort_unless(static::canAccess(), 403);
        $data = $this->form->getState();
        if (CarbonImmutable::parse($data['from'])->diffInDays(CarbonImmutable::parse($data['to'])) > 365) {
            throw ValidationException::withMessages(['data.from' => __('traffic.range_error')]);
        }
        $this->filters = $data;
    }

    public function report(): array
    {
        abort_unless(static::canAccess(), 403);

        return app(WebsiteAnalyticsReport::class)->build($this->filters);
    }

    protected function getHeaderActions(): array
    {
        return [Action::make('export')->label(__('traffic.export'))->icon(Heroicon::OutlinedArrowDownTray)->action(fn () => $this->export())];
    }

    public function export(): StreamedResponse
    {
        $report = $this->report();
        app(AdminAudit::class)->record('exported', 'website_traffic');

        return response()->streamDownload(function () use ($report): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [__('traffic.date'), __('traffic.visitors'), __('traffic.visits'), __('traffic.views'), __('traffic.orders')], ';', '"', '');
            foreach ($report['daily'] as $day) {
                fputcsv($out, [$day->business_date, $day->visitors, $day->visits, $day->views, $day->orders], ';', '"', '');
            }
            fclose($out);
        }, 'umami-traffic-'.$this->filters['from'].'-'.$this->filters['to'].'.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'no-store']);
    }
}
