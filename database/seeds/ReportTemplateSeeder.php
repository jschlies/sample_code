<?php

use App\Waypoint\Models\ReportTemplate;
use App\Waypoint\Repositories\ReportTemplateRepository;
use App\Waypoint\Seeder;

/**
 * Class ReportTemplateSeeder
 */
class ReportTemplateSeeder extends Seeder
{
    /**
     * ReportTemplateSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     * @throws Exception
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(ReportTemplate::class);
        $this->ModelRepositoryObj = App::make(ReportTemplateRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}