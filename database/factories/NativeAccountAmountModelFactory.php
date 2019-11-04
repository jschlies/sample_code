<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\NativeAccountAmount;
use App\Waypoint\Models\NativeAccount;
use App\Waypoint\Models\Property;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;
use Carbon\Carbon;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    NativeAccountAmount::class,
    function ()
    {
        $MonthYearTimestampObj = Seeder::getFakerObj()->dateTimeBetween($startDate = '-60 months', $endDate = '-30 months');
        return [
            'month_year_timestamp' => $MonthYearTimestampObj->format('Y-m-d H:i:s'),
            'month'                => $MonthYearTimestampObj->format('m'),
            'year'                 => $MonthYearTimestampObj->format('Y'),
            'actual'               => mt_rand(1000, 6000),
            'budget'               => mt_rand(5000, 100000),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    NativeAccountAmount::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function ($seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        if (isset($factory->getProvidedValuesArr()['property_id']))
        {
            $PropertyObj = Property::find($factory->getProvidedValuesArr()['property_id']);
        }
        elseif ( ! $PropertyObj = $ClientObj->properties->random())
        {
            $PropertySeederObj = new PropertySeeder(['client_id' => $ClientObj->id], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $PropertyObj       = $PropertySeederObj->run()->first();
        }

        /** @var NativeAccount $NativeAccountObj */
        if (isset($seeder_provided_attributes_arr['native_account_id']))
        {
            $native_account_id = $seeder_provided_attributes_arr['native_account_id'];
        }
        else
        {
            $NativeAccountSeederObj = new NativeAccountSeeder(
                [
                    'client_id'     => $ClientObj->id,
                    'native_coa_id' => $ClientObj->nativeCoas->first()->id,
                ],
                1,
                Seeder::PHPUNIT_FACTORY_NAME
            );
            $NativeAccountObj       = $NativeAccountSeederObj->run()->first();
            $native_account_id = $NativeAccountObj->id;
        }

        if (
            isset($seeder_provided_attributes_arr['year']) && $seeder_provided_attributes_arr['year'] &&
            isset($seeder_provided_attributes_arr['month']) && $seeder_provided_attributes_arr['month']
        )
        {
            $seeder_provided_attributes_arr['month_year_timestamp'] =
                Carbon::createFromDate(
                    $seeder_provided_attributes_arr['year'],
                    $seeder_provided_attributes_arr['month'],
                    1
                )->startOfDay();
        }

        return array_merge(
            $factory->raw(NativeAccountAmount::class),
            [
                'client_id'         => $ClientObj->id,
                'property_id'       => $PropertyObj->id,
                'native_account_id' => $native_account_id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);