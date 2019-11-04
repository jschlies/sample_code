<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\FailedJob;
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
 * Class FailedJobRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method FailedJob findWithoutFail($id, $columns = ['*']) desc
 * @method FailedJob find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method FailedJob first($columns = ['*']) desc
 */
class FailedJobRepositoryBase extends BaseRepository
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
        return FailedJob::class;
    }

    /**
     * Save a new FailedJob in repository
     *
     * @param array $attributes
     * @return FailedJob
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $FailedJobObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ( $FailedJobObj instanceof Model)
        {
            $this->triggerCreatedEvent($FailedJobObj);
        }
        return $FailedJobObj;
    }

    /**
     * Update a FailedJob entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return FailedJob
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $FailedJobObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($FailedJobObj instanceof Model)
        {
            $this->triggerUpdatedEvent($FailedJobObj);
        }
        return $FailedJobObj;
    }

    /**
     * Delete a FailedJob entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $FailedJobObj = $this->find($id);
        $result = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($FailedJobObj instanceof Model)
        {
            $this->triggerDeletedEvent($FailedJobObj);
        }

        return $result;
    }

    /**
     * @param FailedJob $FailedJobObj
     */
    public function triggerCreatedEvent($FailedJobObj)
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
            $this->ObjectEnabledForEvents($FailedJobObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\FailedJobCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                    new \App\Waypoint\Events\FailedJobCreatedEvent(
                    $FailedJobObj,
                    [
                        'event_trigger_message'        => 'Called from FailedJobRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($FailedJobObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param FailedJob $FailedJobObj
     */
    public function triggerUpdatedEvent($FailedJobObj)
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
            $this->ObjectEnabledForEvents($FailedJobObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\FailedJobUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\FailedJobUpdatedEvent(
                    $FailedJobObj,
                    [
                        'event_trigger_message'        => 'Called from FailedJobRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($FailedJobObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param FailedJob $FailedJobObj
     */
    public function triggerDeletedEvent($FailedJobObj)
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
            $this->ObjectEnabledForEvents($FailedJobObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\FailedJobDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\FailedJobDeletedEvent(
                    $FailedJobObj,
                    [
                        'event_trigger_message'        => 'Called from FailedJobRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($FailedJobObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
