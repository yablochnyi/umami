@php($presenter = \App\Filament\Pages\ActivityLog::class)
<div class="umami-audit-details">
    <dl class="umami-audit-meta">
        <div><dt>{{ __('audit.account') }}</dt><dd>{{ $entry->actor_email }} · {{ $entry->actor_name }}</dd></div>
        <div><dt>{{ __('audit.date') }}</dt><dd>{{ $entry->created_at->timezone('Europe/Warsaw')->format('d.m.Y H:i:s') }} (Europe/Warsaw)</dd></div>
        <div><dt>{{ __('audit.action') }}</dt><dd>{{ __('audit.actions.'.$entry->action) }}</dd></div>
        <div><dt>{{ __('audit.resource') }}</dt><dd>{{ $presenter::resourceLabel($entry->resource) }} {{ $presenter::subjectLabel($entry) }}</dd></div>
    </dl>
    @if($entry->changes)
        <div class="umami-table-scroll">
            <table class="umami-orders-table umami-audit-changes">
                <thead><tr><th>{{ __('audit.field') }}</th><th>{{ __('audit.before') }}</th><th>{{ __('audit.after') }}</th></tr></thead>
                <tbody>
                    @foreach($entry->changes as $field => $change)
                        <tr>
                            <th>{{ $presenter::fieldLabel($field) }}</th>
                            <td><pre>{{ $change['redacted'] ? __('audit.redacted') : $presenter::displayValue($change['before']) }}</pre></td>
                            <td><pre>{{ $change['redacted'] ? __('audit.redacted') : $presenter::displayValue($change['after']) }}</pre></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p>{{ $entry->page ? __('audit.pages.'.$entry->page) : __('audit.no_changes') }}</p>
    @endif
</div>
