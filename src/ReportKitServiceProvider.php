<?php

namespace ReportKit\Laravel\Legacy;

use Illuminate\Support\ServiceProvider;
use ReportKit\Core\Settings\ArraySettingsStore;
use ReportKit\Core\Settings\SettingsStore;

/**
 * Laravel 4.1 service provider for ReportKit.
 *
 * Register in app/config/app.php:
 *   'ReportKit\Laravel\Legacy\ReportKitServiceProvider',
 */
class ReportKitServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->app->singleton('reportkit.settings', function () {
            return new ArraySettingsStore([
                'brand.name' => 'ReportKit',
                'brand.pdf_disclaimer' => 'This document was generated for authorized use only.',
                'brand.accent' => '#0b7a4b',
                'routes.enabled' => false,
            ]);
        });

        $this->app->bind(SettingsStore::class, function ($app) {
            return $app['reportkit.settings'];
        });

        $this->app->singleton('reportkit', function () {
            return new ReportKitManager();
        });
    }

    /**
     * @return void
     */
    public function boot()
    {
        $viewPath = dirname(__DIR__) . '/resources/views';

        if (is_dir($viewPath) && method_exists($this->app['view'], 'addNamespace')) {
            $this->app['view']->addNamespace('reportkit', $viewPath);
        }

        $this->loadReportDefinitions();
        $this->registerArtisanCommands();
    }

    /**
     * Load app/Reports/*.php definitions when the directory exists.
     *
     * @return void
     */
    protected function loadReportDefinitions()
    {
        $path = $this->app['path'] . '/Reports';

        if (!is_dir($path)) {
            return;
        }

        foreach (glob($path . '/*.php') as $file) {
            require $file;
        }
    }

    /**
     * @return void
     */
    protected function registerArtisanCommands()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            'ReportKit\Laravel\Legacy\Console\MakeReportCommand',
            'ReportKit\Laravel\Legacy\Console\InstallCommand',
        ]);
    }

    /**
     * Opt-in: call from app/routes.php after setting routes.enabled = true.
     * Prefer explicit Route::get lines from reportkit:make for L4.1 domain groups.
     *
     * @return void
     */
    public static function registerRoutes()
    {
        $app = function_exists('app') ? app() : null;

        if (!$app || !isset($app['reportkit.settings'])) {
            return;
        }

        if (!$app['reportkit.settings']->get('routes.enabled', false)) {
            return;
        }

        $routesFile = __DIR__ . '/Http/routes.php';

        if (is_file($routesFile)) {
            require $routesFile;
        }
    }
}
