<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\NativeAccount;
use App\Waypoint\Models\NativeAccountType;
use App\Waypoint\Models\NativeCoa;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    NativeAccount::class,
    function ()
    {
        return [
            'native_account_name' => Seeder::getFakerObj()->words(4, true),
            'native_account_code' => Seeder::getFakerObj()->randomLetter . Seeder::getFakerObj()->randomLetter . Seeder::getFakerObj()->isbn13,
            'is_category'         => false,
            'is_recoverable'      => Seeder::getFakerObj()->randomElement([true, false]),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    NativeAccount::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        if (isset($seeder_provided_attributes_arr['native_account_type_id']))
        {
            $NativeAccountTypeObj = NativeAccountType::find($seeder_provided_attributes_arr['native_account_type_id']);
        }
        else
        {
            $NativeAccountTypeObj = $ClientObj->nativeAccountTypes->random();
        }
        if (isset($seeder_provided_attributes_arr['native_coa_id']))
        {
            $NativeCoaObj = NativeCoa::find($seeder_provided_attributes_arr['native_coa_id']);
        }
        else
        {
            $NativeCoaObj = $ClientObj->nativeCoas->first();
        }

        /**
         * we process this special because of the many connections, relations and
         * conventions in native accounts.
         * @todo Huh??????
         */
        $native_account_name = Seeder::getLeafBomaReportTemplateAccountGroupObjArr()->first()->report_template_account_group_name;
        Seeder::deleteLeafOfBomaReportTemplateAccountGroupObjArr($native_account_name);

        return array_merge(
            $factory->raw(NativeAccount::class),
            [
                'client_id'              => $NativeCoaObj->client_id,
                'native_coa_id'          => $NativeCoaObj->id,
                'native_account_type_id' => $NativeAccountTypeObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);