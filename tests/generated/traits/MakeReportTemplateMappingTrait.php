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
use App\Waypoint\Models\ReportTemplateMapping;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeReportTemplateMappingTrait
{
    /**
     * Create fake instance of ReportTemplateMapping and save it in database
     *
     * @param array $report_template_mappings_arr
     * @return ReportTemplateMapping
     */
    public function makeReportTemplateMapping($report_template_mappings_arr = [])
    {
        $theme = $this->fakeReportTemplateMappingData($report_template_mappings_arr);
        return $this->ReportTemplateMappingRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of ReportTemplateMapping
     *
     * @param array $report_template_mappings_arr
     * @return ReportTemplateMapping
     */
    public function fakeReportTemplateMapping($report_template_mappings_arr = [])
    {
        return new ReportTemplateMapping($this->fakeReportTemplateMappingData($report_template_mappings_arr));
    }

    /**
     * Get fake data of ReportTemplateMapping
     *
     * @param array $report_template_mappings_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeReportTemplateMappingData($report_template_mappings_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($report_template_mappings_arr);
        return $factory->raw(ReportTemplateMapping::class, $report_template_mappings_arr, $factory_name);
    }
}