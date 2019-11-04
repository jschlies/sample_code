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
use App\Waypoint\Models\CustomReportType;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeCustomReportTypeTrait
{
    /**
     * Create fake instance of CustomReportType and save it in database
     *
     * @param array $custom_report_types_arr
     * @return CustomReportType
     */
    public function makeCustomReportType($custom_report_types_arr = [])
    {
        $theme = $this->fakeCustomReportTypeData($custom_report_types_arr);
        return $this->CustomReportTypeRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of CustomReportType
     *
     * @param array $custom_report_types_arr
     * @return CustomReportType
     */
    public function fakeCustomReportType($custom_report_types_arr = [])
    {
        return new CustomReportType($this->fakeCustomReportTypeData($custom_report_types_arr));
    }

    /**
     * Get fake data of CustomReportType
     *
     * @param array $custom_report_types_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeCustomReportTypeData($custom_report_types_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($custom_report_types_arr);
        return $factory->raw(CustomReportType::class, $custom_report_types_arr, $factory_name);
    }
}