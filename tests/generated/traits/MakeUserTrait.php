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
use App\Waypoint\Models\User;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeUserTrait
{
    /**
     * Create fake instance of User and save it in database
     *
     * @param array $users_arr
     * @return User
     */
    public function makeUser($users_arr = [])
    {
        $theme = $this->fakeUserData($users_arr);
        return $this->UserRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of User
     *
     * @param array $users_arr
     * @return User
     */
    public function fakeUser($users_arr = [])
    {
        return new User($this->fakeUserData($users_arr));
    }

    /**
     * Get fake data of User
     *
     * @param array $users_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeUserData($users_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($users_arr);
        return $factory->raw(User::class, $users_arr, $factory_name);
    }
}