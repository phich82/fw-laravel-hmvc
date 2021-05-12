<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

/**
 * Admin routes
 */

$moduleRoute = 'sample-module';

Route::group(['prefix' => $moduleRoute], function (Router $router) {
    Route::get('/', function() {
        page_title()->setTitle('sample-module');
        return view('sample-module::homepage');
    });
});
