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
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeReportTemplateAccountGroupTrait
{
    /**
     * Create fake instance of ReportTemplateAccountGroup and save it in database
     *
     * @param array $report_template_account_groups_arr
     * @return ReportTemplateAccountGroup
     */
    public function makeReportTemplateAccountGroup($report_template_account_groups_arr = [])
    {
        $theme = $this->fakeReportTemplateAccountGroupData($report_template_account_groups_arr);
        return $this->ReportTemplateAccountGroupRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of ReportTemplateAccountGroup
     *
     * @param array $report_template_account_groups_arr
     * @return ReportTemplateAccountGroup
     */
    public function fakeReportTemplateAccountGroup($report_template_account_groups_arr = [])
    {
        return new ReportTemplateAccountGroup($this->fakeReportTemplateAccountGroupData($report_template_account_groups_arr));
    }

    /**
     * Get fake data of ReportTemplateAccountGroup
     *
     * @param array $report_template_account_groups_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeReportTemplateAccountGroupData($report_template_account_groups_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($report_template_account_groups_arr);
        return $factory->raw(ReportTemplateAccountGroup::class, $report_template_account_groups_arr, $factory_name);
    }
}