<?php

/**
 * Lorapok ReportKit
 * Copyright (c) 2026 Lorapok Labs (https://lorapok.tech)
 * Licensed under the Lorapok Non-Commercial License 1.0 (Lorapok-NCL-1.0)
 *
 * ReportWeeksController — Opt-in weeks / rows / trace endpoints for async_prepare (Laravel 4.1–5.4).
 */

namespace ReportKit\Laravel\Legacy\Http\Controllers;

use ReportKit\Core\Http\AjaxResponse;
use ReportKit\Core\Http\HandlesReportWeeks;
use ReportKit\Core\Settings\ReportkitConfig;

/**
 * Opt-in weeks / rows / trace endpoints for async_prepare (Laravel 4.1–5.4).
 */
class ReportWeeksController
{
    use HandlesReportWeeks;

    /**
     * @param string $slug
     * @return mixed
     */
    public function weeks($slug)
    {
        return $this->respond($this->reportWeeksPayload($slug, \Input::all(), $this->config()));
    }

    /**
     * @param string $slug
     * @return mixed
     */
    public function rows($slug)
    {
        return $this->respond($this->reportRowsPayload($slug, \Input::all(), $this->config()));
    }

    /**
     * @param string $slug
     * @return mixed
     */
    public function trace($slug)
    {
        $enabled = (bool) $this->configValue('reportkit.routes.trace', false);

        return $this->respond($this->reportTracePayload($slug, \Input::all(), $this->config(), $enabled));
    }

    /**
     * @param array $payload
     * @return mixed
     */
    protected function respond(array $payload)
    {
        $status = AjaxResponse::status($payload);
        unset($payload['_status']);

        return \Response::json($payload, $status);
    }

    /**
     * @param string $serviceClass
     * @return object
     */
    protected function makeReportService($serviceClass)
    {
        if (function_exists('app')) {
            try {
                return app($serviceClass);
            } catch (\Exception $e) {
                // fall through
            }
        }

        return new $serviceClass();
    }

    /**
     * @return array
     */
    protected function config()
    {
        $configPath = dirname(dirname(dirname(__DIR__))) . '/config/reportkit.php';

        return ReportkitConfig::load(function_exists('app') ? app() : null, $configPath);
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function configValue($key, $default)
    {
        if (function_exists('config')) {
            return config($key, $default);
        }

        if (class_exists('Config')) {
            return \Config::get($key, $default);
        }

        return $default;
    }
}
