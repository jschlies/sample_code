<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\AccessList;
use Cache;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class AccessListRepository
 * @package App\Waypoint\Repositories
 */
class AccessListRepository extends AccessListRepositoryBase
{
    /**
     * @return string
     */
    public function model()
    {
        return AccessList::class;
    }

    /**
     * Save a new AccessList in repository
     *
     * @param array $attributes
     * @return AccessList
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $AccessListObj = parent::create($attributes);
        Cache::tags('AccessList_' . $AccessListObj->client_id)->flush();

        return $AccessListObj;
    }

    /**
     * Update a AccessList entity in repository by id
     *
     * @param array $attributes
     * @param int $access_list_id
     * @return AccessList
     * @throws ValidatorException
     */
    public function update(array $attributes, $access_list_id)
    {
        $AccessListObj = parent::update($attributes, $access_list_id);
        Cache::tags('AccessList_' . $AccessListObj->client_id)->flush();

        return $AccessListObj;
    }

    /**
     * Delete a AccessList entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $AccessListObj = $this->find($id);
        $result        = parent::delete($id);
        Cache::tags('AccessList_' . $AccessListObj->client_id)->flush();

        return $result;
    }
}
