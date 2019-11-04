<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\LeaseSchedule;
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
 * Class LeaseScheduleRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method LeaseSchedule findWithoutFail($id, $columns = ['*']) desc
 * @method LeaseSchedule find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method LeaseSchedule first($columns = ['*']) desc
 */
class LeaseScheduleRepositoryBase extends BaseRepository
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
        return LeaseSchedule::class;
    }

    /**
     * Save a new LeaseSchedule in repository
     *
     * @param array $attributes
     * @return LeaseSchedule
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $LeaseScheduleObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ( $LeaseScheduleObj instanceof Model)
        {
            $this->triggerCreatedEvent($LeaseScheduleObj);
        }
        return $LeaseScheduleObj;
    }

    /**
     * Update a LeaseSchedule entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return LeaseSchedule
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $LeaseScheduleObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($LeaseScheduleObj instanceof Model)
        {
            $this->triggerUpdatedEvent($LeaseScheduleObj);
        }
        return $LeaseScheduleObj;
    }

    /**
     * Delete a LeaseSchedule entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $LeaseScheduleObj = $this->find($id);
        $result = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($LeaseScheduleObj instanceof Model)
        {
            $this->triggerDeletedEvent($LeaseScheduleObj);
        }

        return $result;
    }

    /**
     * @param LeaseSchedule $LeaseScheduleObj
     */
    public function triggerCreatedEvent($LeaseScheduleObj)
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
            $this->ObjectEnabledForEvents($LeaseScheduleObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\LeaseScheduleCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                    new \App\Waypoint\Events\LeaseScheduleCreatedEvent(
                    $LeaseScheduleObj,
                    [
                        'event_trigger_message'        => 'Called from LeaseScheduleRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($LeaseScheduleObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param LeaseSchedule $LeaseScheduleObj
     */
    public function triggerUpdatedEvent($LeaseScheduleObj)
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
            $this->ObjectEnabledForEvents($LeaseScheduleObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\LeaseScheduleUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\LeaseScheduleUpdatedEvent(
                    $LeaseScheduleObj,
                    [
                        'event_trigger_message'        => 'Called from LeaseScheduleRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($LeaseScheduleObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param LeaseSchedule $LeaseScheduleObj
     */
    public function triggerDeletedEvent($LeaseScheduleObj)
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
            $this->ObjectEnabledForEvents($LeaseScheduleObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\LeaseScheduleDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\LeaseScheduleDeletedEvent(
                    $LeaseScheduleObj,
                    [
                        'event_trigger_message'        => 'Called from LeaseScheduleRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($LeaseScheduleObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
