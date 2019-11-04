<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\EntityTag;
use App\Waypoint\Models\FavoriteGroup;

/**
 * Class FavoriteRepository
 * @package App\Waypoint\Repositories
 */
class FavoriteGroupRepository extends EntityTagRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return FavoriteGroup::class;
    }

    /**
     * @param array $attributes
     * @return EntityTag
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function create(array $attributes)
    {
        return parent::create($attributes);
    }
}
