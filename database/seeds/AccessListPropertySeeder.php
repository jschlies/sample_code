<?php

use App\Waypoint\Models\AccessListProperty;
use App\Waypoint\Repositories\AccessListPropertyRepository;
use App\Waypoint\Seeder;

/**
 * Class AccessListPropertySeeder
 */
class AccessListPropertySeeder extends Seeder
{
    /**
     * AccessListPropertySeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(AccessListProperty::class);
        $this->ModelRepositoryObj = App::make(AccessListPropertyRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}