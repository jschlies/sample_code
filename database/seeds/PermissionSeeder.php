<?php

use App\Waypoint\Models\Permission;
use App\Waypoint\Repositories\PermissionRepository;
use App\Waypoint\Seeder;

/**
 * Class PermissionSeeder
 */
class PermissionSeeder extends Seeder
{
    /**
     * PermissionSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(Permission::class);
        $this->ModelRepositoryObj = App::make(PermissionRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}