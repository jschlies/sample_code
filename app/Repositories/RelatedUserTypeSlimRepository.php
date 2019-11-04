<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\RelatedUserTypeSlim;

/**
 * Slim facade of the RelatedUserType model
 * Class RelatedUserTypeSlimRepository
 * @package App\Waypoint\Repositories
 */
class RelatedUserTypeSlimRepository extends RelatedUserTypeRepositoryBase
{

    /**
     * @return string|void
     */
    public function model()
    {
        return RelatedUserTypeSlim::class;
    }
}