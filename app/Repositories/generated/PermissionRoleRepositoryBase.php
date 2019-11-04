<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\PermissionRole;
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
 * Class PermissionRoleRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method PermissionRole findWithoutFail($id, $columns = ['*']) desc
 * @method PermissionRole find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method PermissionRole first($columns = ['*']) desc
 */
class PermissionRoleRepositoryBase extends BaseRepository
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
        return PermissionRole::class;
    }

    /**
     * Save a new PermissionRole in repository
     *
     * @param array $attributes
     * @return PermissionRole
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $PermissionRoleObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ( $PermissionRoleObj instanceof Model)
        {
            $this->triggerCreatedEvent($PermissionRoleObj);
        }
        return $PermissionRoleObj;
    }

    /**
     * Update a PermissionRole entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return PermissionRole
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $PermissionRoleObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($PermissionRoleObj instanceof Model)
        {
            $this->triggerUpdatedEvent($PermissionRoleObj);
        }
        return $PermissionRoleObj;
    }

    /**
     * Delete a PermissionRole entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $PermissionRoleObj = $this->find($id);
        $result = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($PermissionRoleObj instanceof Model)
        {
            $this->triggerDeletedEvent($PermissionRoleObj);
        }

        return $result;
    }

    /**
     * @param PermissionRole $PermissionRoleObj
     */
    public function triggerCreatedEvent($PermissionRoleObj)
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
            $this->ObjectEnabledForEvents($PermissionRoleObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\PermissionRoleCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                    new \App\Waypoint\Events\PermissionRoleCreatedEvent(
                    $PermissionRoleObj,
                    [
                        'event_trigger_message'        => 'Called from PermissionRoleRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($PermissionRoleObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param PermissionRole $PermissionRoleObj
     */
    public function triggerUpdatedEvent($PermissionRoleObj)
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
            $this->ObjectEnabledForEvents($PermissionRoleObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\PermissionRoleUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\PermissionRoleUpdatedEvent(
                    $PermissionRoleObj,
                    [
                        'event_trigger_message'        => 'Called from PermissionRoleRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($PermissionRoleObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param PermissionRole $PermissionRoleObj
     */
    public function triggerDeletedEvent($PermissionRoleObj)
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
            $this->ObjectEnabledForEvents($PermissionRoleObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\PermissionRoleDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\PermissionRoleDeletedEvent(
                    $PermissionRoleObj,
                    [
                        'event_trigger_message'        => 'Called from PermissionRoleRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($PermissionRoleObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
