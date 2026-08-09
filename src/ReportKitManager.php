<?php

namespace ReportKit\Laravel\Legacy;

use ReportKit\Core\Report\Report as CoreReport;
use ReportKit\Core\Report\ReportRegistry;

/**
 * Application binding for the ReportKit facade.
 */
class ReportKitManager
{
    /**
     * @param string $id
     * @param callable|null $callback
     * @return \ReportKit\Core\Report\ReportDefinition
     */
    public function define($id, $callback = null)
    {
        return CoreReport::define($id, $callback);
    }

    /**
     * @param string $id
     * @return \ReportKit\Core\Report\ReportDefinition|null
     */
    public function get($id)
    {
        return CoreReport::get($id);
    }

    /**
     * @return array
     */
    public function all()
    {
        return CoreReport::all();
    }

    /**
     * Placeholder for L4.1 route registration (domain groups stay in the host app).
     * Prints registered report ids for install verification.
     *
     * @return array
     */
    public function routes()
    {
        return array_keys(ReportRegistry::all());
    }
}
