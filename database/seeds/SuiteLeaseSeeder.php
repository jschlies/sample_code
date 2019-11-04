<?php

use App\Waypoint\Models\SuiteLease;
use App\Waypoint\Repositories\SuiteLeaseRepository;
use App\Waypoint\Seeder;

/**
 * Class SuiteLeaseSeeder
 */
class SuiteLeaseSeeder extends Seeder
{
    /**
     * SuiteLeaseSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(SuiteLease::class);
        $this->ModelRepositoryObj = App::make(SuiteLeaseRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}