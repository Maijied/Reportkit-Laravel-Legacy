{{--
 * Lorapok ReportKit
 * Copyright (c) 2026 Lorapok Labs (https://lorapok.tech)
 * Licensed under the Lorapok Non-Commercial License 1.0 (Lorapok-NCL-1.0)
 *
 * settings-bootstrap — Blade UI partial.
--}}

{{-- Inline browser-safe settings — avoids extra round-trip (Phase A2/A3) --}}

@php

    $rkPackageRoot = dirname(dirname(dirname(__DIR__)));

    $rkReportId = isset($reportkitReportId) ? $reportkitReportId : null;

    $rkSettingsPayload = array();



    if (function_exists('app')) {

        try {

            $rkSettingsPayload = \ReportKit\Core\Settings\ReportBrowserSettings::payload(

                app(),

                $rkPackageRoot . '/config/reportkit.php',

                $rkReportId

            );

        } catch (\Exception $e) {

            $rkSettingsPayload = array();

        }

    }



    $rkSettingsJson = \ReportKit\Core\Settings\BrowserSettingsBuilder::encode($rkSettingsPayload);

@endphp

<script>

window.__REPORTKIT_SETTINGS__ = {!! $rkSettingsJson !!};

</script>


