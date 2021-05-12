<?php

namespace Devtools\Providers;

use Devtools\Console\Generators\MakeCommand;
use Devtools\Console\Generators\MakeController;
use Devtools\Console\Generators\MakeFacade;
use Devtools\Console\Generators\MakeMiddleware;
use Devtools\Console\Generators\MakeMigration;
use Devtools\Console\Generators\MakeModel;
use Devtools\Console\Generators\CreateModule;
use Devtools\Console\Generators\MakeProvider;
use Devtools\Console\Generators\MakeRequest;
use Devtools\Console\Generators\MakeSupport;
use Devtools\Console\Generators\MakeView;
use Devtools\Console\Generators\MakeViewComposer;
use Illuminate\Support\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            CreateModule::class,
            MakeProvider::class,
            MakeController::class,
            MakeMiddleware::class,
            MakeRequest::class,
            MakeModel::class,
            MakeFacade::class,
            MakeSupport::class,
            MakeView::class,
            MakeCommand::class,
            MakeViewComposer::class,
            MakeMigration::class,
        ]);
    }
}
