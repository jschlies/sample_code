<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\PropertyNativeCoa;
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
 * Class PropertyNativeCoaRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method PropertyNativeCoa findWithoutFail($id, $columns = ['*']) desc
 * @method PropertyNativeCoa find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method PropertyNativeCoa first($columns = ['*']) desc
 */
class PropertyNativeCoaRepositoryBase extends BaseRepository
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
        return PropertyNativeCoa::class;
    }

    /**
     * Save a new PropertyNativeCoa in repository
     *
     * @param array $attributes
     * @return PropertyNativeCoa
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $PropertyNativeCoaObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ( $PropertyNativeCoaObj instanceof Model)
        {
            $this->triggerCreatedEvent($PropertyNativeCoaObj);
        }
        return $PropertyNativeCoaObj;
    }

    /**
     * Update a PropertyNativeCoa entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return PropertyNativeCoa
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $PropertyNativeCoaObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($PropertyNativeCoaObj instanceof Model)
        {
            $this->triggerUpdatedEvent($PropertyNativeCoaObj);
        }
        return $PropertyNativeCoaObj;
    }

    /**
     * Delete a PropertyNativeCoa entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $PropertyNativeCoaObj = $this->find($id);
        $result = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($PropertyNativeCoaObj instanceof Model)
        {
            $this->triggerDeletedEvent($PropertyNativeCoaObj);
        }

        return $result;
    }

    /**
     * @param PropertyNativeCoa $PropertyNativeCoaObj
     */
    public function triggerCreatedEvent($PropertyNativeCoaObj)
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
            $this->ObjectEnabledForEvents($PropertyNativeCoaObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\PropertyNativeCoaCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                    new \App\Waypoint\Events\PropertyNativeCoaCreatedEvent(
                    $PropertyNativeCoaObj,
                    [
                        'event_trigger_message'        => 'Called from PropertyNativeCoaRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($PropertyNativeCoaObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param PropertyNativeCoa $PropertyNativeCoaObj
     */
    public function triggerUpdatedEvent($PropertyNativeCoaObj)
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
            $this->ObjectEnabledForEvents($PropertyNativeCoaObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\PropertyNativeCoaUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\PropertyNativeCoaUpdatedEvent(
                    $PropertyNativeCoaObj,
                    [
                        'event_trigger_message'        => 'Called from PropertyNativeCoaRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($PropertyNativeCoaObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param PropertyNativeCoa $PropertyNativeCoaObj
     */
    public function triggerDeletedEvent($PropertyNativeCoaObj)
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
            $this->ObjectEnabledForEvents($PropertyNativeCoaObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\PropertyNativeCoaDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\PropertyNativeCoaDeletedEvent(
                    $PropertyNativeCoaObj,
                    [
                        'event_trigger_message'        => 'Called from PropertyNativeCoaRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($PropertyNativeCoaObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
