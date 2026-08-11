<?php

/**
 * Lorapok ReportKit
 * Copyright (c) 2026 Lorapok Labs (https://lorapok.tech)
 * Licensed under the Lorapok Non-Commercial License 1.0 (Lorapok-NCL-1.0)
 *
 * routes.php — package HTTP routes.
 */

/**
 * Opt-in ReportKit routes (Laravel 4.1–5.4).
 */

Route::get('reportkit/settings.json', array(
    'as' => 'reportkit.settings',
    'uses' => 'ReportKit\\Laravel\\Legacy\\Http\\Controllers\\SettingsController@json',
));

Route::get('reportkit/{slug}/settings.json', array(
    'as' => 'reportkit.report.settings',
    'uses' => 'ReportKit\\Laravel\\Legacy\\Http\\Controllers\\SettingsController@forReport',
));

Route::get('reportkit/{slug}/data', array(
    'as' => 'reportkit.data',
    'uses' => 'ReportKit\\Laravel\\Legacy\\Http\\Controllers\\ReportDataController@data',
));

Route::get('reportkit/{slug}/weeks', array(
    'as' => 'reportkit.weeks',
    'uses' => 'ReportKit\\Laravel\\Legacy\\Http\\Controllers\\ReportWeeksController@weeks',
));

Route::get('reportkit/{slug}/rows', array(
    'as' => 'reportkit.rows',
    'uses' => 'ReportKit\\Laravel\\Legacy\\Http\\Controllers\\ReportWeeksController@rows',
));

Route::get('reportkit/{slug}/trace', array(
    'as' => 'reportkit.trace',
    'uses' => 'ReportKit\\Laravel\\Legacy\\Http\\Controllers\\ReportWeeksController@trace',
));

Route::post('reportkit/{slug}/prepared', array(
    'as' => 'reportkit.prepared.store',
    'uses' => 'ReportKit\\Laravel\\Legacy\\Http\\Controllers\\ReportBrowseController@storePrepared',
));

Route::get('reportkit/{slug}/browse', array(
    'as' => 'reportkit.browse',
    'uses' => 'ReportKit\\Laravel\\Legacy\\Http\\Controllers\\ReportBrowseController@browse',
));

Route::post('reportkit/{slug}/send', array(
    'as' => 'reportkit.send',
    'uses' => 'ReportKit\\Laravel\\Legacy\\Http\\Controllers\\ReportSendController@send',
));
