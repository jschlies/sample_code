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
use App\Waypoint\Models\AuthenticatingEntity;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeAuthenticatingEntityTrait
{
    /**
     * Create fake instance of AuthenticatingEntity and save it in database
     *
     * @param array $authenticating_entities_arr
     * @return AuthenticatingEntity
     */
    public function makeAuthenticatingEntity($authenticating_entities_arr = [])
    {
        $theme = $this->fakeAuthenticatingEntityData($authenticating_entities_arr);
        return $this->AuthenticatingEntityRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of AuthenticatingEntity
     *
     * @param array $authenticating_entities_arr
     * @return AuthenticatingEntity
     */
    public function fakeAuthenticatingEntity($authenticating_entities_arr = [])
    {
        return new AuthenticatingEntity($this->fakeAuthenticatingEntityData($authenticating_entities_arr));
    }

    /**
     * Get fake data of AuthenticatingEntity
     *
     * @param array $authenticating_entities_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeAuthenticatingEntityData($authenticating_entities_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($authenticating_entities_arr);
        return $factory->raw(AuthenticatingEntity::class, $authenticating_entities_arr, $factory_name);
    }
}