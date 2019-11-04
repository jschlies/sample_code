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
use App\Waypoint\Models\PermissionRole;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakePermissionRoleTrait
{
    /**
     * Create fake instance of PermissionRole and save it in database
     *
     * @param array $permission_roles_arr
     * @return PermissionRole
     */
    public function makePermissionRole($permission_roles_arr = [])
    {
        $theme = $this->fakePermissionRoleData($permission_roles_arr);
        return $this->PermissionRoleRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of PermissionRole
     *
     * @param array $permission_roles_arr
     * @return PermissionRole
     */
    public function fakePermissionRole($permission_roles_arr = [])
    {
        return new PermissionRole($this->fakePermissionRoleData($permission_roles_arr));
    }

    /**
     * Get fake data of PermissionRole
     *
     * @param array $permission_roles_arr
     * @param string $factory_name
     * @return array
     */
    public function fakePermissionRoleData($permission_roles_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($permission_roles_arr);
        return $factory->raw(PermissionRole::class, $permission_roles_arr, $factory_name);
    }
}