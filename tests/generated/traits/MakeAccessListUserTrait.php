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
use App\Waypoint\Models\AccessListUser;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeAccessListUserTrait
{
    /**
     * Create fake instance of AccessListUser and save it in database
     *
     * @param array $access_list_users_arr
     * @return AccessListUser
     */
    public function makeAccessListUser($access_list_users_arr = [])
    {
        $theme = $this->fakeAccessListUserData($access_list_users_arr);
        return $this->AccessListUserRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of AccessListUser
     *
     * @param array $access_list_users_arr
     * @return AccessListUser
     */
    public function fakeAccessListUser($access_list_users_arr = [])
    {
        return new AccessListUser($this->fakeAccessListUserData($access_list_users_arr));
    }

    /**
     * Get fake data of AccessListUser
     *
     * @param array $access_list_users_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeAccessListUserData($access_list_users_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($access_list_users_arr);
        return $factory->raw(AccessListUser::class, $access_list_users_arr, $factory_name);
    }
}