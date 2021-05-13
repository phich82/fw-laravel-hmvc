<?php

namespace App\Binding;

use Core\Http\Services\Contracts\HttpContract;
use Core\Http\Services\Implementations\Http;

class Binding
{
    static function start($app = null)
    {
        // Binding by context
        app() // if it is ApiController of version 1, we use the http config of version 1
            ->when([\Api\V1\Http\Controllers\ApiController::class])
            ->needs(HttpContract::class)
            ->give(fn() => new Http(config('api-v1::api.http_config', [])));
            // ->give(function () {
            //     return new Http(config('api-v1::api.http_config', []));
            // });

        app() // if it is Apiontroller of version 2, we use the http config of version 2
            ->when([\Api\V2\Http\Controllers\ApiController::class])
            ->needs(HttpContract::class)
            ->give(fn() => new Http(config('api-v2::api.http_config', [])));
            // ->give(function () {
            //     return new Http(config('api-v2::api.http_config', []));
            // });
    }
}
