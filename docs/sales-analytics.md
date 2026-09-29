# Sales Analytics

`/admin/sales-analytics` is restricted to the protected administrator (`is_admin`),
including Livewire requests, order details, refresh requests and CSV export.
No assignable staff permission exposes this data. Labels support Polish/Ukrainian.

## Data And Definitions

- Source: GoPOS API v3 orders. API reference:
  https://app.gopos.io/doc/swagger-ui/index.html
- `analytics_orders` is separate from operational website orders, preventing double
  counting. Original source values are retained; UMAMI_WWW is displayed as the site.
  GoOrder is separate and may include orders originating from external storefronts.
- `analytics_items` stores active top-level order lines, not modifier/ingredient
  recipes. Service, packaging and delivery lines are retained. Product rankings
  use line value and original names, so historical names may appear separately.
- No names, customer addresses, phone numbers, comments or public receipt links are
  stored. Payment records contain only method, status and amount.
- Monetary amounts are integer minor units. Different currencies are never summed.
  Paid totals reflect GoPOS records, not bank settlement, platform remittances or
  cash remaining in the drawer. No commission, food cost or profit is inferred.
- Metrics default to CLOSED orders, PLN, last 30 calendar dates including today.
  Changing status can include cancelled/open records; all metrics and exports use
  the same filters and order-number search. Full receipt totals apply even when
  filtering by one dish; the dish filter selects receipts containing that dish.
- Order creation times without offsets are interpreted in Europe/Warsaw, matching
  GoPOS wall-clock timestamps. UTC instants and indexed local day/hour/weekday are
  retained. Analysis is by creation date, not payment or fiscal-report date.

## Sync And Deployment

```sh
php artisan migrate --force
php artisan filament:clear-cached-components
php artisan route:cache
php artisan view:clear
php artisan analytics:sync --from=2026-05-01
```

Ship `public/build` from `npm ci && npm run build` together with the code. No seeding
or updates to existing checkout integrations are needed. Back up the database
before migrating. This migration only adds the three analytics tables.

The existing Laravel scheduler cron is required (every minute). Nightly refresh is
03:40 Europe/Warsaw. Config: ANALYTICS_FROM (2026-05-01), ANALYTICS_SYNC_TIME (03:40),
ANALYTICS_SYNC_ENABLED (true). A separate `analytics:sync --requested` scheduled
command consumes authenticated admin refresh requests; no queue worker is needed.

Each refresh rereads the whole configured history to pick up old corrections and
cancellations. It takes an upper ID snapshot, pages by exclusive `id_from` and
inclusive `id_to`, validates monotonic pagination and retries failed GETs three
times. Only GET requests are sent. There is no arbitrary page limit or last-100
truncation. Orders are upserted by organization/GoPOS ID; lines are replaced in an
order-level transaction. Repeated sync is safe. Physical deletions not returned by
the API cannot be inferred; GoPOS REMOVED/VOIDED statuses are retained when returned.

A shared cache lock prevents overlapping CLI/nightly/manual runs for two hours.
Keep a single scheduler host, and run each import within that bound. A failed run
leaves previously imported records intact; already-refreshed records stay updated.
The UI shows incomplete/failed runs and the time of the last complete run. Retry a
failed import with the same command. Logs: storage/logs/analytics-sync.log. Private
API response bodies are not copied into sync diagnostics.

```sh
php artisan test --filter='SalesAnalyticsTest|AdminPanelTest|SiteTextsAdminTest|SiteSettingsAdminTest|GoOrderMenuSyncTest|GoOrderTest'
```
