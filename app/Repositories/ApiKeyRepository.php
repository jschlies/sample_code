<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\ApiKey;
use App\Waypoint\Repository as BaseRepository;

/**
 *
 * NOTE NOTE this is not a standard repository
 * see https://github.com/chrisbjr/api-guard
 */

/**
 * Class ApiKeyRepository
 * @package App\Waypoint\Repositories
 */
class ApiKeyRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return ApiKey::class;
    }

    /**
     * this is here only so generated controllers work
     * @param $criteria
     * @return null
     * @todo see HER-410
     *
     */
    public function pushCriteria($criteria)
    {
        return null;
    }

    /**
     * Save a new $ApiKeyObj in repository
     *
     * @param array $attributes
     * @return ApiKey
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function create(array $attributes)
    {
        $ApiKeyObj = parent::create($attributes);

        if ($ApiKeyObj instanceof Model && in_array('ApiKey', RepositoryEventBase::getEnabledModelRepositoryEvents()))
        {
            if ( ! $this->isSuppressEvents())
            {
                /** @noinspection PhpUndefinedClassInspection */
                event(new ApiKeyCreatedEvent($ApiKeyObj));
            }
        }

        return $ApiKeyObj;
    }

    /**
     * Update a $ApiKeyObj in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return ApiKey
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $ApiKeyObj = parent::update($attributes, $id);

        if ($ApiKeyObj instanceof Model && in_array('ApiKey', RepositoryEventBase::getEnabledModelRepositoryEvents()))
        {
            if ( ! $this->isSuppressEvents())
            {
                /** @noinspection PhpUndefinedClassInspection */
                event(new ApiKeyUpdatedEvent($ApiKeyObj));
            }
        }

        return $ApiKeyObj;
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
        $ApiKeyObj = $this->find($id);
        $result    = parent::delete($id);

        if ($ApiKeyObj instanceof Model && in_array('ApiKey', RepositoryEventBase::getEnabledModelRepositoryEvents()))
        {
            if ( ! $this->isSuppressEvents())
            {
                /** @noinspection PhpUndefinedClassInspection */
                event(new ApiKeyDeletedEvent($ApiKeyObj));
            }
        }

        return $result;
    }

    /**
     * See  Chrisbjr\ApiGuard\Repositories\ApiKeyRepository as ApiKeyRepositoryBase;
     *
     */

    /**
     * @param $key
     * @return ApiKeyRepository
     */
    public function getByKey($key)
    {
        $apiKey = $this->findWhere(['key', '=', $key])->first();

        if (empty($apiKey) || $apiKey->exists == false)
        {
            return null;
        }

        return $apiKey;
    }

    /**
     * A sure method to generate a unique API key
     *
     * @return string
     */
    public function generateKey()
    {
        do
        {
            $salt   = sha1(time() . mt_rand());
            $newKey = substr($salt, 0, 40);
        } // Already in the DB? Fail. Try again
        while ($this->keyExists($newKey));

        return $newKey;
    }

    /**
     * Make an ApiKey
     *
     * @param null $userId
     * @param int $level
     * @param bool $ignoreLimits
     * @return ApiKey
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function make($userId = null, $level = 10, $ignoreLimits = false)
    {
        return $this->create(
            [
                'user_id'       => $userId,
                'key'           => $this->generateKey(),
                'level'         => $level,
                'ignore_limits' => $ignoreLimits,
            ]
        );
    }

    /**
     * Checks whether a key exists in the database or not
     *
     * @param $key
     * @return bool
     */
    private function keyExists($key)
    {
        $apiKeyCount = $this->findWhere(['key', '=', $key])->count();

        if ($apiKeyCount > 0)
        {
            return true;
        }

        return false;
    }
}
