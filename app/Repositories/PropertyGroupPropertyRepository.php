<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\PropertyGroupProperty;
use Cache;

/**
 * Class PropertyGroupPropertyRepository
 * @package App\Waypoint\Repositories
 */
class PropertyGroupPropertyRepository extends PropertyGroupPropertyRepositoryBase
{
    /**
     * Save a new entity in repository
     *
     * @param array $attributes
     * @return PropertyGroupProperty
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function create(array $attributes)
    {
        $PropertyGroupPropertyObj = parent::create($attributes);
        Cache::tags('PropertyGroup_' . $PropertyGroupPropertyObj->propertyGroup->client_id)->flush();

        return $PropertyGroupPropertyObj;
    }

    /**
     * Update a entity in repository by id
     *
     * @param array $attributes
     * @param integer $access_list_property_id
     * @return PropertyGroupProperty
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function update(array $attributes, $access_list_property_id)
    {
        /** @var PropertyGroupProperty $PropertyGroupPropertyObj */
        $PropertyGroupPropertyObj = parent::update($attributes, $access_list_property_id);
        Cache::tags('PropertyGroup_' . $PropertyGroupPropertyObj->propertyGroup->client_id)->flush();

        return $PropertyGroupPropertyObj;
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
        $PropertyGroupPropertyObj = $this->find($access_list_property_id);
        $result                   = parent::delete($access_list_property_id);
        Cache::tags('PropertyGroup_' . $PropertyGroupPropertyObj->propertyGroup->client_id)->flush();

        return $result;
    }
}
