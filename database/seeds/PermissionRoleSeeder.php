<?php

use App\Waypoint\Models\PermissionRole;
use App\Waypoint\Repositories\PermissionRoleRepository;
use App\Waypoint\Seeder;

/**
 * Class PermissionRoleSeeder
 */
class PermissionRoleSeeder extends Seeder
{
    /**
     * PermissionRoleSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(PermissionRole::class);
        $this->ModelRepositoryObj = App::make(PermissionRoleRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}