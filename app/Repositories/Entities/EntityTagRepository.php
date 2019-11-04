<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\EntityTag;

/**
 * Class EntityTagRepository
 * @package App\Waypoint\Repositories
 */
class EntityTagRepository extends EntityTagRepositoryBase
{
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
