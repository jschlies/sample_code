<?php

namespace App\Waypoint\Tests;

use App\Waypoint\Seeder;
use App\Waypoint\Tests\Factory as EloquentFactory;
use Faker\Generator as FakerGenerator;
use Illuminate\Database\DatabaseServiceProvider as DatabaseServiceProviderBase;

class DatabaseServiceProvider extends DatabaseServiceProviderBase
{
    /**
     * Register the Eloquent factory instance in the container.
     *
     * @return void
     */
    protected function registerEloquentFactory()
    {
        $this->app->singleton(
            FakerGenerator::class,
            function ()
            {
                return Seeder::getFakerObj();
            }
        );

        $this->app->singleton(
            EloquentFactory::class,
            function ($app)
            {
                return EloquentFactory::construct(Seeder::getFakerObj(), database_path('factories'));
            }
        );
    }
}
