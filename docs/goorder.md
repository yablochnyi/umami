# GoOrder checkout pilot

## Storefront menu synchronization

`php artisan goorder:sync-menu --dry-run` previews the operation. Run
`php artisan goorder:sync-menu` to apply it. The menu is read directly from
GoOrder `config/menus`, following menu/category entities, not the entire POS
catalog or modifier-only products. New categories and dishes are created.
Existing names, prices, descriptions, images, category assignments, sorting,
SEO, and manual Active switches are preserved. Only GoOrder identity,
publication, modifier metadata, and schedules are refreshed.

Items absent from the storefront are hidden using `goorder_published`, without
deleting them or changing manual Active switches. Returning items can become
visible again unless manually disabled. Category settings contain an import
switch; disabling it prevents new imports and ordering in that category.
Admin item/category forms also have optional local weekly hours. These narrow
the GoOrder schedule; they never extend hours forbidden by GoOrder. All times
use Europe/Warsaw, including DST; the closing minute is excluded. Overnight
local intervals are supported. ASAP uses the current time, preorders use their
requested fulfillment time. Backend validation also protects stale carts and
orders waiting for price confirmation. Included single-choice lunch sauces
are required at checkout and validated against the catalog.

Missing/invalid catalogs, unknown schedule/modifier formats, and removal of
more than 25% of local dishes abort the transaction. Review genuine large
changes before using `--allow-large-removal`. Network failures do not empty
the menu. The command is protected by a 30-minute lock; dry runs roll back
all menu writes and do not download images.

The Laravel scheduler runs the sync daily at 03:15 Europe/Warsaw, independently
of `GOORDER_ENABLED` (which controls order submission). Configure
`GOORDER_MENU_SYNC_ENABLED`, `GOORDER_MENU_SYNC_TIME`, and
`GOORDER_MENU_REFERENCE` in the environment. GoPOS is consulted only to map
routing IDs for the existing direct-POS checkout; it never selects the dishes
or supplies their visible content/prices.

Deployment: deploy the code, back up the database, run `php artisan migrate
--force`, review the dry run, then apply one sync. Ensure that the web-server
user has **one** scheduler entry (do not duplicate an existing scheduler):

```cron
* * * * * cd /var/www/umamisushifo_usr/data/www/umamisushifood.pl/umami && php artisan schedule:run >> storage/logs/scheduler.log 2>&1
```

Check `php artisan schedule:list`, `storage/logs/goorder-menu-sync.log`, and
`storage/logs/laravel.log`. Configure server monitoring for command failures
and log rotation. A laptop runs this schedule only while its scheduler process
is running; production cron is a separate deployment step, not a Codex reminder.

Do not run the older `gopos:sync-menu` to maintain the storefront: it overwrites
existing content/prices and does not import missing dishes. Menu sync does not
enable GoOrder checkout automatically. Existing price differences remain
intentional under the preserve-content rule; GoOrder checkout still requires
customer approval of a changed quote before submitting the order.

This adapter uses the Umami GoOrder storefront API observed on 2026-09-25,
not the public GoPOS order API. GoOrder remains the intermediary and its
subscription/connection is still required. This is not a documented partner
API contract. Confirm support/permission and retention requirements with GoPOS
before a production rollout; storefront changes can break the adapter.

## What is implemented

- Server-side catalog identity checks, an OPEN GoOrder cart, then `/pay` using
  the restaurant's existing **offline** cash/card methods (no online charge).
- The local form redirects to a private, unguessable status URL. The GoOrder
  token and contact snapshot are encrypted with APP_KEY, never sent to the browser.
- WAITING_FOR_ACCEPTED is distinct from ACCEPTED. CONFIRMED alone is not staff
  acceptance. Only an accepted order exposes `estimated_preparation_at`.
- Status polling every 10 seconds; a minute scheduler refreshes orders when the
  customer closes the page. Store the absolute UTC deadline, never a duration.
  A missing later ETA does not reset the saved deadline. A new ETA replaces it.
- Ready, delivering, received, rejected, canceled, overdue and stale states.
  Reaching zero is not treated as proof that the order is ready.
- A browser-local active-order link/countdown survives navigation and reload.
  Status links can be reopened independently of the checkout session. Confirming
  a revised quote requires the original checkout session.
- Session checkout keys, unique DB constraints, cache locks and a durable
  pre-submission marker prevent automatic duplicate `/pay` calls.
- A timeout after `/pay` triggers read-only reconciliation, not a retry or a
  direct-GoPOS fallback. A crash before the remote ID is saved leaves an OPEN
  cart, which has not been submitted to the kitchen.

## Enablement

1. Back up the database and set a permanent APP_KEY. Never rotate it without
   retaining old keys: it encrypts remote order credentials.
2. Run `php artisan migrate`.
3. Run `php artisan goorder:map-menu` and review every active item and price.
   `php artisan goorder:map-menu --apply` saves only unique, exact Polish-name
   matches and their immutable GoOrder references. It does not change prices.
   Resolve unmatched dishes manually; runtime never fuzzy-matches dishes.
4. Review `config/goorder.php`: storefront, offline payment IDs and reference IDs.
   New consent rules, payment gateways, and products with modifier groups fail
   closed until corresponding UI support has been implemented.
5. Use a shared database or Redis cache/session lock store on multi-worker hosts.
   Set GOORDER_ENABLED=true and rebuild config cache. There is no silent fallback
   to direct GoPOS when GoOrder fails. Disabling new GoOrder checkout does not
   stop reconciliation of previously submitted orders.
6. Run Laravel's scheduler (`php artisan schedule:run` every minute).
7. Coordinate one low-value order with staff watching the actual GoPOS terminal.
   Verify popup **and persistent sound**, then accept and choose a time. Check
   that the website updates, reloads without resetting, and reflects a revised
   ETA. Repeat for delivery, scheduled pickup, rejection and completion.

The feature is OFF by default until this terminal acceptance test is passed.
No production migrations or live order submissions are performed by setup.

## Amounts and limitations

The GoOrder and GoPOS catalogs have different item IDs and can have different
prices. The adapter never equates those IDs. If GoOrder returns a different
quote (items, fees or delivery), the customer must explicitly confirm it before
submission. Quote contents are checked again immediately before `/pay`.
The storefront has no verified atomic price-lock or idempotency contract;
coordinate pricing changes with the restaurant and monitor this pilot.

Prices for mapped local order lines are updated to the approved remote quote.
The full approved quote, including extra fees, is kept in `goorder_quote`.
Do not assume legacy local item sums include every possible remote surcharge.

For ambiguous submissions, look up `goorder_id` and the internal UMAMI reference
in the restaurant dashboard. Never clear the submission marker or re-send
without verifying that no order was already accepted. Diagnostics in the admin
contain error codes, not customer data or upstream response bodies.

Treat tracking URLs as secrets. Disable URL capture for these routes in external
analytics, redact them in access logs, use HTTPS, and exclude them from proxies,
service-worker caches and crawlers. Application responses are no-store/noindex;
analytics scripts are disabled on the tracking page. Browser storage remembers
the most recent link for seven days after the ETA or latest visit; it does not
provide cross-device discovery. The exact saved link works on another device.

## Verification performed

`php artisan test --filter=GoOrderTest` covers the adapter with fake HTTP, including
duplicate submit, changed prices, identity changes, encrypted credentials,
acceptance/ETA persistence, tracking privacy and network-failure reconciliation.

Latest local run: all 19 GoOrder tests pass. The full suite passes 26/27; the
unchanged legal page currently fails because `resources/views/legal.blade.php`
references an undefined `$homeUrl`. Desktop/mobile browser checks verified
reload persistence, waiting-to-accepted updates, offline handling and the active
order banner. No production database was modified.

The private tracking page deliberately disables analytics scripts. GoOrder
purchase events are not currently emitted to Google Analytics; add a
privacy-reviewed server-side event before relying on ecommerce reports.

One anonymous OPEN cart containing Bifu Ramen was created against the actual
storefront to verify the draft response format (58 PLN). It was **not** submitted
with `/pay`, charged, or sent to the POS. Its test item was subsequently removed,
leaving an empty OPEN cart. Native terminal sound and staff acceptance
are therefore still unverified. That test must be performed with restaurant staff.
