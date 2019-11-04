<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\NativeCoa;
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
 * Class NativeCoaRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method NativeCoa findWithoutFail($id, $columns = ['*']) desc
 * @method NativeCoa find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method NativeCoa first($columns = ['*']) desc
 */
class NativeCoaRepositoryBase extends BaseRepository
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
        return NativeCoa::class;
    }

    /**
     * Save a new NativeCoa in repository
     *
     * @param array $attributes
     * @return NativeCoa
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $NativeCoaObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ( $NativeCoaObj instanceof Model)
        {
            $this->triggerCreatedEvent($NativeCoaObj);
        }
        return $NativeCoaObj;
    }

    /**
     * Update a NativeCoa entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return NativeCoa
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $NativeCoaObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($NativeCoaObj instanceof Model)
        {
            $this->triggerUpdatedEvent($NativeCoaObj);
        }
        return $NativeCoaObj;
    }

    /**
     * Delete a NativeCoa entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $NativeCoaObj = $this->find($id);
        $result = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($NativeCoaObj instanceof Model)
        {
            $this->triggerDeletedEvent($NativeCoaObj);
        }

        return $result;
    }

    /**
     * @param NativeCoa $NativeCoaObj
     */
    public function triggerCreatedEvent($NativeCoaObj)
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
            $this->ObjectEnabledForEvents($NativeCoaObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\NativeCoaCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                    new \App\Waypoint\Events\NativeCoaCreatedEvent(
                    $NativeCoaObj,
                    [
                        'event_trigger_message'        => 'Called from NativeCoaRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($NativeCoaObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param NativeCoa $NativeCoaObj
     */
    public function triggerUpdatedEvent($NativeCoaObj)
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
            $this->ObjectEnabledForEvents($NativeCoaObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\NativeCoaUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\NativeCoaUpdatedEvent(
                    $NativeCoaObj,
                    [
                        'event_trigger_message'        => 'Called from NativeCoaRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($NativeCoaObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param NativeCoa $NativeCoaObj
     */
    public function triggerDeletedEvent($NativeCoaObj)
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
            $this->ObjectEnabledForEvents($NativeCoaObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\NativeCoaDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\NativeCoaDeletedEvent(
                    $NativeCoaObj,
                    [
                        'event_trigger_message'        => 'Called from NativeCoaRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($NativeCoaObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
