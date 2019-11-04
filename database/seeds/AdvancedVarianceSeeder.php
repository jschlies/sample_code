<?php

use App\Waypoint\Jobs\AdvancedVarianceLineItemRefreshJob;
use App\Waypoint\Tests\Mocks\NativeCoaLedgerMockRepository;
use App\Waypoint\Collection;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Repositories\AdvancedVarianceRepository;
use App\Waypoint\Seeder;
use Illuminate\Database\Eloquent\FactoryBuilder;

/**
 * Class AdvancedVarianceSeeder
 */
class AdvancedVarianceSeeder extends Seeder
{
    /**
     * AdvancedVarianceSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     * @throws \Exception
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(AdvancedVariance::class);
        /** @noinspection PhpParamsInspection */
        AdvancedVarianceRepository::setNativeCoaLedgerRepositoryObj(App::make(NativeCoaLedgerMockRepository::class));
        $this->ModelRepositoryObj = App::make(AdvancedVarianceRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        /** @var FactoryBuilder $FactoryObj */
        $FactoryObj = $this->factory(
            $this->getResultingClass(),
            $this->getFactoryName(),
            $this->count
        );
        $return_me  = new Collection();
        foreach ($FactoryObj->raw($this->getAttributes()) as $raw_item)
        {
            /**
             * nasty constraint issue
             */
            while (AdvancedVariance::where('property_id', $raw_item['property_id'])
                                   ->where('as_of_month', $raw_item['as_of_month'])
                                   ->where('as_of_year', $raw_item['as_of_year'])
                                   ->get()->count()
            )
            {
                if ($raw_item['period_type'] == AdvancedVariance::PERIOD_TYPE_QUARTERLY)
                {
                    $raw_item['as_of_month'] = Seeder::getFakerObj()->randomElement([3, 6, 9, 12]);
                    $raw_item['as_of_year']  = Seeder::getFakerObj()->randomElement([2014, 2015, 2016, 2017]);
                }
                else
                {
                    $raw_item['as_of_month'] = Seeder::getFakerObj()->randomElement([1, 2, 4, 5, 7, 8, 10, 11]);
                    $return_me['as_of_year'] = Seeder::getFakerObj()->randomElement([2014, 2015, 2016, 2017]);
                }
            }
            /** @noinspection PhpParamsInspection */
            AdvancedVarianceRepository::setNativeCoaLedgerRepositoryObj(App::make(NativeCoaLedgerMockRepository::class));
            /** @var AdvancedVariance $AdvancedVarianceObj */
            $AdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->create($raw_item);
            $return_me[]         = $AdvancedVarianceObj;

            $AdvancedVarianceLineItemObj = $AdvancedVarianceObj->advancedVarianceLineItems->first();

            $AdvancedVarianceLineItemRefreshJobObj = new AdvancedVarianceLineItemRefreshJob($AdvancedVarianceLineItemObj->toArray());
            $AdvancedVarianceLineItemRefreshJobObj->handle();
        }
        return $return_me;
    }
}