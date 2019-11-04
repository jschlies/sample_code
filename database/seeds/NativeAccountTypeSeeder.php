<?php

use App\Waypoint\Models\NativeAccountType;
use App\Waypoint\Repositories\NativeAccountTypeRepository;
use App\Waypoint\Seeder;

/**
 * Class NativeAccountTypeSeeder
 */
class NativeAccountTypeSeeder extends Seeder
{
    /**
     * NativeAccountTypeSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     * @throws \Exception
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(NativeAccountType::class);
        $this->ModelRepositoryObj = App::make(NativeAccountTypeRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}