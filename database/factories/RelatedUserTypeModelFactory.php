<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\Opportunity;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\RelatedUserType;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    RelatedUserType::class,
    function ()
    {
        return [
            'name'                   => Seeder::getFakerObj()->jobTitle,
            'description'            => Seeder::getFakerObj()->words(15, true),
            'related_object_type'    => Seeder::getFakerObj()->randomElement([Property::class, Opportunity::class]),
            'related_object_subtype' => Seeder::getFakerObj()->words(4, true),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    RelatedUserType::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        return array_merge(
            $factory->raw(RelatedUserType::class),
            [
                'client_id' => $ClientObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);