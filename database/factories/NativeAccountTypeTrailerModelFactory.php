<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\NativeAccountType;
use App\Waypoint\Models\NativeAccountTypeTrailer;
use App\Waypoint\Models\NativeCoa;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    NativeAccountTypeTrailer::class,
    function ()
    {
        return [
            'property_id'                   => null,
            'actual_coefficient'            => 1,
            'budgeted_coefficient'          => 1,
            'advanced_variance_coefficient' => 1,
        ];
    }
);

$factory->defineAs(
    NativeAccountTypeTrailer::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        if (isset($seeder_provided_attributes_arr['native_coa_id']))
        {
            $NativeCoaObj = NativeCoa::find($seeder_provided_attributes_arr['native_coa_id']);
        }
        else
        {
            $NativeCoaObj = $ClientObj->nativeCoas->random();
        }
        if (isset($seeder_provided_attributes_arr['native_account_type_id']))
        {
            $NativeAccountTypeObj = NativeAccountType::find($seeder_provided_attributes_arr['native_account_type_id']);
        }
        else
        {
            $NativeAccountTypeObj = $ClientObj->nativeAccountTypes->random();
        }

        return array_merge(
            $factory->raw(NativeAccountTypeTrailer::class),
            [
                'native_coa_id'          => $NativeCoaObj->id,
                'native_account_type_id' => $NativeAccountTypeObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);