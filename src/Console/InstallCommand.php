<?php

namespace ReportKit\Laravel\Legacy\Console;

use Illuminate\Console\Command;

/**
 * One-time install helper for Laravel 4.1–5.4 hosts.
 */
class InstallCommand extends Command
{
    protected $name = 'reportkit:install';

    protected $description = 'Print ReportKit install checklist for Laravel 4.1–5.4';

    public function fire()
    {
        $this->info('ReportKit install checklist (Laravel 4.1–5.4)');
        $this->line('');
        $this->line('1. composer require reportkit/core reportkit/laravel-legacy');
        $this->line('2. Add provider: ReportKit\\Laravel\\Legacy\\ReportKitServiceProvider');
        $this->line('3. Add alias: ReportKit => ReportKit\\Laravel\\Legacy\\Facades\\ReportKit');
        $this->line('4. Create app/Reports/ for Report::define files');
        $this->line('5. Copy @reportkit/ui CSS/JS into public/css|js/reportkit/');
        $this->line('6. Scaffold NEW reports only: php artisan reportkit:make Demo --dry-run');
        $this->line('7. Do NOT migrate existing reports until you are ready');
        $this->line('');
        $this->info('Docs: https://github.com/Maijied/Reportkit-Laravel-Legacy');
    }
}
