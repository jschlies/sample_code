<?php

use App\Waypoint\Models\ClientCategory;
use App\Waypoint\Repositories\ClientCategoryRepository;
use App\Waypoint\Seeder;

/**
 * Class ClientCategorySeeder
 */
class ClientCategorySeeder extends Seeder
{
    /**
     * ClientCategorySeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     * @throws \Exception
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(ClientCategory::class);
        $this->ModelRepositoryObj = App::make(ClientCategoryRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}