<?php

use App\Waypoint\Models\ApiKey;
use App\Waypoint\Models\ApiLog;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\User;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    ApiLog::class,
    function ()
    {
        return [
            'route'      => Seeder::getFakerObj()->sentences(2, true),
            'method'     => 'index',
            'params'     => Seeder::getFakerObj()->sentences(2, true),
            'ip_address' => Seeder::getFakerObj()->ipv4,
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    ApiLog::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        if (isset($seeder_provided_attributes_arr['api_key_id']))
        {
            $ApiKeyObj = ApiKey::find($factory->getProvidedValuesArr()['api_key_id']);
        }
        elseif ( ! $ApiKeyObj = $ClientObj->users->filter(function (User $UserObj) { return $UserObj->apiKey; })->first()->apiKey)
        {
            $ApiKeySeederObj = new ApiKeySeeder([], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $ApiKeyObj       = $ApiKeySeederObj->run()->first();
        }
        return array_merge(
            $factory->raw(ApiLog::class),
            [
                'api_key_id' => $ApiKeyObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);