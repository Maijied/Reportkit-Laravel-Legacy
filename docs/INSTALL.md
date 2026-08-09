# Install ReportKit on Laravel 4.1–5.4

## 1. Composer

```bash
composer require reportkit/core reportkit/laravel-legacy
```

Or VCS repositories pointing at:

- https://github.com/Maijied/Reportkit-Core.git
- https://github.com/Maijied/Reportkit-Laravel-Legacy.git

## 2. Provider + alias

In `app/config/app.php`:

```php
'ReportKit\\Laravel\\Legacy\\ReportKitServiceProvider',

'ReportKit' => 'ReportKit\\Laravel\\Legacy\\Facades\\ReportKit',
```

## 3. UI assets

From https://github.com/Maijied/Reportkit-UI :

```bash
mkdir -p public/css/reportkit public/js/reportkit
# copy css/reportkit.css (+ optional reportkit-compat.css)
# copy js/reportkit.js
```

## 4. Checklist

```bash
php artisan reportkit:install
```

## 5. Scaffold a NEW report only

```bash
php artisan reportkit:make Demo --route=admin/demo-report --dry-run
php artisan reportkit:make Demo --route=admin/demo-report --preset=hybrid
```

Add routes for that controller in your domain route file.  
**Do not change existing report controllers/views in this phase.**

## 6. Definitions folder

Create `app/Reports/` — the provider auto-requires `*.php` on boot.

## 7. Optional routes helper

`ReportKit::routes()` returns registered report ids. Full auto route wiring is host-group aware; prefer explicit `Route::get` lines printed by `reportkit:make` until you enable a host bridge.
