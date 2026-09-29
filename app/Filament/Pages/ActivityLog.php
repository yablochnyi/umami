<?php

namespace App\Filament\Pages;

use App\Models\AdminAuditLog;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
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

class ActivityLog extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $slug = 'activity-log';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.activity-log';

    public static function getNavigationLabel(): string
    {
        return __('audit.title');
    }

    public function getTitle(): string
    {
        return __('audit.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.management');
    }

    public static function canAccess(): bool
    {
        return auth()->id() && User::whereKey(auth()->id())->whereHas('role', fn ($query) => $query->where('is_admin', true))->exists();
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    public function hydrate(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    public function table(Table $table): Table
    {
        abort_unless(static::canAccess(), 403);

        return $table->query(AdminAuditLog::query())
            ->defaultSort('id', 'desc')->deferFilters(false)
            ->columns([
                TextColumn::make('created_at')->label(__('audit.date'))->dateTime('d.m.Y H:i:s')->timezone('Europe/Warsaw')->sortable(),
                TextColumn::make('actor_email')->label(__('audit.account'))->description(fn (AdminAuditLog $record) => $record->actor_name)->searchable()->wrap(),
                TextColumn::make('action')->label(__('audit.action'))->formatStateUsing(fn ($state) => __('audit.actions.'.$state))->badge()
                    ->color(fn ($state) => match ($state) {
                        'created', 'login' => 'success', 'updated' => 'warning', 'deleted' => 'danger', default => 'gray'
                    }),
                TextColumn::make('resource')->label(__('audit.resource'))->formatStateUsing(fn ($state) => self::resourceLabel($state)),
                TextColumn::make('subject_label')->label(__('audit.record'))->state(fn (AdminAuditLog $record) => self::subjectLabel($record))
                    ->description(fn (AdminAuditLog $record) => $record->page ? __('audit.pages.'.$record->page) : ($record->subject_id ? '#'.$record->subject_id : null))->wrap()->limit(70),
                TextColumn::make('changes')->label(__('audit.changed'))->state(fn (AdminAuditLog $record) => implode(', ', array_map(fn ($field) => self::fieldLabel($field), array_keys($record->changes ?? []))))->wrap()->limit(100),
            ])
            ->filters([
                SelectFilter::make('actor_id')->label(__('audit.account'))->searchable()->preload()->options(function () {
                    $history = AdminAuditLog::whereIn('id', AdminAuditLog::selectRaw('MAX(id)')->groupBy('actor_id'))
                        ->get(['actor_id', 'actor_email', 'actor_name'])->mapWithKeys(fn ($log) => [$log->actor_id => $log->actor_email.' · '.$log->actor_name]);

                    return $history->replace(User::orderBy('email')->get(['id', 'email', 'name'])->mapWithKeys(fn ($user) => [$user->id => $user->email.' · '.$user->name]))->sort()->all();
                }),
                SelectFilter::make('action')->label(__('audit.action'))->options(__('audit.actions')),
                SelectFilter::make('resource')->label(__('audit.resource'))->options([...__('admin.resources'), ...__('audit.resources')]),
                Filter::make('period')->columns(['default' => 1, 'md' => 2])->columnSpan(['default' => 1, 'md' => 2])->schema([
                    DatePicker::make('from')->label(__('audit.from')),
                    DatePicker::make('to')->label(__('audit.to'))->afterOrEqual('from'),
                ])->query(function (Builder $query, array $data): Builder {
                    foreach (['from', 'to'] as $key) {
                        if (filled($data[$key] ?? null)) {
                            try {
                                $date = CarbonImmutable::createFromFormat('!Y-m-d', $data[$key], 'Europe/Warsaw');
                                if (! $date || $date->format('Y-m-d') !== $data[$key]) {
                                    return $query->whereRaw('1 = 0');
                                }
                            } catch (\Throwable) {
                                return $query->whereRaw('1 = 0');
                            }
                            $query->where('created_at', $key === 'from' ? '>=' : '<', ($key === 'to' ? $date->addDay() : $date)->utc());
                        }
                    }

                    return $query;
                }),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(['default' => 1, 'md' => 2, 'xl' => 5])
            ->recordActions([
                Action::make('details')->label(__('audit.details'))->icon(Heroicon::OutlinedEye)->iconButton()
                    ->modalHeading(__('audit.details'))->modalWidth('5xl')
                    ->modalContent(function (AdminAuditLog $record) {
                        abort_unless(static::canAccess(), 403);

                        return view('filament.partials.audit-details', ['entry' => $record]);
                    })->modalSubmitAction(false)->modalCancelActionLabel(__('audit.close')),
            ])->emptyStateHeading(__('audit.empty'))->paginated([25, 50, 100]);
    }

    public static function resourceLabel(string $resource): string
    {
        return __('admin.resources')[$resource] ?? __('audit.resources')[$resource] ?? $resource;
    }

    public static function subjectLabel(AdminAuditLog $entry): string
    {
        if ($entry->resource === 'site_texts' && $entry->subject_label) {
            return __('texts.blocks')[$entry->subject_label] ?? __('texts.other_block');
        }
        if ($entry->resource === 'site_settings' && $entry->subject_label) {
            return __('settings.labels')[$entry->subject_label] ?? __('settings.operational')[$entry->subject_label] ?? '#'.$entry->subject_id;
        }

        return $entry->subject_label ?: ($entry->subject_id ? '#'.$entry->subject_id : '');
    }

    public static function fieldLabel(string $field): string
    {
        return __('audit.fields')[$field] ?? $field;
    }

    public static function displayValue(mixed $value): string
    {
        if ($value === null) {
            return __('audit.none');
        }
        if (is_bool($value)) {
            return $value ? __('audit.yes') : __('audit.no');
        }

        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) : (string) $value;
    }
}
