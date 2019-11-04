<?php

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Opportunity;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\RelatedUser;
use App\Waypoint\Models\RelatedUserType;
use App\Waypoint\Models\User;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
/** @noinspection PhpUnusedParameterInspection */
$factory->define(
    RelatedUser::class,
    function ()
    {
        return [
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    RelatedUser::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        do
        {
            if (isset($seeder_provided_attributes_arr['user_id']))
            {
                $UserObj = User::find($seeder_provided_attributes_arr['user_id']);
            }
            else
            {
                $UserObj = $ClientObj->users->random();
            }
        } while ($UserObj->relatedUsers->count());

        $RelatedObj = null;
        do
        {
            if (isset($seeder_provided_attributes_arr['related_user_type_id']))
            {
                $RelatedUserTypeObj = RelatedUserType::find($seeder_provided_attributes_arr['related_user_type_id']);
            }
            elseif ( ! $RelatedUserTypeObj = $ClientObj->relatedUserTypes->random())
            {
                $RelatedUserTypeSeederObj = new RelatedUserTypeSeeder(['client_id_id' => $ClientObj->id], 1, Seeder::PHPUNIT_FACTORY_NAME);
                $RelatedUserTypeObj       = $RelatedUserTypeSeederObj->run()->first();
            }

            if (isset($seeder_provided_attributes_arr['related_object_id']))
            {
                if ($RelatedUserTypeObj->related_object_type == Property::class)
                {
                    $RelatedObj = Property::find($seeder_provided_attributes_arr['related_object_id']);
                }
                elseif ($RelatedUserTypeObj->related_object_type == Opportunity::class)
                {
                    $RelatedObj = Opportunity::find($seeder_provided_attributes_arr['related_object_id']);
                }
                else
                {
                    throw new GeneralException('invalid related_object_type');
                }
            }
            else
            {
                if ($RelatedUserTypeObj->related_object_type == Property::class)
                {
                    $RelatedObj = $ClientObj->properties->random();
                }
                elseif ($RelatedUserTypeObj->related_object_type == Opportunity::class)
                {
                    $RelatedObj = $ClientObj->getOpportunities()->first();
                }
            }

        } while ( ! $RelatedObj);

        return array_merge(
            $factory->raw(RelatedUser::class),
            [
                'user_id'              => $UserObj->id,
                'related_object_id'    => $RelatedObj->id,
                'related_user_type_id' => $RelatedUserTypeObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);