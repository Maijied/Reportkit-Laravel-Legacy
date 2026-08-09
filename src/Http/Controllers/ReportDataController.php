<?php

namespace ReportKit\Laravel\Legacy\Http\Controllers;

use ReportKit\Core\Contracts\RowSource;
use ReportKit\Core\Filter\FilterValidator;
use ReportKit\Core\Report\ReportRegistry;
use ReportKit\Core\Table\DataTableResponder;
use ReportKit\Core\Table\PseudoPaginator;

/**
 * Opt-in generic DataTables endpoint for a registered report slug (Laravel 4.1–5.4).
 */
class ReportDataController
{
    /**
     * @param string $slug
     * @return mixed
     */
    public function data($slug)
    {
        $definition = ReportRegistry::get($slug);

        if (!$definition || empty($definition->serviceClass)) {
            return \Response::json(array('error' => 'Unknown report.'), 404);
        }

        $serviceClass = $definition->serviceClass;

        if (!class_exists($serviceClass)) {
            return \Response::json(array('error' => 'Report service missing.'), 500);
        }

        $inputs = \Input::all();

        if (!empty($inputs['start_date']) || !empty($inputs['end_date'])) {
            $maxMonths = $this->configValue('reportkit.date.max_months', 6);
            $dateError = (new FilterValidator())->validateDateAndOptionalWeek($inputs, (int) $maxMonths);

            if ($dateError) {
                return \Response::json(array('error' => $dateError), 422);
            }
        }

        $service = $this->resolveService($serviceClass);

        if (!$service instanceof RowSource && !method_exists($service, 'getRows')) {
            return \Response::json(array('error' => 'Report service invalid.'), 500);
        }

        $rows = $service->getRows($inputs);
        if (!is_array($rows)) {
            $rows = array();
        }

        $paginator = new PseudoPaginator();
        $search = '';

        if (isset($inputs['search']['value'])) {
            $search = $inputs['search']['value'];
        } elseif (isset($inputs['search']) && is_string($inputs['search'])) {
            $search = $inputs['search'];
        }

        $columns = array();

        if (!empty($definition->tables[0]) && is_object($definition->tables[0]) && !empty($definition->tables[0]->columns)) {
            foreach ($definition->tables[0]->columns as $col) {
                if (is_object($col) && isset($col->key)) {
                    $columns[] = $col->key;
                } elseif (is_array($col) && isset($col['key'])) {
                    $columns[] = $col['key'];
                }
            }
        }

        if ($search !== '' && !empty($columns)) {
            $rows = $paginator->searchBy($rows, $search, $columns);
        }

        if (!empty($inputs['order'][0]['column']) || (isset($inputs['order'][0]) && array_key_exists('column', $inputs['order'][0]))) {
            $colIndex = (int) $inputs['order'][0]['column'];
            $dir = isset($inputs['order'][0]['dir']) ? $inputs['order'][0]['dir'] : 'asc';

            if (isset($columns[$colIndex])) {
                $rows = $paginator->sortBy($rows, $columns[$colIndex], $dir);
            }
        }

        $filtered = count($rows);
        $start = isset($inputs['start']) ? (int) $inputs['start'] : 0;
        $length = isset($inputs['length'])
            ? (int) $inputs['length']
            : (int) $this->configValue('reportkit.table.default_page_length', 25);
        $page = $paginator->slice($rows, $start, $length);
        $summary = method_exists($service, 'getSummary') ? $service->getSummary($rows) : array();

        $responder = new DataTableResponder();

        return \Response::json(
            $responder->respond($inputs, $page, $filtered, $filtered, $summary)
        );
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
