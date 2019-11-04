<?php

use App\Waypoint\Models\Suite;
use App\Waypoint\Repositories\SuiteRepository;
use App\Waypoint\Seeder;

/**
 * Class SuiteSeeder
 */
class SuiteSeeder extends Seeder
{
    /**
     * SuiteSeeder constructor.
     * @param array $attributes
     * @param int $count
     * @param string $factory_name
     * @throws \Exception
     */
    public function __construct($attributes = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($attributes, $count, $factory_name);
        $this->setResultingClass(Suite::class);
        $this->ModelRepositoryObj = App::make(SuiteRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        $SuiteObjArr = parent::run();
        foreach ($SuiteObjArr as $SuiteObj)
        {
            $LeaseSeederObj = new LeaseSeeder(
                [
                    'client_id'   => $SuiteObj->property->client_id,
                    'property_id' => $SuiteObj->property_id,
                    'suite_id'    => $SuiteObj->id,
                ],
                5,
                self::PHPUNIT_FACTORY_NAME
            );
            $LeaseSeederObj->run();
        }
        return $SuiteObjArr;
    }
}