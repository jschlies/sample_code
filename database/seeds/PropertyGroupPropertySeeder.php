<?php

use App\Waypoint\Models\PropertyGroupProperty;
use App\Waypoint\Repositories\PropertyGroupPropertyRepository;
use App\Waypoint\Seeder;

/**
 * Class PropertyGroupPropertySeeder
 */
class PropertyGroupPropertySeeder extends Seeder
{
    /**
     * PropertyGroupPropertySeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(PropertyGroupProperty::class);
        $this->ModelRepositoryObj = App::make(PropertyGroupPropertyRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}