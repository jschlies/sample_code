<?php

use App\Waypoint\Models\CalculatedFieldEquation;
use App\Waypoint\Repositories\CalculatedFieldEquationRepository;
use App\Waypoint\Seeder;

/**
 * Class AccessListSeeder
 */
class CalculatedFieldEquationSeeder extends Seeder
{
    /**
     * CalculatedFieldEquationSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(CalculatedFieldEquation::class);
        $this->ModelRepositoryObj = App::make(CalculatedFieldEquationRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}