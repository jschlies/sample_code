<?php

use App\Waypoint\Repositories\AccessListUserRepository;
use App\Waypoint\Seeder;
use App\Waypoint\Models\AccessListUser;

/**
 * Class AccessListUserSeeder
 */
class AccessListUserSeeder extends Seeder
{
    /**
     * AccessListUserSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     * @throws Exception
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(AccessListUser::class);
        $this->ModelRepositoryObj = App::make(AccessListUserRepository::class)->setSuppressEvents(true);;
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}