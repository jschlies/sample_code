<?php

use App\Waypoint\Models\AccessList;
use App\Waypoint\Repositories\AccessListRepository;
use App\Waypoint\Seeder;

/**
 * Class AccessListSeeder
 */
class AccessListSeeder extends Seeder
{
    /**
     * AccessListSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(AccessList::class);
        $this->ModelRepositoryObj = App::make(AccessListRepository::class)->setSuppressEvents(true);;
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}