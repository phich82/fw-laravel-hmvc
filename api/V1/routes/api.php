<?php

use Api\V1\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::get('test', function () {
    dd(1);
});

Route::get('demo', [ApiController::class, 'demo']);