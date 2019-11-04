<?php

use App\Waypoint\Models\PropertyNativeCoa;
use App\Waypoint\Repositories\PropertyNativeCoaRepository;
use App\Waypoint\Seeder;

/**
 * Class PropertyNativeCoaSeeder
 */
class PropertyNativeCoaSeeder extends Seeder
{
    /**
     * PropertyNativeCoaSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     * @throws \Exception
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(PropertyNativeCoa::class);
        $this->ModelRepositoryObj = App::make(PropertyNativeCoaRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}