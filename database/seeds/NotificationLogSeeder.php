<?php

use App\Waypoint\Models\NotificationLog;
use App\Waypoint\Repositories\NotificationLogRepository;
use App\Waypoint\Seeder;

class NotificationLogSeeder extends Seeder
{
    /**
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(NotificationLog::class);
        $this->ModelRepositoryObj = App::make(NotificationLogRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}