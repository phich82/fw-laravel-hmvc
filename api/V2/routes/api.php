<?php

use Illuminate\Support\Facades\Route;
use Api\V2\Http\Controllers\ApiController;

Route::get('test', function () {
    dd(2);
});

Route::get('demo', [ApiController::class, 'demo']);