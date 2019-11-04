<?php

use App\Waypoint\Models\DownloadHistory;
use App\Waypoint\Repositories\DownloadHistoryRepository;
use App\Waypoint\Seeder;

/**
 * Class DownloadHistorySeeder
 */
class DownloadHistorySeeder extends Seeder
{
    /**
     * DownloadHistorySeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     * @throws \Exception
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(DownloadHistory::class);
        $this->ModelRepositoryObj = App::make(DownloadHistoryRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}