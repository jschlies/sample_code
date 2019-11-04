<?php

use App\Waypoint\Models\RelatedUserType;
use App\Waypoint\Repositories\RelatedUserTypeRepository;
use App\Waypoint\Seeder;

/**
 * Class RelatedUserTypeSeeder
 */
class RelatedUserTypeSeeder extends Seeder
{
    /**
     * RelatedUserTypeSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(RelatedUserType::class);
        $this->ModelRepositoryObj = App::make(RelatedUserTypeRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}