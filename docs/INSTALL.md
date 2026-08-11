# Install ReportKit on Laravel 4.1–5.4

> Site docs: https://reportkit.lorapok.tech/docs/0.1/adapters/laravel-legacy


## 1. Composer

```bash
composer require reportkit/core reportkit/laravel-legacy
```

Or VCS monorepo (single clone):

- https://github.com/Maijied/Reportkit-Core.git

## 2. Provider + alias

In `app/config/app.php`:

```php
'ReportKit\\Laravel\\Legacy\\ReportKitServiceProvider',

'ReportKit' => 'ReportKit\\Laravel\\Legacy\\Facades\\ReportKit',
```

## 3. UI assets + config

```bash
php artisan reportkit:install --with-config --publish-assets
```

This copies `@lorapok-labs/reportkit-ui` CSS/JS into `public/css|js/reportkit/` when the UI package is available locally (or follow the GitHub copy steps below).

Manual fallback from [Reportkit-Core/reportkit-ui](https://github.com/Maijied/Reportkit-Core/tree/main/reportkit-ui):

```bash
mkdir -p public/css/reportkit public/js/reportkit
# copy css/reportkit.css
# copy css/reportkit-compat.css
# copy js/reportkit.js
```

## 4. Checklist

```bash
php artisan reportkit:install
# or with publish:
php artisan reportkit:install --with-config --publish-assets
```

## 5. Scaffold a NEW report only

```bash
php artisan reportkit:make Demo --route=admin/demo-report --dry-run
php artisan reportkit:make Demo --route=admin/demo-report --preset=hybrid --layout=layouts.master
# bus host example:
# php artisan reportkit:make Demo --route=admin/demo-report --preset=hybrid --layout=public.admin_master
composer dump-autoload
```

`--layout` defaults to `layouts.master`. Controllers are **global-namespace** under `app/controllers/admin/Reports/` — suggested routes use `DemoReportController@index`, not a `Reports\` prefix.

Add routes for that controller in your domain route file (unify on `{{route}}/data` for DataTables).

**Do not change existing report controllers/views in this phase.**

## 6. Definitions folder

Create `app/Reports/` — the provider auto-requires `*.php` on boot.

## 7. Optional routes helper

Set `routes.enabled` to `true` on the settings store, then call `ReportKit::routes()` from a host bridge to load `src/Http/routes.php` (`reportkit/{slug}/data`). Default remains **disabled**; prefer explicit `Route::get` lines printed by `reportkit:make`.
