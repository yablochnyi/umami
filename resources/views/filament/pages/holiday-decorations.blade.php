<x-filament-panels::page>
    <div class="umami-holidays">
        @foreach ($holidays as $slug => $holiday)
            <section class="umami-holiday" aria-labelledby="holiday-title-{{ $slug }}" wire:key="holiday-{{ $slug }}">
                <img class="umami-holiday-art" src="{{ asset($holiday['preview']) }}" alt="" width="160" height="107">
                <div class="umami-holiday-name">
                    <h2 id="holiday-title-{{ $slug }}">{{ __('holidays.names.'.$slug) }}</h2>
                    <span>{{ __('holidays.'.($holiday['enabled'] ? 'enabled' : 'disabled')) }}</span>
                </div>
                <div wire:key="holiday-switch-{{ $slug }}-{{ $holiday['enabled'] ? 'on' : 'off' }}">
                    <x-filament::toggle
                        :state="$holiday['enabled'] ? 'true' : 'false'"
                        :aria-checked="$holiday['enabled'] ? 'true' : 'false'"
                        aria-labelledby="holiday-title-{{ $slug }}"
                        :disabled="! auth()->user()?->hasAdminPermission('site_settings.update')"
                        on-color="success"
                        wire:click="setHoliday('{{ $slug }}', {{ $holiday['enabled'] ? 'false' : 'true' }})"
                        wire:loading.attr="disabled"
                        wire:target="setHoliday"
                    />
                </div>
            </section>
        @endforeach
    </div>
</x-filament-panels::page>
