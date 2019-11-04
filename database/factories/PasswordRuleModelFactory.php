<?php

use App\Waypoint\Models\PasswordRule;
use App\Waypoint\Seeder;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    PasswordRule::class,
    function ()
    {
        return [
            'points'             => rand(1, 100),
            'description'        => Seeder::getFakeDescription(),
            'password_rule_type' => Seeder::getFakerObj()->randomElement(PasswordRule::$passwword_rule_type_arr),
            'regular_expression' => Seeder::getFakerObj()->word,
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    PasswordRule::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        return array_merge(
            $factory->raw(PasswordRule::class),
            [],
            $seeder_provided_attributes_arr
        );
    }
);