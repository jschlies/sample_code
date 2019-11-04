<?php

use App\Waypoint\Repositories\EntityTagEntityRepository;
use App\Waypoint\Seeder;
use App\Waypoint\Models\EntityTagEntity;

/**
 * Class EntityTagEntitySeeder
 */
class EntityTagEntitySeeder extends Seeder
{
    /**
     * EntityTagEntitySeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(EntityTagEntity::class);
        $this->ModelRepositoryObj = App::make(EntityTagEntityRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}