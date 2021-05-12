<?php

namespace Api\V1\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Api\V1\Providers\ModuleServiceProvider;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = 'Api\V1\Http\Controllers';

    protected $prefix = 'api';
    protected $version = 'v1';
    protected $middleware = 'api';

    public function map()
    {
        $module = $this->getModuleName();

        Route::prefix($this->_prefixApi($module))
            ->middleware(config("{$module}::api.middleware", $this->middleware))
            ->namespace(config("{$module}::api.namespace", $this->namespace))
            ->group(__DIR__ . '/../../routes/api.php');
    }

    /**
     * Get prefix of api
     *
     * @return string
     */
    private function _prefixApi($module = '')
    {
        $module = $module ? "{$module}::" : '';
        $prefix  = config("{$module}api.prefix", $this->prefix);
        $version = config("{$module}api.version", $this->version);

        return "{$prefix}/{$version}";
    }

    /**
     * Get module name
     *
     * @return string
     */
    private function getModuleName()
    {
        return (new ModuleServiceProvider(app()->make(Application::class)))->getModuleName();
    }

    /**
     * Get config
     *
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    private function config($key = null, $default = null)
    {
        try {
            $keys = explode('.', $key);
            if (empty($keys)) {
                return $default;
            }
            $filename = array_shift($keys);
            $config = include(__DIR__ . "/../../config/{$filename}.php");
            $value = $config;
            foreach ($keys as $key) {
                $value = $config[$key];
            }
            return $value;
        } catch (\Exception $e) {
            return $default;
        }
    }
}
