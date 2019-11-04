<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\NativeAccount;
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
 * Class NativeAccountRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method NativeAccount findWithoutFail($id, $columns = ['*']) desc
 * @method NativeAccount find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method NativeAccount first($columns = ['*']) desc
 */
class NativeAccountRepositoryBase extends BaseRepository
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
        return NativeAccount::class;
    }

    /**
     * Save a new NativeAccount in repository
     *
     * @param array $attributes
     * @return NativeAccount
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $NativeAccountObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ( $NativeAccountObj instanceof Model)
        {
            $this->triggerCreatedEvent($NativeAccountObj);
        }
        return $NativeAccountObj;
    }

    /**
     * Update a NativeAccount entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return NativeAccount
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $NativeAccountObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($NativeAccountObj instanceof Model)
        {
            $this->triggerUpdatedEvent($NativeAccountObj);
        }
        return $NativeAccountObj;
    }

    /**
     * Delete a NativeAccount entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $NativeAccountObj = $this->find($id);
        $result = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($NativeAccountObj instanceof Model)
        {
            $this->triggerDeletedEvent($NativeAccountObj);
        }

        return $result;
    }

    /**
     * @param NativeAccount $NativeAccountObj
     */
    public function triggerCreatedEvent($NativeAccountObj)
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
            $this->ObjectEnabledForEvents($NativeAccountObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\NativeAccountCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                    new \App\Waypoint\Events\NativeAccountCreatedEvent(
                    $NativeAccountObj,
                    [
                        'event_trigger_message'        => 'Called from NativeAccountRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($NativeAccountObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param NativeAccount $NativeAccountObj
     */
    public function triggerUpdatedEvent($NativeAccountObj)
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
            $this->ObjectEnabledForEvents($NativeAccountObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\NativeAccountUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\NativeAccountUpdatedEvent(
                    $NativeAccountObj,
                    [
                        'event_trigger_message'        => 'Called from NativeAccountRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($NativeAccountObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param NativeAccount $NativeAccountObj
     */
    public function triggerDeletedEvent($NativeAccountObj)
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
            $this->ObjectEnabledForEvents($NativeAccountObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\NativeAccountDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\NativeAccountDeletedEvent(
                    $NativeAccountObj,
                    [
                        'event_trigger_message'        => 'Called from NativeAccountRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($NativeAccountObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
