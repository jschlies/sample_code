<?php

use App\Waypoint\Models\PermissionRole;
use App\Waypoint\Seeder;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    PermissionRole::class,
    function ()
    {
        return [];
    }
);

$factory->defineAs(
    PermissionRole::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        $PermissionSeederObj = new PermissionSeeder([], 1, Seeder::PHPUNIT_FACTORY_NAME);
        $PermissionObj       = $PermissionSeederObj->run()->first();
        $RoleSeederObj       = new RoleSeeder([], 1, Seeder::PHPUNIT_FACTORY_NAME);
        $RoleObj             = $RoleSeederObj->run()->first();
        return array_merge(
            $factory->raw(PermissionRole::class),
            [
                'permission_id' => $PermissionObj->id,
                'role_id'       => $RoleObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);