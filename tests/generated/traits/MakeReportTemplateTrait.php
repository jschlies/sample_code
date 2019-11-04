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
use App\Waypoint\Models\ReportTemplate;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeReportTemplateTrait
{
    /**
     * Create fake instance of ReportTemplate and save it in database
     *
     * @param array $report_templates_arr
     * @return ReportTemplate
     */
    public function makeReportTemplate($report_templates_arr = [])
    {
        $theme = $this->fakeReportTemplateData($report_templates_arr);
        return $this->ReportTemplateRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of ReportTemplate
     *
     * @param array $report_templates_arr
     * @return ReportTemplate
     */
    public function fakeReportTemplate($report_templates_arr = [])
    {
        return new ReportTemplate($this->fakeReportTemplateData($report_templates_arr));
    }

    /**
     * Get fake data of ReportTemplate
     *
     * @param array $report_templates_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeReportTemplateData($report_templates_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($report_templates_arr);
        return $factory->raw(ReportTemplate::class, $report_templates_arr, $factory_name);
    }
}