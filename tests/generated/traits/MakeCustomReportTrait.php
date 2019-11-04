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
use App\Waypoint\Models\CustomReport;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeCustomReportTrait
{
    /**
     * Create fake instance of CustomReport and save it in database
     *
     * @param array $custom_reports_arr
     * @return CustomReport
     */
    public function makeCustomReport($custom_reports_arr = [])
    {
        $theme = $this->fakeCustomReportData($custom_reports_arr);
        return $this->CustomReportRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of CustomReport
     *
     * @param array $custom_reports_arr
     * @return CustomReport
     */
    public function fakeCustomReport($custom_reports_arr = [])
    {
        return new CustomReport($this->fakeCustomReportData($custom_reports_arr));
    }

    /**
     * Get fake data of CustomReport
     *
     * @param array $custom_reports_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeCustomReportData($custom_reports_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($custom_reports_arr);
        return $factory->raw(CustomReport::class, $custom_reports_arr, $factory_name);
    }
}