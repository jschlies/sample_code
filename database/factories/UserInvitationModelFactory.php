<?php

use App\Waypoint\Models\Client;
use App\Waypoint\Models\User;
use App\Waypoint\Models\UserInvitation;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    UserInvitation::class,
    function ()
    {
        return [
            'invitation_status'     => Seeder::getFakerObj()->randomElement(UserInvitation::$invitation_status_value_arr),
            'one_time_token_expiry' => Seeder::getFakerObj()->dateTimeBetween($startDate = '+1 days', $endDate = '+30 days')->format('Y-m-d H:i:s'),
            'one_time_token'        => Seeder::getFakerObj()->shuffleString('abcdefghijk01234567890'),
            'inviter_ip'            => Seeder::getFakerObj()->ipv4,
            'acceptance_time'       => Seeder::getFakerObj()->dateTimeBetween($startDate = '-30 days', $endDate = '-1 days')->format('Y-m-d H:i:s'),
            'acceptance_ip'         => Seeder::getFakerObj()->ipv4,
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    UserInvitation::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        if (isset($seeder_provided_attributes_arr['invitee_user_id']))
        {
            $InviteeUserObj = User::find($seeder_provided_attributes_arr['invitee_user_id']);
        }
        else
        {
            $InviteeUserObj = $ClientObj->users->random();
        }
        if (isset($seeder_provided_attributes_arr['inviter_user_id']))
        {
            $InviterUserObj = User::find($seeder_provided_attributes_arr['inviter_user_id']);
        }
        else
        {
            do
            {
                $InviterUserObj = $ClientObj->users->random();
            } while ($InviterUserObj->id == $InviteeUserObj->id);
        }
        return array_merge(
            $factory->raw(UserInvitation::class),
            [
                'invitee_user_id' => $InviteeUserObj->id,
                'inviter_user_id' => $InviterUserObj->id,
            ],
            $seeder_provided_attributes_arr
        );
    }
);