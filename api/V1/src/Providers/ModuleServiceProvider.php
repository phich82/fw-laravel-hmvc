<?php

namespace Api\V1\Providers;

use Api\V1\Exceptions\Handler;
use Devtools\Providers\AbstractModuleProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;

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
        return 'api-v1';
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->app->bind(ExceptionHandler::class, Handler::class);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->app->register(RouteServiceProvider::class);
    }
}
