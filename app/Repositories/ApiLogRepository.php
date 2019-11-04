<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\ApiLog;
use App\Waypoint\Repository as BaseRepository;

/**
 * Class ApiLogRepository
 * @package App\Waypoint\Repositories
 */
class ApiLogRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return ApiLog::class;
    }

    /**
     * Save a new $ApiLogObj in repository
     *
     * @param array $attributes
     * @return ApiLog
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function create(array $attributes)
    {
        $ApiLogObj = parent::create($attributes);

        if ($ApiLogObj instanceof Model && in_array('ApiLog', RepositoryEventBase::getEnabledModelRepositoryEvents()))
        {
            if ( ! $this->isSuppressEvents())
            {
                /** @noinspection PhpUndefinedClassInspection */
                event(new ApiLogCreatedEvent($ApiLogObj));
            }
        }

        return $ApiLogObj;
    }

    /**
     * Update a $ApiLogObj in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return ApiLog
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $ApiLogObj = parent::update($attributes, $id);

        if ($ApiLogObj instanceof Model && in_array('ApiKey', RepositoryEventBase::getEnabledModelRepositoryEvents()))
        {
            if ( ! $this->isSuppressEvents())
            {
                /** @noinspection PhpUndefinedClassInspection */
                event(new ApiLogUpdatedEvent($ApiLogObj));
            }
        }

        return $ApiLogObj;
    }

    /**
     * Delete a entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function delete($id)
    {
        $ApiLogObj = $this->find($id);
        $result    = parent::delete($id);

        if ($ApiLogObj instanceof Model && in_array('ApiKey', RepositoryEventBase::getEnabledModelRepositoryEvents()))
        {
            if ( ! $this->isSuppressEvents())
            {
                /** @noinspection PhpUndefinedClassInspection */
                event(new ApiLogDeletedEvent($ApiLogObj));
            }
        }

        return $result;
    }
}
