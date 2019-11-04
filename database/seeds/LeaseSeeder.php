<?php

use App\Waypoint\Models\Lease;
use App\Waypoint\Repositories\LeaseRepository;
use App\Waypoint\Seeder;

/**
 * Class LeaseSeeder
 */
class LeaseSeeder extends Seeder
{
    /**
     * LeaseSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(Lease::class);
        $this->ModelRepositoryObj = App::make(LeaseRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}