<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\ClientFull;

/**
 * Class ClientRepository
 * @package App\Waypoint\Repositories
 */
class ClientFullRepository extends ClientRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return ClientFull::class;
    }
}
