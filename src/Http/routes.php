<?php

/**
 * Opt-in ReportKit routes (Laravel 4.1–5.4).
 */

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
