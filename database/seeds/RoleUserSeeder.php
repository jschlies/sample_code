<?php

use App\Waypoint\Models\RoleUser;
use App\Waypoint\Repositories\RoleUserRepository;
use App\Waypoint\Seeder;

/**
 * Class RoleUserSeeder
 */
class RoleUserSeeder extends Seeder
{
    /**
     * RoleUserSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(RoleUser::class);
        $this->ModelRepositoryObj = App::make(RoleUserRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}