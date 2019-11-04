<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Models\Heartbeat;
use App\Waypoint\Models\User;
use DB;

/**
 * Class UserRepository
 * @package App\Waypoint\Repositories
 */
class HeartbeatRepository extends UserRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return Heartbeat::class;
    }

    /**
     * @param User $CurrentLoggedInUserObj
     * @return Collection
     */
    public function findHeartbeats(User $CurrentLoggedInUserObj)
    {
        /**
         * ordinarily, we'd use this
         * ->findWhere() but this gets hit alot so.........
         * @todo we should thing about index's
         */
        $user_id_arr = DB::select(
            DB::raw(
                '
                    SELECT * FROM users
                        WHERE 
                            email = :EMAIL  AND
                            (
                                active_status = :ACTIVE_STATUS_ACTIVE OR
                                (
                                    active_status = :ACTIVE_STATUS_INACTIVE AND
                                    user_invitation_status = :USER_INVITATION_STATUS_PENDING
                                )
                            )
                            ORDER BY users.client_id;
                '
            ),
            [
                'EMAIL'                          => $CurrentLoggedInUserObj->email,
                'ACTIVE_STATUS_ACTIVE'           => User::ACTIVE_STATUS_ACTIVE,
                'ACTIVE_STATUS_INACTIVE'         => User::ACTIVE_STATUS_INACTIVE,
                'USER_INVITATION_STATUS_PENDING' => User::USER_INVITATION_STATUS_PENDING,
            ]
        );

        $user_id_arr = array_map(
            function ($item)
            {
                return $item->id;
            },
            $user_id_arr);

        return $this->findWherein('id', $user_id_arr)->sortBy('id');
    }
}