<?php

use App\Waypoint\Models\CalculatedFieldEquationProperty;
use App\Waypoint\Repositories\CalculatedFieldEquationPropertyRepository;
use App\Waypoint\Seeder;

/**
 * Class AccessListSeeder
 */
class CalculatedFieldEquationPropertySeeder extends Seeder
{
    /**
     * CalculatedFieldEquationPropertySeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(CalculatedFieldEquationProperty::class);
        $this->ModelRepositoryObj = App::make(CalculatedFieldEquationPropertyRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}