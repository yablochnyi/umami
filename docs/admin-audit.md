# Admin Activity Journal

`/admin/activity-log` is restricted to the protected administrator role.
It is not an assignable role permission. Access is checked on initial requests,
Livewire hydration, table construction and detail actions. The account filter
uses the immutable account ID; stored email/name snapshots remain available
after renames and account deletion. Dates are filtered and displayed in
Europe/Warsaw, stored in UTC.

## Events

- Successful login (including remember-me restoration) and explicit logout.
- Successful authenticated page GETs: section, record, edit/create form opened.
- Creates, updates and deletes of managed Eloquent resources, with changed
  fields and before/after values. Opening an edit form is not a saved change.
- Analytics detail views, export requests and requested synchronisation.

Verified persistent Filament middleware supplies actor context to real
Livewire requests. Polling, filtering and other component updates do not
produce repeated page views. Public visitors, order webhooks and background
syncs are not attributed to a staff user. The login page discloses logging.

Only explicitly allowlisted resource fields are copied. Password changes are
marked without storing passwords/hashes. Unknown settings values, customer
contact/address values and integration payloads are excluded or redacted.
No request bodies, URLs/query strings, IP addresses or session tokens are stored.
Rendered values are escaped. History entries cannot be edited or deleted through
the admin UI or ordinary model operations; this is not protection against
someone with direct database/server access.

Audit writes share the current database transaction with panel edits, so
rolled-back edits leave no audit entry. Missing-table checks allow installation
before the migration finishes without breaking the public website.

## Scope and Retention

History starts when deployed; it cannot reconstruct earlier actions.
Closing a browser or letting a session expire is not an explicit logout event.
Page opening does not prove someone read every field or how long they looked.
Individual searches, filter values, keystrokes and each table row are not logged.
New resources or mass SQL updates require explicit audit integration.

No automatic pruning is enabled. The operator should set an appropriate
retention policy and staff-access notice as part of its security procedures.
The journal is stored in the application database and covered by its backups.

## Deployment / Verification

Run `php artisan migrate --force`, rebuild/deploy Vite assets, clear Filament
component discovery and rebuild the route cache when deploying the new page.
Existing GoOrder integration flags and scheduled synchronisation are unchanged.

`php artisan test --filter=AdminAuditTest` includes actual signed Livewire HTTP
login, logout and save requests, access checks, transaction rollback,
redaction, before/after values and time zone filter boundaries.
