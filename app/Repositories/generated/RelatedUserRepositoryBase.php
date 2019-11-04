<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\RelatedUser;
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
 * Class RelatedUserRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method RelatedUser findWithoutFail($id, $columns = ['*']) desc
 * @method RelatedUser find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method RelatedUser first($columns = ['*']) desc
 */
class RelatedUserRepositoryBase extends BaseRepository
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
        return RelatedUser::class;
    }

    /**
     * Save a new RelatedUser in repository
     *
     * @param array $attributes
     * @return RelatedUser
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $RelatedUserObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ( $RelatedUserObj instanceof Model)
        {
            $this->triggerCreatedEvent($RelatedUserObj);
        }
        return $RelatedUserObj;
    }

    /**
     * Update a RelatedUser entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return RelatedUser
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $RelatedUserObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($RelatedUserObj instanceof Model)
        {
            $this->triggerUpdatedEvent($RelatedUserObj);
        }
        return $RelatedUserObj;
    }

    /**
     * Delete a RelatedUser entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $RelatedUserObj = $this->find($id);
        $result = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($RelatedUserObj instanceof Model)
        {
            $this->triggerDeletedEvent($RelatedUserObj);
        }

        return $result;
    }

    /**
     * @param RelatedUser $RelatedUserObj
     */
    public function triggerCreatedEvent($RelatedUserObj)
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
            $this->ObjectEnabledForEvents($RelatedUserObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\RelatedUserCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                    new \App\Waypoint\Events\RelatedUserCreatedEvent(
                    $RelatedUserObj,
                    [
                        'event_trigger_message'        => 'Called from RelatedUserRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($RelatedUserObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param RelatedUser $RelatedUserObj
     */
    public function triggerUpdatedEvent($RelatedUserObj)
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
            $this->ObjectEnabledForEvents($RelatedUserObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\RelatedUserUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\RelatedUserUpdatedEvent(
                    $RelatedUserObj,
                    [
                        'event_trigger_message'        => 'Called from RelatedUserRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($RelatedUserObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param RelatedUser $RelatedUserObj
     */
    public function triggerDeletedEvent($RelatedUserObj)
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
            $this->ObjectEnabledForEvents($RelatedUserObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\RelatedUserDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\RelatedUserDeletedEvent(
                    $RelatedUserObj,
                    [
                        'event_trigger_message'        => 'Called from RelatedUserRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($RelatedUserObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
