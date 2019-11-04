<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\AccessListProperty;
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
 * Class AccessListPropertyRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method AccessListProperty findWithoutFail($id, $columns = ['*']) desc
 * @method AccessListProperty find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method AccessListProperty first($columns = ['*']) desc
 */
class AccessListPropertyRepositoryBase extends BaseRepository
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
        return AccessListProperty::class;
    }

    /**
     * Save a new AccessListProperty in repository
     *
     * @param array $attributes
     * @return AccessListProperty
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $AccessListPropertyObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($AccessListPropertyObj instanceof Model)
        {
            $this->triggerCreatedEvent($AccessListPropertyObj);
        }
        return $AccessListPropertyObj;
    }

    /**
     * Update a AccessListProperty entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return AccessListProperty
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $AccessListPropertyObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($AccessListPropertyObj instanceof Model)
        {
            $this->triggerUpdatedEvent($AccessListPropertyObj);
        }
        return $AccessListPropertyObj;
    }

    /**
     * Delete a AccessListProperty entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $AccessListPropertyObj = $this->find($id);
        $result                = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($AccessListPropertyObj instanceof Model)
        {
            $this->triggerDeletedEvent($AccessListPropertyObj);
        }

        return $result;
    }

    /**
     * @param AccessListProperty $AccessListPropertyObj
     */
    public function triggerCreatedEvent($AccessListPropertyObj)
    {
        if ($this->isSuppressEvents())
        {
            return;
        }

        /**
         * Check if there are events set up for this model.
         */
        if (
            in_array($this->model(), RepositoryEventBase::getEnabledModelRepositoryEvents()) ||
            $this->ObjectEnabledForEvents($AccessListPropertyObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AccessListPropertyCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\AccessListPropertyCreatedEvent(
                    $AccessListPropertyObj,
                    [
                        'event_trigger_message'        => 'Called from AccessListPropertyRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($AccessListPropertyObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param AccessListProperty $AccessListPropertyObj
     */
    public function triggerUpdatedEvent($AccessListPropertyObj)
    {
        if ($this->isSuppressEvents())
        {
            return;
        }

        /**
         * Check if there are events set up for this model.
         */
        if (
            in_array($this->model(), RepositoryEventBase::getEnabledModelRepositoryEvents()) ||
            $this->ObjectEnabledForEvents($AccessListPropertyObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AccessListPropertyUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\AccessListPropertyUpdatedEvent(
                      $AccessListPropertyObj,
                      [
                          'event_trigger_message'        => 'Called from AccessListPropertyRepositoryBase',
                          'event_trigger_id'             => waypoint_generate_uuid(),
                          'event_trigger_class'          => self::class,
                          'event_trigger_class_instance' => get_class($this),
                          'event_trigger_object_class'   => get_class($AccessListPropertyObj),
                          'event_trigger_absolute_class' => __CLASS__,
                          'event_trigger_file'           => __FILE__,
                          'event_trigger_line'           => __LINE__,
                      ]
                  )
            );
        }
    }

    /**
     * @param AccessListProperty $AccessListPropertyObj
     */
    public function triggerDeletedEvent($AccessListPropertyObj)
    {
        if ($this->isSuppressEvents())
        {
            return;
        }

        /**
         * Check if there are events set up for this model.
         */
        if (
            in_array($this->model(), RepositoryEventBase::getEnabledModelRepositoryEvents()) ||
            $this->ObjectEnabledForEvents($AccessListPropertyObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AccessListPropertyDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\AccessListPropertyDeletedEvent(
                    $AccessListPropertyObj,
                    [
                        'event_trigger_message'        => 'Called from AccessListPropertyRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($AccessListPropertyObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
