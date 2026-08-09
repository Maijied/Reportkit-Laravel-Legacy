# reportkit/laravel-legacy

Laravel **4.1 – 5.4** adapter for [ReportKit Core](https://github.com/Maijied/Reportkit-Core).

Provides service provider, facade, CAS Blade partials (`reportkit::`), and Artisan:

- `php artisan reportkit:install`
- `php artisan reportkit:make {Name} --route=… --preset=hybrid --flags=… --dry-run`

> PHP 5.6 – 7.4 · Pair with `@reportkit/ui` for CSS/JS

---

## Author

**Mohammad Maizied Hasan Majumder** \<mdshuvo40@gmail.com\>  
Founder & Principal Engineer at Lorapok Labs · Senior Software Engineer @ Shohoz Ltd

---

## Install

```bash
composer require reportkit/laravel-legacy
```

VCS (until Packagist):

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
    // ...
    'ReportKit\\Laravel\\Legacy\\ReportKitServiceProvider',
),
'aliases' => array(
    // ...
    'ReportKit' => 'ReportKit\\Laravel\\Legacy\\Facades\\ReportKit',
),
```

Copy UI assets from [Reportkit-UI](https://github.com/Maijied/Reportkit-UI) into `public/css/reportkit/` and `public/js/reportkit/`.

See [docs/INSTALL.md](docs/INSTALL.md).

## Scaffold a NEW report

```bash
php artisan reportkit:make Demo --route=admin/demo-report --preset=hybrid --dry-run
php artisan reportkit:make Demo --route=admin/demo-report --preset=hybrid \
  --flags=datatables,sync,async_prepare,kpi,excel,csv,pdf
```

Does **not** modify existing reports. Add routes manually in your domain route file (or call `ReportKit::routes()` when `routes.enabled` is true — see manager docs).

## Ecosystem

| Package | Repo |
|---------|------|
| `reportkit/core` | [Reportkit-Core](https://github.com/Maijied/Reportkit-Core) |
| `reportkit/laravel-legacy` | This repository |
| `reportkit/laravel` | [Reportkit-Laravel](https://github.com/Maijied/Reportkit-Laravel) (5.5 → current) |
| `@reportkit/ui` | [Reportkit-UI](https://github.com/Maijied/Reportkit-UI) |

## License

MIT © Mohammad Maizied Hasan Majumder / Lorapok Labs
