<?php

use App\Waypoint\Models\NativeAccount;
use App\Waypoint\Repositories\NativeAccountRepository;
use App\Waypoint\Seeder;

/**
 * Class NativeCoaSeeder
 */
class NativeAccountSeeder extends Seeder
{
    /**
     * NativeCoaSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     * @throws \Exception
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(NativeAccount::class);
        $this->ModelRepositoryObj = App::make(NativeAccountRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}