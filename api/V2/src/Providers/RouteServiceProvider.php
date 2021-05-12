<?php

namespace Api\V2\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\File;

class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = 'Api\V2\Http\Controllers';

    protected $prefix = 'api';
    protected $version = 'v2';
    protected $middleware = 'api';

    public function map()
    {
        Route::prefix($this->_prefixApi())
            ->middleware($this->config('api.middleware', $this->middleware))
            ->namespace($this->config('api.namespace', $this->namespace))
            ->group(__DIR__ . '/../../routes/api.php');
    }

    /**
     * Get prefix of api
     *
     * @return string
     */
    private function _prefixApi()
    {
        $prefix  = $this->config('api.prefix', $this->prefix);
        $version = $this->config('api.version', $this->version);

        return "{$prefix}/{$version}";
    }

    /**
     * Get config
     *
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    private function config($key, $default = null)
    {
        try {
            $keys = explode('.', $key);
            if (empty($keys)) {
                return $default;
            }
            $file = array_shift($keys);
            $config = include(__DIR__ . "/../../config/{$file}.php");
            $value = null;
            foreach ($keys as $key) {
                $value = $config[$key];
            }
            return $value;
        } catch (\Exception $e) {
            return $default;
        }
    }
}
