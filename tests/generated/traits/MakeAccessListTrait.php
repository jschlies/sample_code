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
use App\Waypoint\Models\AccessList;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeAccessListTrait
{
    /**
     * Create fake instance of AccessList and save it in database
     *
     * @param array $access_lists_arr
     * @return AccessList
     */
    public function makeAccessList($access_lists_arr = [])
    {
        $theme = $this->fakeAccessListData($access_lists_arr);
        return $this->AccessListRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of AccessList
     *
     * @param array $access_lists_arr
     * @return AccessList
     */
    public function fakeAccessList($access_lists_arr = [])
    {
        return new AccessList($this->fakeAccessListData($access_lists_arr));
    }

    /**
     * Get fake data of AccessList
     *
     * @param array $access_lists_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeAccessListData($access_lists_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($access_lists_arr);
        return $factory->raw(AccessList::class, $access_lists_arr, $factory_name);
    }
}