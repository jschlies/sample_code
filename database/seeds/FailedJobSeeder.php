<?php

use App\Waypoint\Models\FailedJob;
use App\Waypoint\Repositories\FailedJobRepository;
use App\Waypoint\Seeder;

/**
 * Class FailedJobSeeder
 */
class FailedJobSeeder extends Seeder
{
    /**
     * FailedJobSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(FailedJob::class);
        $this->ModelRepositoryObj = App::make(FailedJobRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}