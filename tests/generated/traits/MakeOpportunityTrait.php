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
use App\Waypoint\Models\Opportunity;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeOpportunityTrait
{
    /**
     * Create fake instance of Opportunity and save it in database
     *
     * @param array $opportunities_arr
     * @return Opportunity
     */
    public function makeOpportunity($opportunities_arr = [])
    {
        $theme = $this->fakeOpportunityData($opportunities_arr);
        return $this->OpportunityRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of Opportunity
     *
     * @param array $opportunities_arr
     * @return Opportunity
     */
    public function fakeOpportunity($opportunities_arr = [])
    {
        return new Opportunity($this->fakeOpportunityData($opportunities_arr));
    }

    /**
     * Get fake data of Opportunity
     *
     * @param array $opportunities_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeOpportunityData($opportunities_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($opportunities_arr);
        return $factory->raw(Opportunity::class, $opportunities_arr, $factory_name);
    }
}