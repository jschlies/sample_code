<?php

use App\Waypoint\Models\RelatedUser;
use App\Waypoint\Repositories\RelatedUserRepository;
use App\Waypoint\Seeder;

/**
 * Class RelatedUserSeeder
 */
class RelatedUserSeeder extends Seeder
{
    /**
     * RelatedUserSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(RelatedUser::class);
        $this->ModelRepositoryObj = App::make(RelatedUserRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}