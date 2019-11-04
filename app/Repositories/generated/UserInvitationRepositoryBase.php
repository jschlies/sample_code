<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\UserInvitation;
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
 * Class UserInvitationRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method UserInvitation findWithoutFail($id, $columns = ['*']) desc
 * @method UserInvitation find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method UserInvitation first($columns = ['*']) desc
 */
class UserInvitationRepositoryBase extends BaseRepository
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
        return UserInvitation::class;
    }

    /**
     * Save a new UserInvitation in repository
     *
     * @param array $attributes
     * @return UserInvitation
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $UserInvitationObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ( $UserInvitationObj instanceof Model)
        {
            $this->triggerCreatedEvent($UserInvitationObj);
        }
        return $UserInvitationObj;
    }

    /**
     * Update a UserInvitation entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return UserInvitation
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $UserInvitationObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($UserInvitationObj instanceof Model)
        {
            $this->triggerUpdatedEvent($UserInvitationObj);
        }
        return $UserInvitationObj;
    }

    /**
     * Delete a UserInvitation entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $UserInvitationObj = $this->find($id);
        $result = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($UserInvitationObj instanceof Model)
        {
            $this->triggerDeletedEvent($UserInvitationObj);
        }

        return $result;
    }

    /**
     * @param UserInvitation $UserInvitationObj
     */
    public function triggerCreatedEvent($UserInvitationObj)
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
            $this->ObjectEnabledForEvents($UserInvitationObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\UserInvitationCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                    new \App\Waypoint\Events\UserInvitationCreatedEvent(
                    $UserInvitationObj,
                    [
                        'event_trigger_message'        => 'Called from UserInvitationRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($UserInvitationObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param UserInvitation $UserInvitationObj
     */
    public function triggerUpdatedEvent($UserInvitationObj)
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
            $this->ObjectEnabledForEvents($UserInvitationObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\UserInvitationUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\UserInvitationUpdatedEvent(
                    $UserInvitationObj,
                    [
                        'event_trigger_message'        => 'Called from UserInvitationRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($UserInvitationObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param UserInvitation $UserInvitationObj
     */
    public function triggerDeletedEvent($UserInvitationObj)
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
            $this->ObjectEnabledForEvents($UserInvitationObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\UserInvitationDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\UserInvitationDeletedEvent(
                    $UserInvitationObj,
                    [
                        'event_trigger_message'        => 'Called from UserInvitationRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($UserInvitationObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
