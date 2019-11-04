<?php

use App\Waypoint\Models\ApiLog;
use App\Waypoint\Repositories\ApiLogRepository;
use App\Waypoint\Seeder;

/**
 * Class ApiLogSeeder
 */
class ApiLogSeeder extends Seeder
{
    /**
     * ApiLogSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(ApiLog::class);
        $this->ModelRepositoryObj = App::make(ApiLogRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}