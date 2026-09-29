<form method="POST" action="{{ route('admin.language') }}" class="umami-language" aria-label="{{ __('admin.language') }}">
    @csrf
    @foreach (['pl' => 'PL', 'uk' => 'UA'] as $locale => $label)
        <button type="submit" name="locale" value="{{ $locale }}" aria-pressed="{{ app()->getLocale() === $locale ? 'true' : 'false' }}" title="{{ $locale === 'pl' ? 'Polski' : 'Українська' }}">{{ $label }}</button>
    @endforeach
</form>
