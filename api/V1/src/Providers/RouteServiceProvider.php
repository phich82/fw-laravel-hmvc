<?php

namespace Api\V1\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = 'Api\V1\Http\Controllers';

    protected $prefix = 'api';
    protected $version = 'v1';
    protected $middleware = 'api';

    public function map()
    {
        Route::prefix($this->_prefixApi())
            ->middleware(config('api.middleware', $this->middleware))
            ->namespace(config('api.namespace', $this->namespace))
            ->group(__DIR__ . '/../../routes/api.php');
    }

    /**
     * Get prefix of api
     *
     * @return string
     */
    private function _prefixApi()
    {
        $prefix  = config('api.prefix', $this->prefix);
        $version = config('api.version', $this->version);

        return "{$prefix}/{$version}";
    }
}
