<?php

use App\Waypoint\Collection;
use App\Waypoint\Models\TenantIndustry;
use App\Waypoint\Repositories\TenantIndustryRepository;
use App\Waypoint\Seeder;

/**
 * Class TenantIndustrySeeder
 */
class TenantIndustrySeeder extends Seeder
{
    /**
     * TenantIndustrySeeder constructor.
     * @param array $attributes
     * @param int $count
     * @param string $factory_name
     * @throws Exception
     */
    public function __construct($attributes = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($attributes, $count, $factory_name);
        $this->setResultingClass(TenantIndustry::class);
        $this->ModelRepositoryObj = App::make(TenantIndustryRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return Collection
     */
    public function run()
    {
        return parent::run();
    }
}