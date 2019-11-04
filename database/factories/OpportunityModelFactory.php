<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\ClientCategory;
use App\Waypoint\Models\Opportunity;
use App\Waypoint\Models\Property;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    Opportunity::class,
    function ()
    {
        return [
            'name'                 => Seeder::getFakeName(),
            'description'          => Seeder::getFakeDescription(),
            'opportunity_status'   => Seeder::getFakerObj()->randomElement(Opportunity::$opportunity_status_arr),
            'opportunity_priority' => Seeder::getFakerObj()->randomElement(Opportunity::$opportunity_priority_arr),
            'estimated_incentive'  => mt_rand(0, 1000000000),
            'expense_amount'       => mt_rand(0, 1000000000),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    Opportunity::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        $AssignedToUserObj = $ClientObj->users->random();
        $CreatedByUserObj  = $ClientObj->users->random();
        if (isset($seeder_provided_attributes_arr['property_id']))
        {
            $PropertyObj = Property::find($seeder_provided_attributes_arr['property_id']);
        }
        elseif ( ! $PropertyObj = $ClientObj->properties->random())
        {
            $PropertyObj = $ClientObj->properties->random();
        }

        if (isset($seeder_provided_attributes_arr['client_category_id']))
        {
            $ClientCategoryObj = ClientCategory::find($seeder_provided_attributes_arr['client_category_id']);
        }
        elseif ( ! $ClientCategoryObj = $ClientObj->clientCategories->random())
        {
            $ClientCategoryObj = $ClientObj->clientCategories->random();
        }
        return array_merge(
            $factory->raw(Opportunity::class),
            [
                'assigned_to_user_id' => $AssignedToUserObj->id,
                'property_id'         => $PropertyObj->id,
                'created_by_user_id'  => $CreatedByUserObj->id,
                'client_category_id'  => $ClientCategoryObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);
