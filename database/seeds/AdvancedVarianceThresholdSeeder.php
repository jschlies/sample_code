<?php

use App\Waypoint\Models\AdvancedVarianceThreshold;
use App\Waypoint\Repositories\AdvancedVarianceThresholdRepository;
use App\Waypoint\Seeder;

/**
 * Class AdvancedVarianceApprovalSeeder
 */
class AdvancedVarianceThresholdSeeder extends Seeder
{
    /**
     * AdvancedVarianceThresholdSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     * @throws \Exception
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(AdvancedVarianceThreshold::class);
        $this->ModelRepositoryObj = App::make(AdvancedVarianceThresholdRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}