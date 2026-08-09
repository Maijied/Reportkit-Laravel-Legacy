<?php

namespace ReportKit\Laravel\Legacy\Http\Controllers;

use ReportKit\Core\Contracts\RowSource;
use ReportKit\Core\Filter\FilterValidator;
use ReportKit\Core\Report\ReportRegistry;

/**
 * Opt-in weeks / rows / trace endpoints for async_prepare (Laravel 4.1–5.4).
 */
class ReportWeeksController
{
    /**
     * @param string $slug
     * @return mixed
     */
    public function weeks($slug)
    {
        $service = $this->resolve($slug);

        if (!is_object($service) || $this->isJsonResponse($service)) {
            return $service;
        }

        $inputs = \Input::all();
        $maxMonths = (int) $this->configValue('reportkit.date.max_months', 6);
        $error = (new FilterValidator())->validateDateAndOptionalWeek($inputs, $maxMonths);

        if ($error) {
            return \Response::json(array('error' => $error), 422);
        }

        $weeks = method_exists($service, 'getWeeks') ? $service->getWeeks($inputs) : array();

        return \Response::json(array('weeks' => is_array($weeks) ? $weeks : array()));
    }

    /**
     * @param string $slug
     * @return mixed
     */
    public function rows($slug)
    {
        $service = $this->resolve($slug);

        if (!is_object($service) || $this->isJsonResponse($service)) {
            return $service;
        }

        $inputs = \Input::all();
        $maxMonths = (int) $this->configValue('reportkit.date.max_months', 6);
        $error = (new FilterValidator())->validateDateAndOptionalWeek($inputs, $maxMonths);

        if ($error) {
            return \Response::json(array('error' => $error), 422);
        }

        $rows = method_exists($service, 'getRows') ? $service->getRows($inputs) : array();

        return \Response::json(array(
            'rows' => is_array($rows) ? array_values($rows) : array(),
            'count' => is_array($rows) ? count($rows) : 0,
        ));
    }

    /**
     * @param string $slug
     * @return mixed
     */
    public function trace($slug)
    {
        if (!$this->configValue('reportkit.routes.trace', false)) {
            return \Response::json(array('error' => 'Trace disabled.'), 404);
        }

        $service = $this->resolve($slug);

        if (!is_object($service) || $this->isJsonResponse($service)) {
            return $service;
        }

        $inputs = \Input::all();
        $rows = method_exists($service, 'getRows') ? $service->getRows($inputs) : array();
        $trace = method_exists($service, 'getTrace') ? $service->getTrace() : array();

        return \Response::json(array(
            'count' => is_array($rows) ? count($rows) : 0,
            'trace' => $trace,
        ));
    }

    /**
     * @param string $slug
     * @return object|mixed
     */
    protected function resolve($slug)
    {
        $definition = ReportRegistry::get($slug);

        if (!$definition || empty($definition->serviceClass)) {
            return \Response::json(array('error' => 'Unknown report.'), 404);
        }

        $serviceClass = $definition->serviceClass;

        if (!class_exists($serviceClass)) {
            return \Response::json(array('error' => 'Report service missing.'), 500);
        }

        $service = $this->resolveService($serviceClass);

        if (!$service instanceof RowSource && !method_exists($service, 'getRows')) {
            return \Response::json(array('error' => 'Report service invalid.'), 500);
        }

        return $service;
    }

    /**
     * @param string $serviceClass
     * @return object
     */
    protected function resolveService($serviceClass)
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
     * @param mixed $value
     * @return bool
     */
    protected function isJsonResponse($value)
    {
        return is_object($value) && (
            $value instanceof \Illuminate\Http\JsonResponse
            || (method_exists($value, 'getStatusCode') && method_exists($value, 'getData'))
        );
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
