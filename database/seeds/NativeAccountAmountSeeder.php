<?php

use App\Waypoint\Models\NativeAccountAmount;
use App\Waypoint\Repositories\NativeAccountAmountRepository;
use App\Waypoint\Seeder;

/**
 * Class NativeAccountAmountSeeder
 */
class NativeAccountAmountSeeder extends Seeder
{
    /**
     * NativeAccountAmountSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     * @throws \Exception
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(NativeAccountAmount::class);
        $this->ModelRepositoryObj = App::make(NativeAccountAmountRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}