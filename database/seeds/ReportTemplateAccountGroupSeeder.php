<?php

use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Repositories\ReportTemplateAccountGroupRepository;
use App\Waypoint\Seeder;

/**
 * Class ReportTemplateAccountGroupSeeder
 */
class ReportTemplateAccountGroupSeeder extends Seeder
{
    /**
     * ReportTemplateAccountGroupSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     * @throws Exception
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(ReportTemplateAccountGroup::class);
        $this->ModelRepositoryObj = App::make(ReportTemplateAccountGroupRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}