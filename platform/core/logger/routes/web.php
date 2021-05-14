<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

/**
 * Admin routes
 */

$moduleRoute = 'logger';

Route::group(['prefix' => $moduleRoute], function (Router $router) {
    Route::get('/', function() {
        page_title()->setTitle('logger');

        return view('logger::homepage');
    });
});
