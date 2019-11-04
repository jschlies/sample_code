<?php

use App\Waypoint\Models\AdvancedVarianceApproval;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\User;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    AdvancedVarianceApproval::class,
    function ()
    {
        return [
            "approval_date" => Seeder::getFakerObj()->dateTimeBetween($startDate = '-30 months', $endDate = '+30 months')->format('Y-m-d H:i:s'),
        ];

    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    AdvancedVarianceApproval::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        /** @var Property $PropertyObj */
        $PropertyObj = $ClientObj->properties->filter(function ($Property) { return $Property->advancedVariances->count(); });

        if (isset($seeder_provided_attributes_arr['advanced_variance_id']))
        {
            $AdvancedVarianceObj = AdvancedVariance::find($seeder_provided_attributes_arr['advanced_variance_id']);
        }
        elseif ( ! $AdvancedVarianceObj = $PropertyObj->advancedVariances->random())
        {
            $AdvancedVarianceSeederObj = new AdvancedVarianceSeeder([], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $AdvancedVarianceObj       = $AdvancedVarianceSeederObj->run()->first();
        }

        if (isset($seeder_provided_attributes_arr['approving_user_id']))
        {
            $ApprovingUserObj = User::find($seeder_provided_attributes_arr['approving_user_id']);
        }
        elseif ( ! $ApprovingUserObj = $ClientObj->users->random())
        {
            /** @var UserSeeder $UserSeederObj */
            $UserSeederObj    = new UserSeeder([], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $ApprovingUserObj = $UserSeederObj->run()->first();
        }
        return array_merge(
            $factory->raw(AdvancedVarianceApproval::class),
            [
                'advanced_variance_id' => $AdvancedVarianceObj->id,
                'approving_user_id'    => $ApprovingUserObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);