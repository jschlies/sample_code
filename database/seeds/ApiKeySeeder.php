<?php

use App\Waypoint\Models\ApiKey;
use App\Waypoint\Repositories\ApiKeyRepository;
use App\Waypoint\Seeder;

/**
 * Class ApiKeySeeder
 */
class ApiKeySeeder extends Seeder
{
    /**
     * ApiKeySeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(ApiKey::class);
        $this->ModelRepositoryObj = App::make(ApiKeyRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}