<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\NotificationLog;
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
 * Class NotificationLogRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method NotificationLog findWithoutFail($id, $columns = ['*']) desc
 * @method NotificationLog find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method NotificationLog first($columns = ['*']) desc
 */
class NotificationLogRepositoryBase extends BaseRepository
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
        return NotificationLog::class;
    }

    /**
     * Save a new NotificationLog in repository
     *
     * @param array $attributes
     * @return NotificationLog
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $NotificationLogObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ( $NotificationLogObj instanceof Model)
        {
            $this->triggerCreatedEvent($NotificationLogObj);
        }
        return $NotificationLogObj;
    }

    /**
     * Update a NotificationLog entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return NotificationLog
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $NotificationLogObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($NotificationLogObj instanceof Model)
        {
            $this->triggerUpdatedEvent($NotificationLogObj);
        }
        return $NotificationLogObj;
    }

    /**
     * Delete a NotificationLog entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $NotificationLogObj = $this->find($id);
        $result = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($NotificationLogObj instanceof Model)
        {
            $this->triggerDeletedEvent($NotificationLogObj);
        }

        return $result;
    }

    /**
     * @param NotificationLog $NotificationLogObj
     */
    public function triggerCreatedEvent($NotificationLogObj)
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
            $this->ObjectEnabledForEvents($NotificationLogObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\NotificationLogCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                    new \App\Waypoint\Events\NotificationLogCreatedEvent(
                    $NotificationLogObj,
                    [
                        'event_trigger_message'        => 'Called from NotificationLogRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($NotificationLogObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param NotificationLog $NotificationLogObj
     */
    public function triggerUpdatedEvent($NotificationLogObj)
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
            $this->ObjectEnabledForEvents($NotificationLogObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\NotificationLogUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\NotificationLogUpdatedEvent(
                    $NotificationLogObj,
                    [
                        'event_trigger_message'        => 'Called from NotificationLogRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($NotificationLogObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param NotificationLog $NotificationLogObj
     */
    public function triggerDeletedEvent($NotificationLogObj)
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
            $this->ObjectEnabledForEvents($NotificationLogObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\NotificationLogDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\NotificationLogDeletedEvent(
                    $NotificationLogObj,
                    [
                        'event_trigger_message'        => 'Called from NotificationLogRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($NotificationLogObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
