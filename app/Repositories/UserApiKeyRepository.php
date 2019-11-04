<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\UserApiKey;

/**
 * Class UserRepository
 * @package App\Waypoint\Repositories
 */
class UserApiKeyRepository extends UserRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return UserApiKey::class;
    }
}