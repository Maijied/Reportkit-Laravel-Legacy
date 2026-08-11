# ReportKit for Laravel (Legacy) — upgrade guide

**Package:** `reportkit/laravel-legacy` (Laravel 4.1 → 5.4)  
**Requires:** matching `reportkit/core` version

---

## 0.1.x → 0.2.x (beta)

```bash
composer require reportkit/core:0.2.*@beta reportkit/laravel-legacy:0.2.*@beta
php artisan reportkit:install --with-config --publish-assets
```

### Assets

`--publish-assets` copies:

- `@lorapok-labs/reportkit-ui` CSS/JS → `public/css/reportkit/`, `public/js/reportkit/`
- Kit-Larva GIFs → `public/img/reportkit/`

### Config

Merge new `brand.mascot_enabled` and `brand.loader_animation` keys — see [config migrations](https://reportkit.lorapok.tech/docs/0.1/maintenance/config-migrations).

Disable mascot in strict themes:

```php
'brand' => [
    'mascot_enabled' => false,
],
```

### Bus host (Shohoz)

When integrating bus PR #16886 parity, read [plan/references/bus-pr-16886.md](../../plan/references/bus-pr-16886.md) and the [upgrade overview](https://reportkit.lorapok.tech/docs/0.1/maintenance/upgrade-overview).

---

## Patch releases

`composer update reportkit/laravel-legacy reportkit/core` together.

---

## Links

- [Installation](./INSTALL.md)
- [Upgrade overview](https://reportkit.lorapok.tech/docs/0.1/maintenance/upgrade-overview)
