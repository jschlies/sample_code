<?php

use App\Waypoint\Collection;
use App\Waypoint\Models\TenantTenantAttribute;
use App\Waypoint\Repositories\TenantTenantAttributeRepository;
use App\Waypoint\Seeder;

/**
 * Class TenantTenantAttributeSeeder
 */
class TenantTenantAttributeSeeder extends Seeder
{
    /**
     * TenantTenantAttributeSeeder constructor.
     * @param array $attributes
     * @param int $count
     * @param string $factory_name
     * @throws Exception
     */
    public function __construct($attributes = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($attributes, $count, $factory_name);
        $this->setResultingClass(TenantTenantAttribute::class);
        $this->ModelRepositoryObj = App::make(TenantTenantAttributeRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return Collection
     */
    public function run()
    {
        return parent::run();
    }
}