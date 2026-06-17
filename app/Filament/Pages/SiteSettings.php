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
            ->orWhereIn('key', ['delivery_cost', 'free_delivery_from'])
            ->pluck('value', 'key');

        $this->form->fill([
            'opening_time' => $settings['opening_time'] ?? '12:00',
            'closing_time' => $settings['closing_time'] ?? '20:30',
            'delivery_cost' => $settings['delivery_cost'] ?? '0',
            'free_delivery_from' => $settings['free_delivery_from'] ?? '0',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Godziny pracy restauracji')
                    ->description('W tych godzinach gość może dodawać dania do koszyka na stronie.')
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
                    ])
                    ->columns(2),
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
                    ])
                    ->columns(2),
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
        $this->saveSetting('delivery_cost', 'Koszt dostawy', (string) $data['delivery_cost'], 'number', 22);
        $this->saveSetting('free_delivery_from', 'Darmowa dostawa od', (string) $data['free_delivery_from'], 'number', 23);

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
