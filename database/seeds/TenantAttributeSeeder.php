<?php

use App\Waypoint\Collection;
use App\Waypoint\Models\TenantAttribute;
use App\Waypoint\Repositories\TenantAttributeRepository;
use App\Waypoint\Seeder;

/**
 * Class TenantAttributeSeeder
 */
class TenantAttributeSeeder extends Seeder
{
    /**
     * TenantAttributeSeeder constructor.
     * @param array $attributes
     * @param int $count
     * @param string $factory_name
     * @throws Exception
     */
    public function __construct($attributes = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($attributes, $count, $factory_name);
        $this->setResultingClass(TenantAttribute::class);
        $this->ModelRepositoryObj = App::make(TenantAttributeRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return Collection
     */
    public function run()
    {
        return parent::run();
    }
}