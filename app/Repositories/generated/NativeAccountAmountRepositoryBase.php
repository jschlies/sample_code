<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\NativeAccountAmount;
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
 * Class NativeAccountAmountRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method NativeAccountAmount findWithoutFail($id, $columns = ['*']) desc
 * @method NativeAccountAmount find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method NativeAccountAmount first($columns = ['*']) desc
 */
class NativeAccountAmountRepositoryBase extends BaseRepository
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
        return NativeAccountAmount::class;
    }

    /**
     * Save a new NativeAccountAmount in repository
     *
     * @param array $attributes
     * @return NativeAccountAmount
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $NativeAccountAmountObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ( $NativeAccountAmountObj instanceof Model)
        {
            $this->triggerCreatedEvent($NativeAccountAmountObj);
        }
        return $NativeAccountAmountObj;
    }

    /**
     * Update a NativeAccountAmount entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return NativeAccountAmount
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $NativeAccountAmountObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($NativeAccountAmountObj instanceof Model)
        {
            $this->triggerUpdatedEvent($NativeAccountAmountObj);
        }
        return $NativeAccountAmountObj;
    }

    /**
     * Delete a NativeAccountAmount entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $NativeAccountAmountObj = $this->find($id);
        $result = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($NativeAccountAmountObj instanceof Model)
        {
            $this->triggerDeletedEvent($NativeAccountAmountObj);
        }

        return $result;
    }

    /**
     * @param NativeAccountAmount $NativeAccountAmountObj
     */
    public function triggerCreatedEvent($NativeAccountAmountObj)
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
            $this->ObjectEnabledForEvents($NativeAccountAmountObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\NativeAccountAmountCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                    new \App\Waypoint\Events\NativeAccountAmountCreatedEvent(
                    $NativeAccountAmountObj,
                    [
                        'event_trigger_message'        => 'Called from NativeAccountAmountRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($NativeAccountAmountObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param NativeAccountAmount $NativeAccountAmountObj
     */
    public function triggerUpdatedEvent($NativeAccountAmountObj)
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
            $this->ObjectEnabledForEvents($NativeAccountAmountObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\NativeAccountAmountUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\NativeAccountAmountUpdatedEvent(
                    $NativeAccountAmountObj,
                    [
                        'event_trigger_message'        => 'Called from NativeAccountAmountRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($NativeAccountAmountObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param NativeAccountAmount $NativeAccountAmountObj
     */
    public function triggerDeletedEvent($NativeAccountAmountObj)
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
            $this->ObjectEnabledForEvents($NativeAccountAmountObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\NativeAccountAmountDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\NativeAccountAmountDeletedEvent(
                    $NativeAccountAmountObj,
                    [
                        'event_trigger_message'        => 'Called from NativeAccountAmountRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($NativeAccountAmountObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
