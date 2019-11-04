<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Exceptions\EntityTagException;
use App\Waypoint\Models\EntityTagEntity;
use App;
use ArrayObject;

/**
 * Class EntityTagEntityRepository
 * @package App\Waypoint\Repositories
 */
class EntityTagEntityRepository extends EntityTagEntityRepositoryBase
{
    /**
     * @param array $attributes
     * @return EntityTagEntity
     * @throws App\Waypoint\Exceptions\DeploymentException
     * @throws EntityTagException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function create(array $attributes)
    {
        /**
         * if not provided a entity_tag_id, let's see if we can figure it out
         */
        if ( ! isset($attributes['entity_tag_id']))
        {
            $EntityTagRepositoryObj = $this->makeRepository(EntityTagRepository::class);
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

        if ( ! isset($attributes['client_id']) && ! isset($attributes['user_id']))
        {
            throw new EntityTagException('Please supply a client_id or a user_id');
        }
        if (isset($attributes['client_id']) && isset($attributes['user_id']))
        {
            if ($attributes['client_id'] && $attributes['user_id'])
            {
                /**
                 * user wins
                 */
                unset($attributes['client_id']);
            }
        }
        if (isset($attributes['entity_id']) && isset($attributes['entity_tag_id']))
        {
            /** @var EntityTagEntityRepository $EntityTagRepositoryObj */
            $EntityTagRepositoryObj = App::make(EntityTagRepository::class);
            $EntityTagObj           = $EntityTagRepositoryObj->find($attributes['entity_tag_id']);
            $entity_model           = $EntityTagObj->entity_model;
            /** @noinspection PhpUndefinedMethodInspection */
            if ( ! $EntityObj = $entity_model::find($attributes['entity_id']))
            {
                throw new EntityTagException('Entity tag model is not  ' . $this->model());
            }
        }
        else
        {
            throw new EntityTagException('entity_id or entity_tag_id tag model is missing ');
        }

        if (isset($attributes['data']))
        {
            if (is_object($attributes['data']) || is_array($attributes['data']))
            {
                $attributes['data'] = json_encode($attributes['data']);
            }
        }
        else
        {
            $attributes['data'] = json_encode(new ArrayObject());
        }
        return parent::create($attributes);
    }

    /**
     * Find data by id
     *
     * @param integer $id
     * @param array $columns
     * @return mixed
     * @throws \App\Waypoint\Exceptions\EntityTagException
     */
    public function find($id, $columns = ['*'])
    {
        if ( ! $Obj = parent::find($id, $columns))
        {
            return null;
        }
        if ( ! $Obj->entityTag->name == $this->model())
        {
            throw new EntityTagException('Entity tag model is not  ' . $this->model());
        }
        $Obj->validate();
        return $Obj;
    }
}