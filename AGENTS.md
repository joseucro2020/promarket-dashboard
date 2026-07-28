# AGENTS.md — ProMarket Dashboard

## What this is

Laravel 8 admin dashboard for **ProMarket Latino**, a Venezuelan e-commerce operator. All admin UI lives under `/panel`. Default locale is `es`, timezone is `America/Caracas`.

## Commands

| Action | Command |
|---|---|
| Compile frontend assets | `npm run dev` / `npm run watch` / `npm run prod` |
| Run all tests | `.\vendor\bin\phpunit` |
| Run a single test file | `.\vendor\bin\phpunit tests\Feature\FetchBcvRatesTest.php` |
| Fetch exchange rates from BCV | `php artisan exchange_rates:fetch-bcv` |
| Fetch with SSL skip (dev) | `php artisan exchange_rates:fetch-bcv --insecure` |
| Generate app key | `php artisan key:generate` |
| Publish WasenderAPI config | `php artisan vendor:publish --tag=wasenderapi-config` |

## Key architecture

- All routes in `routes/web.php` prefixed `/panel`. Separate route files: `panel_api.php` (panel AJAX endpoints), `wasender.php` (WhatsApp webhook), `api.php` (REST API).
- 50+ Eloquent models in `app/Models/`. Main app logic in `app/Http/Controllers/` (plain controllers, no invokable pattern).
- Custom libraries registered as facades: `CalcPrice`, `Money`, `Total` (`app/Libraries/`). Also `Helper` facade and `WasenderApi` facade.
- Excel exports in `app/Exports/`. PDF via `barryvdh/laravel-dompdf`.
- Global helpers file loaded via `composer.json` autoload.files: `app/Helpers/helpers.php` (provides `abbreviate_number()` and `Helper` facade methods).
- The `WasenderServiceProvider` loads `routes/wasender.php` and registers the WasenderApi client binding.

## Artisan commands

- `exchange_rates:fetch-bcv` — scrapes `bcv.org.ve` for USD/VES rate, scheduled daily at 05:00 (`app/Console/Kernel.php:28`).
- `NormalizeClientWhatsappPhones` — one-off normalization of phone numbers.

## Frontend build

Laravel Mix (`webpack.mix.js`). SCSS source in `resources/sass/`, compiled to `public/css/`. JS source in `resources/js/`, compiled to `public/js/`. The custom mix setup also glob-copies `resources/vendors/` and `resources/fonts/` to `public/`. RTL mode is driven by `MIX_CONTENT_DIRECTION` env var.

## Testing

- PHPUnit 9, tests in `tests/Feature/` and `tests/Unit/`.
- Feature tests use `RefreshDatabase`. HTTP clients are mocked via `Http::fake()` or `Mockery` + container binding.
- `phpunit.xml` has DB config **commented out** — tests that need a DB require a real MySQL connection by default. DB-dependent tests are opt-in.

## Config & env quirks

- `DASHBOARD_SLOW_QUERY_LOG=true` + `DASHBOARD_SLOW_QUERY_THRESHOLD_MS=200` enable slow-query debugging on dashboard pages.
- `SFTP_*` vars drive an SFTP disk (`ecommerce_sftp`) that uploads banner images to a legacy ecommerce server.
- `WASENDERAPI_*` vars configure the Wasender WhatsApp API client.
- `BANNERS_IMAGE_PATH` / `BANNERS_IMAGE_URL` / `BANNERS_IMAGE_PUBLIC_PATH` for banner storage (`config/custom.php`).
- Banner upload flow: upload via `/panel/banners/upload`, then SFTP-sync to legacy app.

## Notable files

- `routes/web.php` — all panel routes (524 lines)
- `app/Console/Commands/FetchBcvRates.php` — BCV scraper command
- `app/Services/WasenderApi/` — custom WhatsApp SDK scaffold
- `config/custom.php` — theme layout config + banner paths
- `config/wasenderapi.php` — Wasender API config
- `docker/7.4/` and `docker/8.0/` — Dockerfiles for both PHP versions

## Style conventions

- Controllers use `public function` (no resource controllers). Single controller per domain.
- Route names use kebab-case (e.g., `exchange-rates.fetch-now`).
- Views are under `resources/views/panel/` for admin, `resources/views/admin/` for super-admin.
- All user-facing text is in Spanish.
- `users` table has a `type` column: `1` = customer, `2` = admin/staff. Only `type=2` users can log into the panel.
- Do NOT modify vendor files or compiled assets in `public/css/`, `public/js/`, `public/vendors/`, `public/fonts/` — they are gitignored and overwritten by `npm run`.
