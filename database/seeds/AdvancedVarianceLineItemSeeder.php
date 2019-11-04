<?php

use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Repositories\AdvancedVarianceLineItemRepository;
use App\Waypoint\Seeder;

/**
 * Class AdvancedVarianceLineItemSeeder
 */
class AdvancedVarianceLineItemSeeder extends Seeder
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
        $this->setResultingClass(AdvancedVarianceLineItem::class);
        $this->ModelRepositoryObj = App::make(AdvancedVarianceLineItemRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}