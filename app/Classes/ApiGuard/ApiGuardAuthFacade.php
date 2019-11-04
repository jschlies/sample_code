<?php

namespace App\Waypoint\Http;

use Illuminate\Support\Facades\Facade;

class ApiGuardAuthFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ApiGuardAuth::class;
    }
}