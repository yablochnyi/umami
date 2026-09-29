<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class SiteSettings extends Page
{
    protected static ?string $slug = 'ustawienia-strony';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament-panels::pages.page';

    public ?array $data = [];

    private const DEFAULTS = [
        'opening_time' => '12:00', 'closing_time' => '20:30', 'delivery_opening_time' => '13:00',
        'free_delivery_from' => '0', 'minimum_delivery_amount' => '0',
        'restaurant_latitude' => '53.0217', 'restaurant_longitude' => '18.6676',
        'delivery_tier_1_max_km' => '3', 'delivery_tier_1_cost' => '9.99',
        'delivery_tier_2_max_km' => '8', 'delivery_tier_2_cost' => '14.99', 'delivery_tier_3_cost' => '24.99',
        'delivery_tier_1_streets' => "Generała Karola Kniaziewicza\nWojciecha Korfantego\nSzuwarów\nAkacjowa",
        'delivery_tier_2_streets' => '', 'delivery_tier_3_streets' => '',
    ];

    public static function getNavigationLabel(): string
    {
        return __('admin.settings');
    }

    public function getTitle(): string
    {
        return __('admin.settings');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.management');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAdminPermission('site_settings.view') ?? false;
    }

    public function hydrate(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
        $stored = SiteSetting::query()->whereIn('key', array_keys(self::DEFAULTS))->pluck('value', 'key')->all();
        $this->form->fill(array_replace(self::DEFAULTS, $stored));
    }

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->statePath('data')
            ->disabled(fn () => ! auth()->user()?->hasAdminPermission('site_settings.update'))
            ->components([
                Tabs::make('restaurant-settings')->contained(false)->extraAttributes(['class' => 'umami-settings-tabs'])->persistTabInQueryString('section')->tabs([
                    Tab::make(__('settings.hours'))->id('hours')->icon(Heroicon::OutlinedClock)->schema([
                        Section::make(__('settings.hours'))->description('Europe/Warsaw')->columns(['default' => 1, 'md' => 3])->schema([
                            TimePicker::make('opening_time')->label(__('settings.operational.opening_time'))->seconds(false)->format('H:i')->displayFormat('H:i')->required(),
                            TimePicker::make('closing_time')->label(__('settings.operational.closing_time'))->seconds(false)->format('H:i')->displayFormat('H:i')->required()->after('opening_time'),
                            TimePicker::make('delivery_opening_time')->label(__('settings.operational.delivery_opening_time'))->seconds(false)->format('H:i')->displayFormat('H:i')->required(),
                        ]),
                    ]),
                    Tab::make(__('settings.delivery'))->id('delivery')->icon(Heroicon::OutlinedTruck)->schema([
                        Section::make(__('settings.order_conditions'))->columns(['default' => 1, 'md' => 2])->schema([
                            self::money('minimum_delivery_amount'),
                            self::money('free_delivery_from')->helperText(__('settings.free_delivery_hint')),
                        ]),
                        ...array_map(fn (int $zone) => $this->zone($zone), [1, 2, 3]),
                    ]),
                    Tab::make(__('settings.location'))->id('location')->icon(Heroicon::OutlinedMapPin)->schema([
                        Section::make(__('settings.restaurant_location'))->description(__('settings.location_hint'))->columns(['default' => 1, 'md' => 2])->schema([
                            TextInput::make('restaurant_latitude')->label(__('settings.operational.restaurant_latitude'))->numeric()->minValue(-90)->maxValue(90)->required(),
                            TextInput::make('restaurant_longitude')->label(__('settings.operational.restaurant_longitude'))->numeric()->minValue(-180)->maxValue(180)->required(),
                        ]),
                    ]),
                ]),
            ]);
    }

    private static function money(string $key): TextInput
    {
        return TextInput::make($key)->label(__('settings.operational.'.$key))->numeric()->minValue(0)->suffix('zł')->required();
    }

    private function zone(int $zone): Section
    {
        $fields = [];
        if ($zone < 3) {
            $distance = TextInput::make("delivery_tier_{$zone}_max_km")->label(__('settings.zone_radius'))->numeric()->minValue(0.1)->suffix('km')->required()->live(onBlur: true);
            if ($zone === 2) {
                $distance->gt('delivery_tier_1_max_km');
            }
            $fields[] = $distance;
        }
        $fields[] = self::money("delivery_tier_{$zone}_cost")->label(__('settings.zone_price'));
        $fields[] = Textarea::make("delivery_tier_{$zone}_streets")->label(__('settings.zone_streets'))->helperText(__('settings.streets_hint'))->rows(3)->columnSpanFull();

        return Section::make(__('settings.zone', ['number' => $zone]))
            ->description(fn (Get $get) => match ($zone) {
                1 => __('settings.range_first', ['to' => $get('delivery_tier_1_max_km')]),
                2 => __('settings.range_second', ['from' => $get('delivery_tier_1_max_km'), 'to' => $get('delivery_tier_2_max_km')]),
                3 => __('settings.range_last', ['from' => $get('delivery_tier_2_max_km')]),
            })->columns(['default' => 1, 'md' => 2])->schema($fields);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])->id('form')->livewireSubmitHandler('save')->footer([
                Actions::make([Action::make('save')->visible(fn () => auth()->user()?->hasAdminPermission('site_settings.update'))
                    ->label(__('Zapisz'))->submit('save')->keyBindings(['mod+s'])]),
            ]),
        ]);
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->hasAdminPermission('site_settings.update'), 403);
        $data = $this->form->getState();
        DB::transaction(function () use ($data): void {
            foreach (array_keys(self::DEFAULTS) as $index => $key) {
                $type = str_ends_with($key, '_time') ? 'time' : (str_ends_with($key, '_streets') ? 'textarea' : 'number');
                $setting = SiteSetting::firstOrCreate(['key' => $key], [
                    'group' => 'restaurant', 'label' => trans('settings.operational.'.$key, [], 'pl'), 'type' => $type, 'sort_order' => 20 + $index,
                ]);
                $setting->update(['value' => (string) ($data[$key] ?? '')]);
            }
        });
        Notification::make()->success()->title(__('Ustawienia zapisane'))->send();
    }
}
