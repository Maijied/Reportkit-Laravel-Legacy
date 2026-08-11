> Plain-text overview for Packagist (no Mermaid). GitHub renders the full diagram version in [README.md](README.md).

<p align="center">
  <img src="https://raw.githubusercontent.com/Maijied/Reportkit-Core/main/brand/png/reportkit-logo-1200.png" alt="ReportKit for Laravel (Legacy)" width="160">
</p>

<h1 align="center">ReportKit for Laravel (Legacy)</h1>

<p align="center"><strong>Multi-database report engine · Laravel 4.1 → all supported</strong></p>

<p align="center">
  <a href="https://packagist.org/packages/reportkit/laravel-legacy"><img alt="Packagist Version" src="https://img.shields.io/packagist/v/reportkit/laravel-legacy?include_prereleases&label=packagist&color=0b7a4b"></a>
  <a href="https://packagist.org/packages/reportkit/laravel-legacy"><img alt="Downloads" src="https://img.shields.io/packagist/dt/reportkit/laravel-legacy?color=0b7a4b"></a>
  <img alt="PHP" src="https://img.shields.io/badge/php-5.6%20%E2%80%93%207.4-777bb4">
  <img alt="Laravel" src="https://img.shields.io/badge/laravel-4.1%20%E2%80%93%205.4-ff2d20">
  <a href="https://packagist.org/packages/reportkit/laravel-legacy"><img alt="License" src="https://img.shields.io/packagist/l/reportkit/laravel-legacy?color=0b7a4b"></a>
</p>

> Classic Laravel adapter for [ReportKit Core](https://github.com/Maijied/Reportkit-Core). Same engine and Artisan DX — tuned for pre-auto-discovery Laravel and PHP 5.6.
>
> **Website & docs:** https://reportkit.lorapok.tech · **Part of the Lorapok Labs ecosystem.**
>
> For Laravel **5.5 → 13**, use [`reportkit/laravel`](https://github.com/Maijied/Reportkit-Laravel).

> A diagram-rich version of this README (with Mermaid) is shown on the [GitHub repository page](https://github.com/Maijied/Reportkit-Laravel-Legacy).

## What you get

- Service provider + `ReportKit` facade (manual registration for L4.1–5.4).
- `php artisan reportkit:install` — checklist + optional `--with-config --publish-assets`.
- `php artisan reportkit:make` — full-stack stubs (definition, repository, service, controller, blade, JS, test).
- CAS Blade partials under `reportkit::` and opt-in `ReportKit::routes()`.
- Never modifies your existing reports.

## Requirements

- PHP **5.6 – 7.4**
- `reportkit/core` (beta channel allowed)
- Laravel **4.1 – 5.4** host
- `@lorapok-labs/reportkit-ui` assets copied into public

## Install

```bash
composer require reportkit/laravel-legacy
```

Install from Git (VCS):

```json
{
  "repositories": [
    { "type": "vcs", "url": "https://github.com/Maijied/Reportkit-Core.git" },
    { "type": "vcs", "url": "https://github.com/Maijied/Reportkit-Laravel-Legacy.git" }
  ],
  "require": {
    "reportkit/core": "dev-main",
    "reportkit/laravel-legacy": "dev-main"
  }
}
```

Register in `app/config/app.php` (Laravel 4.1 style):

```php
'providers' => array(
    'ReportKit\\Laravel\\Legacy\\ReportKitServiceProvider',
),
'aliases' => array(
    'ReportKit' => 'ReportKit\\Laravel\\Legacy\\Facades\\ReportKit',
),
```

Then:

```bash
php artisan reportkit:install --with-config --publish-assets
php artisan reportkit:make Demo --route=admin/demo-report --preset=hybrid --layout=layouts.master
composer dump-autoload
```

`composer dump-autoload` is required for the L4.1 classmap. Full checklist: [docs/INSTALL.md](docs/INSTALL.md).

## Merge multiple databases

```php
use ReportKit\Core\Source\MergedRowSource;
use ReportKit\Laravel\Legacy\Source\ConnectionRowSource;

$domain = function ($query, array $filters) {
    return $query->from('orders')
        ->whereBetween('created_at', array($filters['start_date'], $filters['end_date']));
};

$live    = new ConnectionRowSource('mysql', $domain, null, 'live');
$archive = new ConnectionRowSource('mysql_archive', $domain, null, 'archive');

$source = new MergedRowSource(array($live, $archive));
$source->dedupeBy('id')->orderBy('created_at', 'desc');

$rows = $source->getRows($filters); // merged + deduped + sorted
```

## Scaffold a NEW report

```bash
php artisan reportkit:make Demo --route=admin/demo-report --preset=hybrid --dry-run
php artisan reportkit:make Demo --route=admin/demo-report --preset=hybrid \
  --layout=layouts.master \
  --flags=datatables,sync,async_prepare,kpi,excel,csv,pdf
composer dump-autoload
```

Add routes manually in your domain route file (or call `ReportKit::routes()` when `routes.enabled` is true):

```php
Route::get('admin/demo-report', 'DemoReportController@index');
Route::get('admin/demo-report/data', 'DemoReportController@data');
```

## Ecosystem

| Package | Repo |
|---------|------|
| `reportkit/core` | [Reportkit-Core](https://github.com/Maijied/Reportkit-Core) |
| `reportkit/laravel-legacy` | This repository |
| `reportkit/laravel` | [Reportkit-Laravel](https://github.com/Maijied/Reportkit-Laravel) (5.5 → 13) |
| `@lorapok-labs/reportkit-ui` | [Reportkit-UI](https://github.com/Maijied/Reportkit-UI) |

## Author

**Mohammad Maizied Hasan Majumder** · [mdshuvo40@gmail.com](mailto:mdshuvo40@gmail.com)
Founder & Principal Engineer at Lorapok Labs · Senior Software Engineer @ Shohoz Ltd

## License

MIT © Mohammad Maizied Hasan Majumder / Lorapok Labs
