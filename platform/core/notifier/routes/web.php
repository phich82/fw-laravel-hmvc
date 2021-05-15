<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

/**
 * Admin routes
 */

$moduleRoute = 'notifier';

Route::group(['prefix' => $moduleRoute], function (Router $router) {
    Route::get('/', function() {
        page_title()->setTitle('notifier');

        return view('notifier::homepage');
    });
});
