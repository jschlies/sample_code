<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\Favorite;
use App\Waypoint\Models\FavoriteGroup;
use App\Waypoint\Models\User;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    Favorite::class,
    function ()
    {

        return [
            'data' => json_encode(
                [
                    'color'       => Seeder::getFakerObj()->colorName,
                    'special_day' => Seeder::getFakerObj()->dateTimeBetween($startDate = '-30 months', $endDate = '+30 months')->format('Y-m-d H:i:s'),
                ]
            ),
        ];
    }
);

$factory->defineAs(
    Favorite::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        $FavoriteGroupObj = Seeder::getFakerObj()->randomElement(FavoriteGroup::where('name', '=', Favorite::class)->get()->all());
        $seeder_to_call   = $FavoriteGroupObj->getShortEntityModel() . 'Seeder';
        if ($seeder_to_call == 'ClientSeeder')
        {
            $ObjectToFavorite = TestCase::getUnitTestClient();
        }
        elseif ($seeder_to_call == 'PropertySeeder')
        {
            $ObjectToFavorite = $ClientObj->properties->random();
        }
        else
        {
            /** @var App\Waypoint\Seeder $ObjectSeederObj */
            $ObjectSeederObj  = new $seeder_to_call(['client_id' => $ClientObj->id], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $ObjectToFavorite = $ObjectSeederObj->run()->first();
        }
        if (isset($factory->getProvidedValuesArr()['user_id']))
        {
            $UserObj = User::find($factory->getProvidedValuesArr()['user_id']);
        }
        else
        {
            $UserObj = $ClientObj->users->random();
        }
        return $factory->raw(
            Favorite::class,
            [
                'client_id'     => $ClientObj->id,
                'entity_tag_id' => $FavoriteGroupObj->id,
                'entity_id'     => $ObjectToFavorite->id,
                'user_id'       => $UserObj->id,
            ]
        );
    }
);