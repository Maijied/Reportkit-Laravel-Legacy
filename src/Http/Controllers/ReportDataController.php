<?php

namespace ReportKit\Laravel\Legacy\Http\Controllers;

use ReportKit\Core\Report\ReportRegistry;
use ReportKit\Core\Table\DataTableResponder;
use ReportKit\Core\Table\PseudoPaginator;

/**
 * Opt-in generic DataTables endpoint for a registered report slug.
 *
 * Loaded only when reportkit.settings routes.enabled is true
 * (see Http/routes.php). Host apps may prefer dedicated controllers
 * from reportkit:make instead.
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
        $service = new $serviceClass();
        $rows = method_exists($service, 'getRows') ? $service->getRows($inputs) : array();
        $summary = method_exists($service, 'getSummary') ? $service->getSummary($rows) : array();

        $paginator = new PseudoPaginator();
        $start = isset($inputs['start']) ? (int) $inputs['start'] : 0;
        $length = isset($inputs['length']) ? (int) $inputs['length'] : 25;
        $page = $paginator->slice($rows, $start, $length);

        $responder = new DataTableResponder();

        return \Response::json(
            $responder->respond($inputs, $page, count($rows), count($rows), $summary)
        );
    }
}
