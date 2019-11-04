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
use App\Waypoint\Models\RoleUser;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeRoleUserTrait
{
    /**
     * Create fake instance of RoleUser and save it in database
     *
     * @param array $role_users_arr
     * @return RoleUser
     */
    public function makeRoleUser($role_users_arr = [])
    {
        $theme = $this->fakeRoleUserData($role_users_arr);
        return $this->RoleUserRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of RoleUser
     *
     * @param array $role_users_arr
     * @return RoleUser
     */
    public function fakeRoleUser($role_users_arr = [])
    {
        return new RoleUser($this->fakeRoleUserData($role_users_arr));
    }

    /**
     * Get fake data of RoleUser
     *
     * @param array $role_users_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeRoleUserData($role_users_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($role_users_arr);
        return $factory->raw(RoleUser::class, $role_users_arr, $factory_name);
    }
}