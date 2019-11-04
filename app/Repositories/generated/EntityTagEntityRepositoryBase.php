<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\EntityTagEntity;
use App\Waypoint\Repository as BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * README - README - README - README - README
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 * See readme.md
 * This file is generated - edits to this file will be lost.
 * Please read and understand the info on generating models/controllers/requests/test in the readme.md
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 */

/**
 * Class EntityTagEntityRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method EntityTagEntity findWithoutFail($id, $columns = ['*']) desc
 * @method EntityTagEntity find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method EntityTagEntity first($columns = ['*']) desc
 */
class EntityTagEntityRepositoryBase extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return EntityTagEntity::class;
    }

    /**
     * Save a new EntityTagEntity in repository
     *
     * @param array $attributes
     * @return EntityTagEntity
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $EntityTagEntityObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ( $EntityTagEntityObj instanceof Model)
        {
            $this->triggerCreatedEvent($EntityTagEntityObj);
        }
        return $EntityTagEntityObj;
    }

    /**
     * Update a EntityTagEntity entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return EntityTagEntity
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $EntityTagEntityObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($EntityTagEntityObj instanceof Model)
        {
            $this->triggerUpdatedEvent($EntityTagEntityObj);
        }
        return $EntityTagEntityObj;
    }

    /**
     * Delete a EntityTagEntity entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $EntityTagEntityObj = $this->find($id);
        $result = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($EntityTagEntityObj instanceof Model)
        {
            $this->triggerDeletedEvent($EntityTagEntityObj);
        }

        return $result;
    }

    /**
     * @param EntityTagEntity $EntityTagEntityObj
     */
    public function triggerCreatedEvent($EntityTagEntityObj)
    {
        if($this->isSuppressEvents())
        {
            return;
        }

        /**
         * Check if there are events set up for this model.
         */
        if (
            in_array($this->model(), RepositoryEventBase::getEnabledModelRepositoryEvents()) ||
            $this->ObjectEnabledForEvents($EntityTagEntityObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\EntityTagEntityCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                    new \App\Waypoint\Events\EntityTagEntityCreatedEvent(
                    $EntityTagEntityObj,
                    [
                        'event_trigger_message'        => 'Called from EntityTagEntityRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($EntityTagEntityObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param EntityTagEntity $EntityTagEntityObj
     */
    public function triggerUpdatedEvent($EntityTagEntityObj)
    {
        if($this->isSuppressEvents())
        {
            return;
        }

        /**
         * Check if there are events set up for this model.
         */
        if (
            in_array($this->model(), RepositoryEventBase::getEnabledModelRepositoryEvents()) ||
            $this->ObjectEnabledForEvents($EntityTagEntityObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\EntityTagEntityUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\EntityTagEntityUpdatedEvent(
                    $EntityTagEntityObj,
                    [
                        'event_trigger_message'        => 'Called from EntityTagEntityRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($EntityTagEntityObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param EntityTagEntity $EntityTagEntityObj
     */
    public function triggerDeletedEvent($EntityTagEntityObj)
    {
        if($this->isSuppressEvents())
        {
            return;
        }

        /**
         * Check if there are events set up for this model.
         */
        if (
            in_array($this->model(), RepositoryEventBase::getEnabledModelRepositoryEvents()) ||
            $this->ObjectEnabledForEvents($EntityTagEntityObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\EntityTagEntityDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\EntityTagEntityDeletedEvent(
                    $EntityTagEntityObj,
                    [
                        'event_trigger_message'        => 'Called from EntityTagEntityRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($EntityTagEntityObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
