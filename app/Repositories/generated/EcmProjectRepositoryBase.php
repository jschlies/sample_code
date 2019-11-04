<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\EcmProject;
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
 * Class EcmProjectRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method EcmProject findWithoutFail($id, $columns = ['*']) desc
 * @method EcmProject find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method EcmProject first($columns = ['*']) desc
 */
class EcmProjectRepositoryBase extends BaseRepository
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
        return EcmProject::class;
    }

    /**
     * Save a new EcmProject in repository
     *
     * @param array $attributes
     * @return EcmProject
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $EcmProjectObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ( $EcmProjectObj instanceof Model)
        {
            $this->triggerCreatedEvent($EcmProjectObj);
        }
        return $EcmProjectObj;
    }

    /**
     * Update a EcmProject entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return EcmProject
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $EcmProjectObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($EcmProjectObj instanceof Model)
        {
            $this->triggerUpdatedEvent($EcmProjectObj);
        }
        return $EcmProjectObj;
    }

    /**
     * Delete a EcmProject entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $EcmProjectObj = $this->find($id);
        $result = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($EcmProjectObj instanceof Model)
        {
            $this->triggerDeletedEvent($EcmProjectObj);
        }

        return $result;
    }

    /**
     * @param EcmProject $EcmProjectObj
     */
    public function triggerCreatedEvent($EcmProjectObj)
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
            $this->ObjectEnabledForEvents($EcmProjectObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\EcmProjectCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                    new \App\Waypoint\Events\EcmProjectCreatedEvent(
                    $EcmProjectObj,
                    [
                        'event_trigger_message'        => 'Called from EcmProjectRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($EcmProjectObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param EcmProject $EcmProjectObj
     */
    public function triggerUpdatedEvent($EcmProjectObj)
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
            $this->ObjectEnabledForEvents($EcmProjectObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\EcmProjectUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\EcmProjectUpdatedEvent(
                    $EcmProjectObj,
                    [
                        'event_trigger_message'        => 'Called from EcmProjectRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($EcmProjectObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param EcmProject $EcmProjectObj
     */
    public function triggerDeletedEvent($EcmProjectObj)
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
            $this->ObjectEnabledForEvents($EcmProjectObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\EcmProjectDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\EcmProjectDeletedEvent(
                    $EcmProjectObj,
                    [
                        'event_trigger_message'        => 'Called from EcmProjectRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($EcmProjectObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
