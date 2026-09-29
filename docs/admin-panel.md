# UMAMI Admin

The Filament panel is available at `/admin`. Its interface supports Polish and
Ukrainian. The language control saves the choice to the signed-in user's profile;
the login page uses the session preference. Storefront languages are unchanged.

## Access

- The roles migration grants the protected Administrator role only to the existing
  account matching `config('app.admin_email')`. It never changes a password.
- For an existing account created later: `php artisan admin:grant user@example.com`.
- Administrators manage Roles and Users. Other roles cannot manage access rights.
- Assign a role in Users. An account without a role or any view permission cannot
  enter the panel. To revoke access, clear its role from another admin account.
- Role permissions cover view, create, update and delete for content resources.
  Orders, Customers and Site Settings expose only view and update. Write access includes view.
- A user cannot demote or delete themselves. The last administrator, the system
  role and roles still assigned to users are protected.
- Resource policies also protect direct URLs, Livewire mutations and bulk actions.
  The restaurant settings page and customer-order relation enforce the same rules.

## Build And Deploy

Back up the database before applying the migration. Deploy the code together with
the Vite output; `/public/build` is intentionally not tracked.

```sh
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
```

For a fresh installation, migrate before running DatabaseSeeder. Do not run the
content seeder on an existing production database to grant access; use admin:grant.
This change does not enable GoOrder checkout or modify synchronization scheduling.

The dashboard summarizes the local website order database, not all GoPOS receipts.
Its totals are order values, not a cash balance, paid turnover or profit.

Site Settings exposes an allowlisted catalog of content values. Its keys, types,
groups and labels are not editable, and records cannot be created or deleted from
the panel. Hours, delivery zones and coordinates are edited in the separate
restaurant settings page. GoPOS zone mappings and the legacy flat delivery fee
remain untouched by that page. Upload labels show the effective PHP/Livewire size
limit; the default local PHP configuration limits uploads to 2 MB.

```sh
php artisan test --filter='AdminPanelTest|GoOrderMenuSyncTest|GoOrderTest'
```
