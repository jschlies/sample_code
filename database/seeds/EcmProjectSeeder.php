<?php

use App\Waypoint\Models\EcmProject;
use App\Waypoint\Repositories\EcmProjectRepository;
use App\Waypoint\Seeder;

/**
 * Class EcmProjectSeeder
 */
class EcmProjectSeeder extends Seeder
{
    /**
     * EcmProjectSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     * @throws \Exception
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(EcmProject::class);
        $this->ModelRepositoryObj = App::make(EcmProjectRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}