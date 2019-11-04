<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AuthenticatingEntity;

/**
 * Class AuthenticatingEntityRepository
 * @package App\Waypoint\Repositories
 */
class AuthenticatingEntityRepository extends AuthenticatingEntityRepositoryBase
{
    /**
     * @return string
     */
    public function model()
    {
        return AuthenticatingEntity::class;
    }

    /**
     * @param array $attributes
     * @return AuthenticatingEntity
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function create(array $attributes)
    {
        $attributes['is_default'] = false;
        if (
            ! isset($attributes['email_regex']) ||
            ! $attributes['email_regex'] ||
            ! isRegularExpression($attributes['email_regex'])
        )
        {
            throw new GeneralException('invalid email_regex');
        }

        return parent::create($attributes);
    }

}
