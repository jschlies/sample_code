<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\ClientCategory;

/**
 * Class ClientCategoryRepository
 * @package App\Waypoint\Repositories
 */
class ClientCategoryRepository extends ClientCategoryRepositoryBase
{
    /**
     * @return string
     */
    public function model()
    {
        return ClientCategory::class;
    }
}
