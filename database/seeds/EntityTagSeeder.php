<?php

use App\Waypoint\Repositories\EntityTagRepository;
use App\Waypoint\Seeder;
use App\Waypoint\Models\EntityTag;

/**
 * Class EntityTagSeeder
 */
class EntityTagSeeder extends Seeder
{
    /**
     * EntityTagSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(EntityTag::class);
        $this->ModelRepositoryObj = App::make(EntityTagRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}