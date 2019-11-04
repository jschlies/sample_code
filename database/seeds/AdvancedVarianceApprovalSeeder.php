<?php

use App\Waypoint\Models\AdvancedVarianceApproval;
use App\Waypoint\Repositories\AdvancedVarianceApprovalRepository;
use App\Waypoint\Seeder;

/**
 * Class AdvancedVarianceApprovalSeeder
 */
class AdvancedVarianceApprovalSeeder extends Seeder
{
    /**
     * AdvancedVarianceApprovalSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     * @throws \Exception
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(AdvancedVarianceApproval::class);
        $this->ModelRepositoryObj = App::make(AdvancedVarianceApprovalRepository::class)->setSuppressEvents(true);;
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}