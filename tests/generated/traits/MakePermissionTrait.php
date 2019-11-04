<?php

namespace App\Waypoint\Tests\Generated;

/**
 * README - README - README - README - README
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 * See readme.md
 * This file is generated - edits to this file will be lost.
 * Please read and understand the info on generating models/controllers/requests/test in the readme.md
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 */

use App\Waypoint\Seeder;
use App\Waypoint\Models\Permission;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakePermissionTrait
{
    /**
     * Create fake instance of Permission and save it in database
     *
     * @param array $permissions_arr
     * @return Permission
     */
    public function makePermission($permissions_arr = [])
    {
        $theme = $this->fakePermissionData($permissions_arr);
        return $this->PermissionRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of Permission
     *
     * @param array $permissions_arr
     * @return Permission
     */
    public function fakePermission($permissions_arr = [])
    {
        return new Permission($this->fakePermissionData($permissions_arr));
    }

    /**
     * Get fake data of Permission
     *
     * @param array $permissions_arr
     * @param string $factory_name
     * @return array
     */
    public function fakePermissionData($permissions_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($permissions_arr);
        return $factory->raw(Permission::class, $permissions_arr, $factory_name);
    }
}