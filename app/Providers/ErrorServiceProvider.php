<?php

namespace App\Waypoint\Providers;

use Illuminate\Support\ServiceProvider;
use App\Waypoint\Exceptions\ExceptionHandler;

class ErrorServiceProvider extends ServiceProvider
{

    /**
     * Register any error handlers.
     *
     * @return void
     */
    public function boot()
    {
        // This is only if you use the Whoops error handler (install via composer).
        /*$whoops = new Run;
        $whoops->pushHandler( new PrettyPageHandler );
        $whoops->register();*/
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'Illuminate\Contracts\Debug\ExceptionHandler',
            ExceptionHandler::class
        );
    }

}