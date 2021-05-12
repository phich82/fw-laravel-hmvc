<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

/**
 * Admin routes
 */

$moduleRoute = 'http';

Route::group(['prefix' => $moduleRoute], function (Router $router) {
    Route::get('/', function() {
        page_title()->setTitle('http');

        return view('http::homepage');
    });
});
