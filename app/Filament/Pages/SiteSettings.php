<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SiteSettings extends Page
{
    protected static ?string $slug = 'ustawienia-strony';

    protected static ?string $navigationLabel = 'Ustawienia strony';

    protected static ?string $title = 'Ustawienia strony';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament-panels::pages.page';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SiteSetting::query()
            ->whereIn('key', ['opening_time', 'closing_time'])
            ->orWhereIn('key', [
                'delivery_opening_time',
                'delivery_cost',
                'free_delivery_from',
                'minimum_delivery_amount',
                'restaurant_latitude',
                'restaurant_longitude',
                'delivery_tier_1_max_km',
                'delivery_tier_1_cost',
                'delivery_tier_2_max_km',
                'delivery_tier_2_cost',
                'delivery_tier_3_cost',
            ])
            ->pluck('value', 'key');

        $this->form->fill([
            'opening_time' => $settings['opening_time'] ?? '12:00',
            'closing_time' => $settings['closing_time'] ?? '20:30',
            'delivery_opening_time' => $settings['delivery_opening_time'] ?? '13:00',
            'delivery_cost' => $settings['delivery_cost'] ?? '0',
            'free_delivery_from' => $settings['free_delivery_from'] ?? '0',
            'minimum_delivery_amount' => $settings['minimum_delivery_amount'] ?? '0',
            'restaurant_latitude' => $settings['restaurant_latitude'] ?? '53.0217',
            'restaurant_longitude' => $settings['restaurant_longitude'] ?? '18.6676',
            'delivery_tier_1_max_km' => $settings['delivery_tier_1_max_km'] ?? '3',
            'delivery_tier_1_cost' => $settings['delivery_tier_1_cost'] ?? '9.99',
            'delivery_tier_2_max_km' => $settings['delivery_tier_2_max_km'] ?? '8',
            'delivery_tier_2_cost' => $settings['delivery_tier_2_cost'] ?? '14.99',
            'delivery_tier_3_cost' => $settings['delivery_tier_3_cost'] ?? '24.99',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Godziny pracy restauracji')
                    ->description('Zamówienia można składać cały czas, ale te godziny ograniczają najbliższy możliwy odbiór i dostawę.')
                    ->schema([
                        TimePicker::make('opening_time')
                            ->label('Otwarte od')
                            ->seconds(false)
                            ->format('H:i')
                            ->displayFormat('H:i')
                            ->required(),
                        TimePicker::make('closing_time')
                            ->label('Otwarte do')
                            ->seconds(false)
                            ->format('H:i')
                            ->displayFormat('H:i')
                            ->required()
                            ->after('opening_time'),
                        TimePicker::make('delivery_opening_time')
                            ->label('Dostawa od')
                            ->seconds(false)
                            ->format('H:i')
                            ->displayFormat('H:i')
                            ->required(),
                    ])
                    ->columns(3),
                Section::make('Dostawa')
                    ->description('Kwoty używane przy koszyku i późniejszym składaniu zamówienia.')
                    ->schema([
                        TextInput::make('delivery_cost')
                            ->label('Koszt dostawy')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('zł')
                            ->required(),
                        TextInput::make('free_delivery_from')
                            ->label('Darmowa dostawa od')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('zł')
                            ->required(),
                        TextInput::make('minimum_delivery_amount')
                            ->label('Minimalna kwota dostawy')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('zł')
                            ->required(),
                    ])
                    ->columns(3),
                Section::make('Dostawa według odległości')
                    ->description('Odległość liczona jest od restauracji do adresu dostawy. Te wartości zastępują stały koszt dostawy.')
                    ->schema([
                        TextInput::make('restaurant_latitude')
                            ->label('Szerokość restauracji')
                            ->numeric()
                            ->required(),
                        TextInput::make('restaurant_longitude')
                            ->label('Długość restauracji')
                            ->numeric()
                            ->required(),
                        TextInput::make('delivery_tier_1_max_km')
                            ->label('Pierwszy próg do')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('km')
                            ->required(),
                        TextInput::make('delivery_tier_1_cost')
                            ->label('Cena pierwszego progu')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('zł')
                            ->required(),
                        TextInput::make('delivery_tier_2_max_km')
                            ->label('Drugi próg do')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('km')
                            ->required(),
                        TextInput::make('delivery_tier_2_cost')
                            ->label('Cena drugiego progu')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('zł')
                            ->required(),
                        TextInput::make('delivery_tier_3_cost')
                            ->label('Cena powyżej drugiego progu')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('zł')
                            ->required(),
                    ])
                    ->columns(3),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Zapisz')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->saveSetting('opening_time', 'Otwarte od', $data['opening_time']);
        $this->saveSetting('closing_time', 'Otwarte do', $data['closing_time']);
        $this->saveSetting('delivery_opening_time', 'Dostawa od', $data['delivery_opening_time']);
        $this->saveSetting('delivery_cost', 'Koszt dostawy', (string) $data['delivery_cost'], 'number', 22);
        $this->saveSetting('free_delivery_from', 'Darmowa dostawa od', (string) $data['free_delivery_from'], 'number', 23);
        $this->saveSetting('minimum_delivery_amount', 'Minimalna kwota dostawy', (string) $data['minimum_delivery_amount'], 'number', 24);
        $this->saveSetting('restaurant_latitude', 'Szerokość geograficzna restauracji', (string) $data['restaurant_latitude'], 'number', 25);
        $this->saveSetting('restaurant_longitude', 'Długość geograficzna restauracji', (string) $data['restaurant_longitude'], 'number', 26);
        $this->saveSetting('delivery_tier_1_max_km', 'Dostawa próg 1 do km', (string) $data['delivery_tier_1_max_km'], 'number', 27);
        $this->saveSetting('delivery_tier_1_cost', 'Dostawa do 3 km', (string) $data['delivery_tier_1_cost'], 'number', 28);
        $this->saveSetting('delivery_tier_2_max_km', 'Dostawa próg 2 do km', (string) $data['delivery_tier_2_max_km'], 'number', 29);
        $this->saveSetting('delivery_tier_2_cost', 'Dostawa 3-8 km', (string) $data['delivery_tier_2_cost'], 'number', 30);
        $this->saveSetting('delivery_tier_3_cost', 'Dostawa powyżej 8 km', (string) $data['delivery_tier_3_cost'], 'number', 31);

        Notification::make()
            ->success()
            ->title('Ustawienia zapisane')
            ->send();
    }

    private function saveSetting(string $key, string $label, string $value, string $type = 'time', ?int $sortOrder = null): void
    {
        SiteSetting::query()->updateOrCreate(
            ['key' => $key],
            [
                'group' => 'restaurant',
                'label' => $label,
                'value' => $value,
                'type' => $type,
                'sort_order' => $sortOrder ?? ($key === 'opening_time' ? 20 : 21),
            ],
        );
    }
}
