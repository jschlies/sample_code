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
use App\Waypoint\Models\RelatedUser;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeRelatedUserTrait
{
    /**
     * Create fake instance of RelatedUser and save it in database
     *
     * @param array $related_users_arr
     * @return RelatedUser
     */
    public function makeRelatedUser($related_users_arr = [])
    {
        $theme = $this->fakeRelatedUserData($related_users_arr);
        return $this->RelatedUserRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of RelatedUser
     *
     * @param array $related_users_arr
     * @return RelatedUser
     */
    public function fakeRelatedUser($related_users_arr = [])
    {
        return new RelatedUser($this->fakeRelatedUserData($related_users_arr));
    }

    /**
     * Get fake data of RelatedUser
     *
     * @param array $related_users_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeRelatedUserData($related_users_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($related_users_arr);
        return $factory->raw(RelatedUser::class, $related_users_arr, $factory_name);
    }
}