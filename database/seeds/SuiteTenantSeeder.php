<?php

use App\Waypoint\Repositories\SuiteTenantRepository;
use App\Waypoint\Seeder;
use App\Waypoint\Models\SuiteTenant;

/**
 * Class SuiteTenantSeeder
 */
class SuiteTenantSeeder extends Seeder
{
    /**
     * SuiteTenantSeeder constructor.
     * @param array $attributes
     * @param int $count
     * @param string $factory_name
     */
    public function __construct($attributes = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($attributes, $count, $factory_name);
        $this->setResultingClass(SuiteTenant::class);
        $this->ModelRepositoryObj = App::make(SuiteTenantRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}