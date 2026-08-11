<?php

/**
 * Lorapok ReportKit
 * Copyright (c) 2026 Lorapok Labs (https://lorapok.tech)
 * Licensed under the Lorapok Non-Commercial License 1.0 (Lorapok-NCL-1.0)
 *
 * ReportBrowseController — Post-prepare JSON browse — session-backed prepared rows (Phase K).
 */

namespace ReportKit\Laravel\Legacy\Http\Controllers;

use ReportKit\Core\Http\HandlesReportBrowse;
use ReportKit\Core\Report\ReportRegistry;
use ReportKit\Core\Settings\ReportkitConfig;
use ReportKit\Core\Settings\ReportSettingsResolver;

/**
 * Post-prepare JSON browse — session-backed prepared rows (Phase K).
 */
class ReportBrowseController
{
    use HandlesReportBrowse;
    /**
     * POST /reportkit/{slug}/prepared — store prepared rows in session for browse.
     *
     * @param string $slug
     * @return mixed
     */
    public function storePrepared($slug)
    {
        $definition = ReportRegistry::get($slug);

        if (!$definition) {
            return \Response::json(array('error' => 'Unknown report.'), 404);
        }

        $payload = \Input::json();

        if (!is_array($payload)) {
            $payload = \Input::all();
        }

        $rows = isset($payload['rows']) && is_array($payload['rows']) ? $payload['rows'] : array();

        if (!$rows) {
            return \Response::json(array('error' => 'No prepared rows supplied.'), 422);
        }

        $encoded = json_encode($rows);

        if ($encoded === false) {
            return \Response::json(array('error' => 'Could not serialize rows.'), 422);
        }

        $maxBytes = (int) ReportSettingsResolver::get(
            $slug,
            $this->config(),
            'store.session_persist_max_bytes',
            1500000
        );

        if (strlen($encoded) > $maxBytes) {
            return \Response::json(array('error' => 'Prepared payload over session limit.'), 422);
        }

        $this->session()->put($this->preparedSessionKey($slug), $rows);

        return \Response::json(array(
            'ok' => true,
            'count' => count($rows),
        ));
    }

    /**
     * GET /reportkit/{slug}/browse — DataTables over session prepared rows.
     *
     * @param string $slug
     * @return mixed
     */
    public function browse($slug)
    {
        $definition = ReportRegistry::get($slug);

        if (!$definition) {
            return \Response::json(array('error' => 'Unknown report.'), 404);
        }

        $rows = $this->session()->get($this->preparedSessionKey($slug), array());

        if (!is_array($rows) || !$rows) {
            return \Response::json(array('error' => 'No prepared data. Run prepare first.'), 422);
        }

        $pageLimitMax = (int) ReportSettingsResolver::get(
            $slug,
            $this->config(),
            'table.page_limit_max',
            10000
        );

        $payload = $this->browsePreparedRows(\Input::all(), $rows, $definition, $pageLimitMax);

        return \Response::json($payload);
    }

    /**
     * @return array
     */
    protected function config()
    {
        return ReportkitConfig::load(
            function_exists('app') ? app() : null,
            dirname(dirname(dirname(__DIR__))) . '/config/reportkit.php'
        );
    }

    /**
     * @return \Illuminate\Session\Store|\Illuminate\Contracts\Session\Session|object
     */
    protected function session()
    {
        if (function_exists('session')) {
            return session();
        }

        return app('session');
    }
}
