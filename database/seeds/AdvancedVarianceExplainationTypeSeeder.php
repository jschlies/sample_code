<?php

use App\Waypoint\Models\AdvancedVarianceExplanationType;
use App\Waypoint\Repositories\AdvancedVarianceExplanationTypeRepository;
use App\Waypoint\Seeder;

/**
 * Class AdvancedVarianceExplanationTypeSeeder
 */
class AdvancedVarianceExplanationTypeSeeder extends Seeder
{
    /**
     * AdvancedVarianceLineItemSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     * @throws \Exception
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(AdvancedVarianceExplanationType::class);
        $this->ModelRepositoryObj = App::make(AdvancedVarianceExplanationTypeRepository::class)->setSuppressEvents(true);;
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}