<?php

/**
 * Opt-in ReportKit routes (Laravel 4.1–5.4).
 *
 * Only required when reportkit.settings "routes.enabled" is true and the host
 * calls ReportKit::routes() / ReportKitServiceProvider::registerRoutes().
 * Default is false — hosts should keep explicit Route::get lines from reportkit:make.
 */

use ReportKit\Laravel\Legacy\Http\Controllers\ReportDataController;

Route::get('reportkit/{slug}/data', array(
    'as' => 'reportkit.data',
    'uses' => 'ReportKit\\Laravel\\Legacy\\Http\\Controllers\\ReportDataController@data',
));
