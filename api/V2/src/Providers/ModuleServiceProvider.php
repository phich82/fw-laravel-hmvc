<?php

namespace Api\V2\Providers;

use Api\V2\Exceptions\Handler;
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
        return 'api-v2';
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
