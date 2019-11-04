<?php

namespace App\Waypoint\Tests\Generated;

/**
 * README - README - README - README - README
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 * See readme.md
 * This file is generated - edits to this file will be lost.
 * Please read and understand the info on generating models/controllers/requests/test in the readme.md
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 */

use App\Waypoint\Seeder;
use App\Waypoint\Models\UserInvitation;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeUserInvitationTrait
{
    /**
     * Create fake instance of UserInvitation and save it in database
     *
     * @param array $user_invitations_arr
     * @return UserInvitation
     */
    public function makeUserInvitation($user_invitations_arr = [])
    {
        $theme = $this->fakeUserInvitationData($user_invitations_arr);
        return $this->UserInvitationRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of UserInvitation
     *
     * @param array $user_invitations_arr
     * @return UserInvitation
     */
    public function fakeUserInvitation($user_invitations_arr = [])
    {
        return new UserInvitation($this->fakeUserInvitationData($user_invitations_arr));
    }

    /**
     * Get fake data of UserInvitation
     *
     * @param array $user_invitations_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeUserInvitationData($user_invitations_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($user_invitations_arr);
        return $factory->raw(UserInvitation::class, $user_invitations_arr, $factory_name);
    }
}