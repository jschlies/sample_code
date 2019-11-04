<?php

use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    AdvancedVariance::class,
    function ()
    {
        $period_type = Seeder::getFakerObj()->randomElement(AdvancedVariance::$period_type_arr);
        if ($period_type == AdvancedVariance::PERIOD_TYPE_QUARTERLY)
        {
            $as_of_month = Seeder::getFakerObj()->randomElement([3, 6, 9, 12]);
        }
        else
        {
            $as_of_month = Seeder::getFakerObj()->randomElement([1, 2, 4, 5, 7, 8, 10, 11]);
        }

        return [
            'advanced_variance_status'     => AdvancedVariance::ACTIVE_STATUS_UNLOCKED,
            "advanced_variance_start_date" => Seeder::getFakerObj()->dateTimeBetween($startDate = '-30 months', $endDate = '+30 months')->format('Y-m-d H:i:s'),
            'period_type'                  => $period_type,
            "locked_date"                  => null,
            "as_of_month"                  => $as_of_month,
            "as_of_year"                   => Seeder::getFakerObj()->randomElement([2014, 2015, 2016, 2017]),
            "target_locked_date"           => Seeder::getFakerObj()->dateTimeBetween($startDate = '+30 months', $endDate = '+35 months')->format('Y-m-d H:i:s'),
            "num_flagged_via_policy"       => 0,
            "num_flagged_manually"         => 0,
            "num_flagged"                  => 0,
            "num_explained"                => 0,
            "num_line_items"               => 0,
            "num_resolved"                 => 0,
            'threshold_mode'               => Seeder::getFakerObj()->randomElement(AdvancedVariance::$threshold_mode_arr),
            'trigger_mode'                 => Seeder::getFakerObj()->randomElement(AdvancedVariance::$trigger_mode_value_arr),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    AdvancedVariance::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        if (isset($seeder_provided_attributes_arr['property_id']))
        {
            $PropertyObj = Property::find($seeder_provided_attributes_arr['property_id']);
        }
        elseif ( ! $PropertyObj = $ClientObj->properties->random())
        {
            $PropertySeederObj = new PropertySeeder([], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $PropertyObj       = $PropertySeederObj->run()->first();
        }

        $return_me = array_merge(
            $factory->raw(AdvancedVariance::class),
            [
                'property_id' => $PropertyObj->id,
            ],
            $seeder_provided_attributes_arr
        );
        /**
         * nasty constraint issue
         */
        do
        {
            if ($return_me['period_type'] == AdvancedVariance::PERIOD_TYPE_QUARTERLY)
            {
                $return_me['as_of_month'] = Seeder::getFakerObj()->randomElement([3, 6, 9, 12]);
                $return_me['as_of_year']  = Seeder::getFakerObj()->randomElement([2014, 2015, 2016, 2017]);
            }
            else
            {
                $return_me['as_of_month'] = Seeder::getFakerObj()->randomElement([1, 2, 4, 5, 7, 8, 10, 11]);
                $return_me['as_of_year']  = Seeder::getFakerObj()->randomElement([2014, 2015, 2016, 2017]);
            }
        } while (
            AdvancedVariance::where('property_id', $return_me['property_id'])
                            ->where('as_of_month', $return_me['as_of_month'])
                            ->where('as_of_year', $return_me['as_of_year'])
                            ->get()->count()
        );
        /**
         * this is correct. $factory->raw(AdvancedVariance::class) is called above
         */
        return $return_me;
    }
);