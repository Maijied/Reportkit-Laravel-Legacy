<?php

namespace ReportKit\Laravel\Legacy\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * One-time install helper for Laravel 4.1–5.4 hosts.
 */
class InstallCommand extends Command
{
    protected $name = 'reportkit:install';

    protected $description = 'Install ReportKit checklist / publish assets & config (Laravel 4.1–5.4)';

    public function fire()
    {
        $this->info('ReportKit install (Laravel 4.1–5.4)');
        $this->line('');

        if ($this->option('with-config')) {
            $this->publishConfig();
        }

        if ($this->option('publish-assets')) {
            $this->copyUiAssets();
        }

        $this->line('1. composer require reportkit/core reportkit/laravel-legacy');
        $this->line('2. Add provider: ReportKit\\Laravel\\Legacy\\ReportKitServiceProvider');
        $this->line('3. Add alias: ReportKit => ReportKit\\Laravel\\Legacy\\Facades\\ReportKit');
        $this->line('4. php artisan reportkit:install --with-config --publish-assets');
        $this->line('5. Create app/Reports/ for Report::define files');
        $this->line('6. Scaffold NEW reports only:');
        $this->line('   php artisan reportkit:make Demo --route=admin/demo-report --preset=hybrid --layout=layouts.master');
        $this->line('   (bus host: --layout=public.admin_master)');
        $this->line('7. composer dump-autoload  (required for L4.1 classmap under app/controllers/admin/Reports/)');
        $this->line('8. Add the suggested Route::get lines to your domain route file');
        $this->line('9. Do NOT migrate existing reports until you are ready');
        $this->line('');
        $this->info('Docs: https://reportkit.lorapok.tech/docs/0.1/adapters/laravel-legacy');
    }

    /**
     * Laravel 5.1+ prefers handle(); keep fire() for L4.
     *
     * @return int
     */
    public function handle()
    {
        $this->fire();

        return 0;
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('publish-assets', null, InputOption::VALUE_NONE, 'Copy @lorapok-labs/reportkit-ui CSS/JS into public/css|js/reportkit/'),
            array('with-config', null, InputOption::VALUE_NONE, 'Publish config/reportkit.php into the host app'),
            array('force', 'f', InputOption::VALUE_NONE, 'Overwrite existing published config'),
        );
    }

    /**
     * @return string
     */
    protected function packageRoot()
    {
        return dirname(dirname(__DIR__));
    }

    /**
     * @param string $relative
     * @return string
     */
    protected function hostBase($relative)
    {
        if (function_exists('base_path')) {
            return base_path($relative);
        }

        return $this->laravel['path.base'] . '/' . ltrim($relative, '/');
    }

    /**
     * @return void
     */
    protected function publishConfig()
    {
        $src = $this->packageRoot() . '/config/reportkit.php';

        if (!is_file($src)) {
            $this->warn('Package config/reportkit.php missing.');
            return;
        }

        $destCandidates = array(
            $this->hostBase('config/reportkit.php'),
            $this->hostBase('app/config/reportkit.php'),
        );

        $dest = $destCandidates[0];

        foreach ($destCandidates as $candidate) {
            $dir = dirname($candidate);
            if (is_dir($dir)) {
                $dest = $candidate;
                break;
            }
        }

        $dir = dirname($dest);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (is_file($dest) && !$this->option('force')) {
            $this->line('Config already exists at ' . $dest . ' (use --force to overwrite)');
            return;
        }

        copy($src, $dest);
        $this->line('Published config → ' . $dest);
    }

    /**
     * @return void
     */
    protected function copyUiAssets()
    {
        $public = function_exists('public_path')
            ? public_path()
            : $this->laravel['path.public'];

        $targets = array(
            'css/reportkit.css' => $public . '/css/reportkit/reportkit.css',
            'css/reportkit-compat.css' => $public . '/css/reportkit/reportkit-compat.css',
            'js/reportkit.js' => $public . '/js/reportkit/reportkit.js',
        );

        $packageParent = dirname($this->packageRoot());
        $candidates = array(
            $this->hostBase('../reportkit-ui'),
            $this->hostBase('../../reportkit/reportkit-ui'),
            $packageParent . '/reportkit-ui',
            dirname($packageParent) . '/reportkit-ui',
        );

        $root = null;

        foreach ($candidates as $candidate) {
            if (is_dir($candidate) && is_file($candidate . '/js/reportkit.js')) {
                $root = $candidate;
                break;
            }
        }

        if (!$root) {
            $this->warn('UI assets package not found locally — copy from https://github.com/Maijied/Reportkit-UI or npm @lorapok-labs/reportkit-ui');
            return;
        }

        foreach ($targets as $rel => $dest) {
            $src = $root . '/' . $rel;

            if (!is_file($src)) {
                continue;
            }

            $dir = dirname($dest);

            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            copy($src, $dest);
            $this->line('Published ' . $rel . ' → ' . $dest);
        }
    }
}
