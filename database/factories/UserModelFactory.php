<?php

use App\Waypoint\Models\Client;
use \App\Waypoint\Models\User;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    User::class,
    function ()
    {
        return [
            'firstname'                   => Seeder::getFakerObj()->firstName . ' ' . Seeder::getFakerObj()->firstName,
            'lastname'                    => Seeder::getFakerObj()->firstName . ' ' . Seeder::getFakerObj()->lastName,
            'email'                       => TestCase::getFakeEmailAddress(),
            'user_name'                   => Seeder::getFakerObj()->shuffleString('abcdefghijk01234567890'),
            'active_status'               => User::ACTIVE_STATUS_ACTIVE,
            'user_invitation_status'      => Seeder::getFakerObj()->randomElement(User::$user_invitation_status_values),
            'user_invitation_status_date' => Seeder::getFakerObj()->dateTimeBetween($startDate = '-30 months', $endDate = '+30 months')->format('Y-m-d H:i:s'),
            'active_status_date'          => Seeder::getFakerObj()->dateTimeBetween($startDate = '-30 months', $endDate = '+30 months')->format('Y-m-d H:i:s'),
            'salutation'                  => Seeder::getFakerObj()->randomElement(['Mr', 'Mrs', 'Ms', 'Sir', 'Madam', 'Sire', 'Col', 'Gen']),
            'suffix'                      => Seeder::getFakerObj()->randomElement(['Sr', 'Jr', 'MD', 'III', 'PhD', 'Ret']),
            'work_number'                 => Seeder::getFakerObj()->phoneNumber,
            'mobile_number'               => Seeder::getFakerObj()->phoneNumber,
            'company'                     => Seeder::getFakerObj()->company,
            'location'                    => Seeder::getFakerObj()->locale,
            'job_title'                   => Seeder::getFakerObj()->jobTitle,
            'config_json'                 => json_encode(Seeder::getFakeUserConfigJson()),
            'image_json'                  => json_encode(new ArrayObject()),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    User::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        return array_merge(
            $factory->raw(User::class),
            [
                'client_id' => $ClientObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);