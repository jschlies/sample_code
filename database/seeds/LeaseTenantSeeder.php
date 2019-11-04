<?php

use App\Waypoint\Repositories\LeaseTenantRepository;
use App\Waypoint\Seeder;
use App\Waypoint\Models\LeaseTenant;

/**
 * Class LeaseTenantSeeder
 */
class LeaseTenantSeeder extends Seeder
{
    /**
     * LeaseTenantSeeder constructor.
     * @param array $attributes
     * @param int $count
     * @param string $factory_name
     */
    public function __construct($attributes = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($attributes, $count, $factory_name);
        $this->setResultingClass(LeaseTenant::class);
        $this->ModelRepositoryObj = App::make(LeaseTenantRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}