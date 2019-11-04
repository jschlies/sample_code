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
use App\Waypoint\Models\Role;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeRoleTrait
{
    /**
     * Create fake instance of Role and save it in database
     *
     * @param array $roles_arr
     * @return Role
     */
    public function makeRole($roles_arr = [])
    {
        $theme = $this->fakeRoleData($roles_arr);
        return $this->RoleRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of Role
     *
     * @param array $roles_arr
     * @return Role
     */
    public function fakeRole($roles_arr = [])
    {
        return new Role($this->fakeRoleData($roles_arr));
    }

    /**
     * Get fake data of Role
     *
     * @param array $roles_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeRoleData($roles_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($roles_arr);
        return $factory->raw(Role::class, $roles_arr, $factory_name);
    }
}