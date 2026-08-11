<?php

/**
 * Lorapok ReportKit
 * Copyright (c) 2026 Lorapok Labs (https://lorapok.tech)
 * Licensed under the Lorapok Non-Commercial License 1.0 (Lorapok-NCL-1.0)
 *
 * SettingsController — Public browser settings JSON (ceilings, brand, logging flags only).
 */

namespace ReportKit\Laravel\Legacy\Http\Controllers;

use ReportKit\Core\Report\ReportRegistry;
use ReportKit\Core\Settings\BrowserSettingsBuilder;
use ReportKit\Core\Settings\ReportkitConfig;
use ReportKit\Core\Settings\ReportSettingsResolver;

/**
 * Public browser settings JSON (ceilings, brand, logging flags only).
 */
class SettingsController
{
    /**
     * GET /reportkit/settings.json
     *
     * @return mixed
     */
    public function json()
    {
        return $this->respond(null);
    }

    /**
     * GET /reportkit/{slug}/settings.json
     *
     * @param string $slug
     * @return mixed
     */
    public function forReport($slug)
    {
        return $this->respond($slug);
    }

    /**
     * @param string|null $slug
     * @return mixed
     */
    protected function respond($slug)
    {
        $app = function_exists('app') ? app() : null;
        $configPath = dirname(dirname(dirname(__DIR__))) . '/config/reportkit.php';
        $config = ReportkitConfig::load($app, $configPath);
        $definition = $slug ? ReportRegistry::get($slug) : null;

        if ($slug && !$definition) {
            return \Response::json(array('error' => 'Unknown report.'), 404);
        }

        return \Response::json(BrowserSettingsBuilder::forReport($config, $definition));
    }
}
