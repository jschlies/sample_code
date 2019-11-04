<?php

use App\Waypoint\Repositories\PropertyGroupRepository;
use App\Waypoint\Seeder;
use App\Waypoint\Models\PropertyGroup;

/**
 * Class PropertyGroupSeeder
 */
class PropertyGroupSeeder extends Seeder
{
    /**
     * PropertyGroupSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     * @throws Exception
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(PropertyGroup::class);
        $this->ModelRepositoryObj = App::make(PropertyGroupRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     * @throws Exception
     */
    public function run()
    {
        return parent::run();
    }
}