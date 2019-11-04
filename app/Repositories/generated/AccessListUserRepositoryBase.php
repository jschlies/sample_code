<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\AccessListUser;
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
 * Class AccessListUserRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method AccessListUser findWithoutFail($id, $columns = ['*']) desc
 * @method AccessListUser find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method AccessListUser first($columns = ['*']) desc
 */
class AccessListUserRepositoryBase extends BaseRepository
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
        return AccessListUser::class;
    }

    /**
     * Save a new AccessListUser in repository
     *
     * @param array $attributes
     * @return AccessListUser
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $AccessListUserObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($AccessListUserObj instanceof Model)
        {
            $this->triggerCreatedEvent($AccessListUserObj);
        }
        return $AccessListUserObj;
    }

    /**
     * Update a AccessListUser entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return AccessListUser
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $AccessListUserObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($AccessListUserObj instanceof Model)
        {
            $this->triggerUpdatedEvent($AccessListUserObj);
        }
        return $AccessListUserObj;
    }

    /**
     * Delete a AccessListUser entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $AccessListUserObj = $this->find($id);
        $result            = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($AccessListUserObj instanceof Model)
        {
            $this->triggerDeletedEvent($AccessListUserObj);
        }

        return $result;
    }

    /**
     * @param AccessListUser $AccessListUserObj
     */
    public function triggerCreatedEvent($AccessListUserObj)
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
            $this->ObjectEnabledForEvents($AccessListUserObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AccessListUserCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\AccessListUserCreatedEvent(
                    $AccessListUserObj,
                    [
                        'event_trigger_message'        => 'Called from AccessListUserRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($AccessListUserObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param AccessListUser $AccessListUserObj
     */
    public function triggerUpdatedEvent($AccessListUserObj)
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
            $this->ObjectEnabledForEvents($AccessListUserObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AccessListUserUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\AccessListUserUpdatedEvent(
                      $AccessListUserObj,
                      [
                          'event_trigger_message'        => 'Called from AccessListUserRepositoryBase',
                          'event_trigger_id'             => waypoint_generate_uuid(),
                          'event_trigger_class'          => self::class,
                          'event_trigger_class_instance' => get_class($this),
                          'event_trigger_object_class'   => get_class($AccessListUserObj),
                          'event_trigger_absolute_class' => __CLASS__,
                          'event_trigger_file'           => __FILE__,
                          'event_trigger_line'           => __LINE__,
                      ]
                  )
            );
        }
    }

    /**
     * @param AccessListUser $AccessListUserObj
     */
    public function triggerDeletedEvent($AccessListUserObj)
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
            $this->ObjectEnabledForEvents($AccessListUserObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AccessListUserDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\AccessListUserDeletedEvent(
                    $AccessListUserObj,
                    [
                        'event_trigger_message'        => 'Called from AccessListUserRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($AccessListUserObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
