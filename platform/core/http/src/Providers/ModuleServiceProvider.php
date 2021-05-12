<?php

namespace Core\Http\Providers;

use Core\Http\Services\Contracts\HttpContract;
use Core\Http\Services\Implementations\Http;
use Devtools\Providers\AbstractModuleProvider;

class ModuleServiceProvider extends AbstractModuleProvider
{
    /**
     * @return string
     */
    public function getDir()
    {
        return __DIR__;
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        return 'http';
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->app->singleton(HttpContract::class, function () {
            return new Http();
        });
    }
}
