<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Models\EntityTagEntity;
use \App\Waypoint\Models\Favorite;
use App\Waypoint\Exceptions\EntityTagException;
use App\Waypoint\Models\FavoriteGroup;

/**
 * Class FavoriteRepository
 * @package App\Waypoint\Repositories
 */
class FavoriteRepository extends EntityTagEntityRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return Favorite::class;
    }

    /**
     * @param array $attributes
     * @return EntityTagEntity|bool|mixed
     * @throws \App\Waypoint\Exceptions\DeploymentException
     * @throws \App\Waypoint\Exceptions\EntityTagException
     */
    public function create(array $attributes)
    {
        $EntityTagRepositoryObj = $this->makeRepository(EntityTagRepository::class);
        if ( ! isset($attributes['entity_tag_id']))
        {
            if ($EntityTagObj = $EntityTagRepositoryObj->findWhere(
                [
                    ['name', '=', $this->model()],
                    ['entity_model', '=', isset($attributes['entity_model']) ? $attributes['entity_model'] : null],
                ]
            )->first()
            )
            {
                $attributes['entity_tag_id'] = $EntityTagObj->id;
            }
            else
            {
                throw new EntityTagException('Please supply a entity_tag_id or a entity_model');
            }
        }
        /**
         * if a favorite exists adn we try to recreate, just return created obj
         */
        if ($FavoriteObj = $this->findWhere(
            [
                'user_id'       => isset($attributes['user_id']) ? $attributes['user_id'] : null,
                'entity_id'     => isset($attributes['entity_id']) ? $attributes['entity_id'] : null,
                'entity_tag_id' => isset($attributes['entity_tag_id']) ? $attributes['entity_tag_id'] : null,
            ]
        )->first()
        )
        {
            return $FavoriteObj;
        }

        return parent::create($attributes);
    }

    /**
     * Find data by id
     *
     * @param integer $id
     * @param array $columns
     * @return Favorite
     * @throws \App\Waypoint\Exceptions\EntityTagException
     */
    public function find($id, $columns = ['*'])
    {
        $FavoriteObj = parent::find($id, $columns);
        if ( ! $FavoriteObj->entityTag->name == Favorite::class)
        {
            throw new EntityTagException('Entity tag model is not  ' . $this->model());
        }
        return $FavoriteObj;
    }

    /**
     * @param array $columns
     * @return Collection
     */
    public function all($columns = ['*'])
    {
        return $this->findWhereIn('entity_tag_id', FavoriteGroup::where('name', '=', Favorite::class)->get()->getArrayOfIDs());
    }

    /**
     * Delete a entity in repository by id
     *
     * @param integer $id
     * @return bool
     * @throws \App\Waypoint\Exceptions\EntityTagException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function delete($id)
    {
        /**
         * for favorites, if we try to delete a non-existing favorites, just let it go
         */
        if ( ! $this->find($id))
        {
            return true;
        }
        $result = parent::delete($id);
        return $result;
    }
}