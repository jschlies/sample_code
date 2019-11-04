<?php

namespace App\Waypoint;

use App\Waypoint\Tests\Factory;

/**
 * Class ModelSaveAndValidateTrait
 * @package App\Waypoint\Models
 *
 * NOTE NOTE NOTE
 * This trait exists because the User, permission and role models
 * extend App\Waypoint\Models\Entrust\User which extends App\Waypoint\Models\Entrust which extends blah blah.
 * the point is that if we want to add base functionality to all models, we need to use this trait
 *
 */
trait SeederFactoryTrait
{
    /**
     * @return mixed
     */
    function factory()
    {
        $factory = app(Factory::class);

        $arguments = func_get_args();

        if (isset($arguments[1]) && is_string($arguments[1]))
        {
            return $factory->of($arguments[0], $arguments[1])->times(isset($arguments[2]) ? $arguments[2] : 1);
        }
        elseif (isset($arguments[1]))
        {
            return $factory->of($arguments[0])->times($arguments[1]);
        }
        else
        {
            return $factory->of($arguments[0]);
        }
    }
}