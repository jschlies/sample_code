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
use App\Waypoint\Models\Suite;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeSuiteTrait
{
    /**
     * Create fake instance of Suite and save it in database
     *
     * @param array $suites_arr
     * @return Suite
     */
    public function makeSuite($suites_arr = [])
    {
        $theme = $this->fakeSuiteData($suites_arr);
        return $this->SuiteRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of Suite
     *
     * @param array $suites_arr
     * @return Suite
     */
    public function fakeSuite($suites_arr = [])
    {
        return new Suite($this->fakeSuiteData($suites_arr));
    }

    /**
     * Get fake data of Suite
     *
     * @param array $suites_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeSuiteData($suites_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($suites_arr);
        return $factory->raw(Suite::class, $suites_arr, $factory_name);
    }
}