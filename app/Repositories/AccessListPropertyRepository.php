<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Models\AccessList;
use App\Waypoint\Models\AccessListProperty;
use App\Waypoint\Models\AccessListPropertyFull;
use App\Waypoint\Models\Client;
use Cache;

/**
 * Class AccessListPropertyRepository
 * @package App\Waypoint\Repositories
 */
class AccessListPropertyRepository extends AccessListPropertyRepositoryBase
{
    /**
     * Save a new entity in repository
     *
     * @param array $attributes
     * @return AccessListProperty|AccessListPropertyFull
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function create(array $attributes)
    {
        $AccessListPropertyObj = parent::create($attributes);
        Cache::tags('AccessList_' . $AccessListPropertyObj->property->client_id)->flush();
        return $AccessListPropertyObj;
    }

    /**
     * Update a entity in repository by id
     *
     * @param array $attributes
     * @param integer $access_list_property_id
     * @return AccessListProperty
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function update(array $attributes, $access_list_property_id)
    {
        $AccessListPropertyObj = parent::update($attributes, $access_list_property_id);
        Cache::tags('AccessList_' . $AccessListPropertyObj->property->client_id)->flush();
        return $AccessListPropertyObj;
    }

    /**
     * Delete a entity in repository by id
     *
     * @param integer $access_list_property_id
     * @return bool
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function delete($access_list_property_id)
    {
        $AccessListPropertyObj = $this->find($access_list_property_id);
        $result                = parent::delete($access_list_property_id);
        Cache::tags('AccessList_' . $AccessListPropertyObj->property->client_id)->flush();
        return $result;
    }

    public function findAccessListPropertyForClient($client_id)
    {
        $ClientRepository = $this->makeRepository(ClientRepository::class);
        /** @var Client $ClientObj */
        $ClientObj = $ClientRepository->with('accessLists.accessListProperties.property')->find($client_id);
        $return_me = new Collection();
        /** @var AccessList $AccessListObj */
        foreach ($ClientObj->accessLists as $AccessListObj)
        {
            $return_me[] = $AccessListObj;
        }
        return $ClientObj->accessLists;
    }
}

