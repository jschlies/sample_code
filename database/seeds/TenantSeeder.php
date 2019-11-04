<?php

use App\Waypoint\Collection;
use App\Waypoint\Models\Tenant;
use App\Waypoint\Repositories\TenantRepository;
use App\Waypoint\Seeder;

/**
 * Class TenantSeeder
 */
class TenantSeeder extends Seeder
{
    /**
     * TenantSeeder constructor.
     * @param array $attributes
     * @param int $count
     * @param string $factory_name
     * @throws Exception
     */
    public function __construct($attributes = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($attributes, $count, $factory_name);
        $this->setResultingClass(Tenant::class);
        $this->ModelRepositoryObj = App::make(TenantRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return Collection
     */
    public function run()
    {
        return parent::run();
    }
}