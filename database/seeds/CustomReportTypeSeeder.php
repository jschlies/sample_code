<?php

use App\Waypoint\Models\CustomReportType;
use App\Waypoint\Repositories\CustomReportTypeRepository;
use App\Waypoint\Seeder;

/**
 * Class CustomReportTypeSeeder
 */
class CustomReportTypeSeeder extends Seeder
{
    /**
     * CustomReportTypeSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     * @throws \Exception
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(CustomReportType::class);
        $this->ModelRepositoryObj = App::make(CustomReportTypeRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}