<?php

use App\Waypoint\Models\LeaseSchedule;
use App\Waypoint\Repositories\LeaseScheduleRepository;
use App\Waypoint\Seeder;

/**
 * Class LeaseScheduleSeeder
 */
class LeaseScheduleSeeder extends Seeder
{
    /**
     * LeaseScheduleSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(LeaseSchedule::class);
        $this->ModelRepositoryObj = App::make(LeaseScheduleRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}