<?php

/**
 * Lorapok ReportKit
 * Copyright (c) 2026 Lorapok Labs (https://lorapok.tech)
 * Licensed under the Lorapok Non-Commercial License 1.0 (Lorapok-NCL-1.0)
 *
 * ReportKitManager — Application binding for the ReportKit facade.
 */

namespace ReportKit\Laravel\Legacy;

use ReportKit\Core\Source\MergedRowSource;
use ReportKit\Laravel\Legacy\Source\ConnectionRowSource;

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
     * Opt-in route registration. When routes.enabled is true, loads Http/routes.php.
     * Always returns registered report ids for install verification.
     *
     * @return array
     */

    /**
     * @param string $connection
     * @param callable $callback
     * @param string|null $label
     * @return ConnectionRowSource
     */
    public function connection($connection, $callback, $label = null)
    {
        return new ConnectionRowSource($connection, $callback, null, $label);
    }

    /**
     * @param array $sources
     * @param string|null $dedupeKey
     * @param string|null $orderBy
     * @param string $direction
     * @return MergedRowSource
     */
    public function merged(array $sources, $dedupeKey = null, $orderBy = null, $direction = 'asc')
    {
        return new MergedRowSource($sources, $dedupeKey, $orderBy, $direction);
    }

    public function routes()
    {
        ReportKitServiceProvider::registerRoutes();

        return array_keys(ReportRegistry::all());
    }
}
