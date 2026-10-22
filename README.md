# Umami Sushi & Food

Restaurant website and administration panel built with Laravel and Filament.
The public website supports Polish, Ukrainian and English; administration
supports Polish and Ukrainian.

## Local Development

Requires PHP 8.3+, Composer, Node.js and a database supported by Laravel.
Configure a local `.env` before running migrations. Do not use production
credentials or enable order submission to external services in a test environment.

```sh
composer install
npm ci
php artisan migrate
npm run build
php artisan serve
```

## Conventions

- HTTP input validation and request authorization belong in `app/Http/Requests`.
- Reusable input rules belong in `app/Rules`; external integrations and business
  rules belong in `app/Services`.
- Interface translations use grouped PHP files in `lang/{locale}` and named keys
  such as `checkout.name`, `menu.categoryTitle` and `labels.price`.
- Editable restaurant content remains in database translations. Ingredients and
  other menu data must not be replaced with interface translation strings.
- `lang/vendor` overrides, when present, belong to their respective packages.
- Never commit credentials, database exports or internal operational reports.

## Checks

Tests use an isolated in-memory SQLite database. Integration tests must fake
outgoing HTTP requests rather than submit real restaurant orders.

```sh
php -d memory_limit=512M vendor/bin/phpunit
vendor/bin/pint --dirty --test
npm run build
```
