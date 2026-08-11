<?php

/**
 * Lorapok ReportKit
 * Copyright (c) 2026 Lorapok Labs (https://lorapok.tech)
 * Licensed under the Lorapok Non-Commercial License 1.0 (Lorapok-NCL-1.0)
 *
 * ReportSendController — POST send prepared report as ZIP attachment (Phase B5).
 */

namespace ReportKit\Laravel\Legacy\Http\Controllers;

use ReportKit\Core\Http\AjaxResponse;
use ReportKit\Core\Http\HandlesReportSend;
use ReportKit\Core\Settings\ReportkitConfig;

/**
 * POST send prepared report as ZIP attachment (Phase B5).
 */
class ReportSendController
{
    use HandlesReportSend;

    /**
     * @param string $slug
     * @return mixed
     */
    public function send($slug)
    {
        $configPath = dirname(dirname(dirname(__DIR__))) . '/config/reportkit.php';
        $config = ReportkitConfig::load(function_exists('app') ? app() : null, $configPath);
        $config['mail_enabled'] = $this->configValue('reportkit.mail.enabled', true);

        $rows = $this->session()->get($this->preparedSessionKey($slug), array());
        $inputs = \Input::all();
        $inputs = is_array($inputs) ? $inputs : array();

        $upload = class_exists('Input') ? \Input::file('file') : null;

        if ($upload) {
            $payload = $this->reportSendFromUpload($slug, $inputs, $config, $upload);
        } else {
            $payload = $this->reportSendPayload($slug, $inputs, $config, is_array($rows) ? $rows : array());
        }

        if (AjaxResponse::isError($payload)) {
            $status = AjaxResponse::status($payload);
            unset($payload['_status']);

            return \Response::json($payload, $status);
        }

        unset($payload['_mail_plan']);
        $payload['message'] = 'Send plan ready — wire host mailer to dispatch.';

        return \Response::json($payload);
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
     * @return object
     */
    protected function session()
    {
        if (function_exists('session')) {
            return session();
        }

        return app('session');
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
