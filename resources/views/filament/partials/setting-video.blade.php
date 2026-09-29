@if (filled($setting->value))
    <figure class="umami-setting-preview" x-data="{ failed: false }">
        <figcaption>{{ __('settings.current_video') }}</figcaption>
        @if (\Illuminate\Support\Facades\Storage::disk('public')->exists($setting->value))
            <video x-show="!failed" x-on:error="failed = true" controls preload="metadata" playsinline src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($setting->value) }}"></video>
            <p x-cloak x-show="failed" role="status">{{ __('settings.video_unavailable') }}</p>
        @else
            <p role="status">{{ __('settings.file_missing') }}</p>
        @endif
    </figure>
@endif
