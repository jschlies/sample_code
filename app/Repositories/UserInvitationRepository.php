<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Models\User;
use App\Waypoint\Models\UserInvitation;
use Carbon\Carbon;

/**
 * Class AccessListPropertyRepository
 * @package App\Waypoint\Repositories
 */
class UserInvitationRepository extends UserInvitationRepositoryBase
{
    public function create(array $attributes)
    {
        $UserInvitationObj = parent::create($attributes);

        $UserRepositoryObj = App::make(UserRepository::class);
        $UserRepositoryObj->update(
            [
                'user_invitation_status'      => User::USER_INVITATION_STATUS_PENDING,
                'user_invitation_status_date' => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            $attributes['invitee_user_id']
        );
        return $UserInvitationObj;
    }

    /**
     * @return string
     */
    public
    function model()
    {
        return UserInvitation::class;
    }
}

