<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\ClientDetail;

/**
 * Class ClientRepository
 * @package App\Waypoint\Repositories
 */
class ClientDetailRepository extends ClientRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return ClientDetail::class;
    }
}
