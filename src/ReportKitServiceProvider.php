<?php

namespace ReportKit\Laravel\Legacy;

use Illuminate\Support\ServiceProvider;
use ReportKit\Core\Settings\ArraySettingsStore;
use ReportKit\Core\Settings\SettingsStore;

/**
 * Laravel 4.1–5.4 service provider for ReportKit.
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
        $this->loadPackageConfig();

        $this->app->singleton('reportkit.settings', function ($app) {
            $config = $this->readConfig();

            return new ArraySettingsStore(array(
                'brand.name' => isset($config['brand']['name']) ? $config['brand']['name'] : 'ReportKit',
                'brand.pdf_disclaimer' => isset($config['brand']['pdf_disclaimer'])
                    ? $config['brand']['pdf_disclaimer']
                    : 'This document was generated for authorized use only.',
                'brand.accent' => isset($config['brand']['accent']) ? $config['brand']['accent'] : '#0b7a4b',
                'routes.enabled' => !empty($config['routes']['enabled']),
            ));
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
        $configPath = dirname(__DIR__) . '/config/reportkit.php';

        if (is_dir($viewPath) && method_exists($this->app['view'], 'addNamespace')) {
            $this->app['view']->addNamespace('reportkit', $viewPath);
        }

        if (method_exists($this, 'publishes') && is_file($configPath)) {
            $this->publishes(array(
                $configPath => $this->app['path'] . '/../config/reportkit.php',
            ), 'reportkit-config');
        }

        $this->loadReportDefinitions();
        $this->registerArtisanCommands();
    }

    /**
     * Load package config/reportkit.php into the Config repository when possible.
     *
     * @return void
     */
    protected function loadPackageConfig()
    {
        $configPath = dirname(__DIR__) . '/config/reportkit.php';

        if (!is_file($configPath)) {
            return;
        }

        if (method_exists($this, 'mergeConfigFrom')) {
            $this->mergeConfigFrom($configPath, 'reportkit');
            return;
        }

        if (isset($this->app['config']) && method_exists($this->app['config'], 'set')) {
            $existing = $this->app['config']->get('reportkit', array());
            $package = require $configPath;
            $this->app['config']->set('reportkit', array_replace_recursive($package, is_array($existing) ? $existing : array()));
        }
    }

    /**
     * @return array
     */
    protected function readConfig()
    {
        if (function_exists('config')) {
            $value = config('reportkit', array());
            return is_array($value) ? $value : array();
        }

        if (isset($this->app['config'])) {
            $value = $this->app['config']->get('reportkit', array());
            return is_array($value) ? $value : array();
        }

        $configPath = dirname(__DIR__) . '/config/reportkit.php';

        if (is_file($configPath)) {
            $value = require $configPath;
            return is_array($value) ? $value : array();
        }

        return array();
    }

    /**
     * Load report definition PHP files under app/Reports (recursive).
     *
     * @return void
     */
    protected function loadReportDefinitions()
    {
        $config = $this->readConfig();
        $relative = isset($config['definitions_path']) ? $config['definitions_path'] : 'app/Reports';
        $path = $this->app['path'] . '/Reports';

        if (strpos($relative, 'app/') === 0) {
            $path = $this->app['path'] . '/' . substr($relative, 4);
        } elseif ($relative !== 'app/Reports') {
            if (function_exists('base_path')) {
                $path = base_path($relative);
            } elseif (isset($this->app['path.base'])) {
                $path = $this->app['path.base'] . '/' . ltrim($relative, '/');
            }
        }

        if (!is_dir($path)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && substr($file->getFilename(), -4) === '.php') {
                require $file->getPathname();
            }
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

        $this->commands(array(
            'ReportKit\Laravel\Legacy\Console\MakeReportCommand',
            'ReportKit\Laravel\Legacy\Console\InstallCommand',
        ));
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

        if (!$app) {
            return;
        }

        $enabled = false;

        if (function_exists('config')) {
            $enabled = (bool) config('reportkit.routes.enabled', false);
        }

        if (!$enabled && isset($app['config'])) {
            $enabled = (bool) $app['config']->get('reportkit.routes.enabled', false);
        }

        if (!$enabled && isset($app['reportkit.settings'])) {
            $enabled = (bool) $app['reportkit.settings']->get('routes.enabled', false);
        }

        if (!$enabled) {
            return;
        }

        $routesFile = __DIR__ . '/Http/routes.php';

        if (is_file($routesFile)) {
            require $routesFile;
        }
    }
}
