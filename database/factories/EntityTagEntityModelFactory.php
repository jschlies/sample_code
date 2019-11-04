<?php

use App\Waypoint\Models\EntityTagEntity;
use App\Waypoint\Seeder;
use App\Waypoint\Models\EntityTag;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    EntityTagEntity::class,
    function ()
    {
        return [
            'entity_id' => Seeder::getFakerObj()->randomNumber(9),
            'data'      => json_encode(
                [
                    'color'       => Seeder::getFakerObj()->colorName,
                    'special_day' => Seeder::getFakerObj()->dateTimeBetween($startDate = '-30 months', $endDate = '+30 months')->format('Y-m-d H:i:s'),
                ]
            ),
        ];
    }
);

$factory->defineAs(
    EntityTagEntity::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        $all = [];
        foreach (EntityTag::all() as $EntityTagObj)
        {
            $all[] = $EntityTagObj;
        }
        $EntityTagObj = Seeder::getFakerObj()->randomElement($all);

        $entity_model_seeder = $EntityTagObj->getShortEntityModel() . 'Seeder';
        $SomeSeederObj       = new $entity_model_seeder([], 1, Seeder::PHPUNIT_FACTORY_NAME);
        $EntityObj           = $SomeSeederObj->run()->first();

        return array_merge(
            $factory->raw(EntityTagEntity::class),
            [
                'entity_id'     => $EntityObj->id,
                'entity_tag_id' => $EntityTagObj->id,
                'user_id'       => Auth::getUser()->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);