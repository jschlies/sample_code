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
use App\Waypoint\Models\Client;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeClientTrait
{
    /**
     * Create fake instance of Client and save it in database
     *
     * @param array $clients_arr
     * @return Client
     */
    public function makeClient($clients_arr = [])
    {
        $theme = $this->fakeClientData($clients_arr);
        return $this->ClientRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of Client
     *
     * @param array $clients_arr
     * @return Client
     */
    public function fakeClient($clients_arr = [])
    {
        return new Client($this->fakeClientData($clients_arr));
    }

    /**
     * Get fake data of Client
     *
     * @param array $clients_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeClientData($clients_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($clients_arr);
        return $factory->raw(Client::class, $clients_arr, $factory_name);
    }
}