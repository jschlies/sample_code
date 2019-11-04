<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\LeaseTenant;
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
 * Class LeaseTenantRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method LeaseTenant findWithoutFail($id, $columns = ['*']) desc
 * @method LeaseTenant find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method LeaseTenant first($columns = ['*']) desc
 */
class LeaseTenantRepositoryBase extends BaseRepository
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
        return LeaseTenant::class;
    }

    /**
     * Save a new LeaseTenant in repository
     *
     * @param array $attributes
     * @return LeaseTenant
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $LeaseTenantObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ( $LeaseTenantObj instanceof Model)
        {
            $this->triggerCreatedEvent($LeaseTenantObj);
        }
        return $LeaseTenantObj;
    }

    /**
     * Update a LeaseTenant entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return LeaseTenant
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $LeaseTenantObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($LeaseTenantObj instanceof Model)
        {
            $this->triggerUpdatedEvent($LeaseTenantObj);
        }
        return $LeaseTenantObj;
    }

    /**
     * Delete a LeaseTenant entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $LeaseTenantObj = $this->find($id);
        $result = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($LeaseTenantObj instanceof Model)
        {
            $this->triggerDeletedEvent($LeaseTenantObj);
        }

        return $result;
    }

    /**
     * @param LeaseTenant $LeaseTenantObj
     */
    public function triggerCreatedEvent($LeaseTenantObj)
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
            $this->ObjectEnabledForEvents($LeaseTenantObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\LeaseTenantCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                    new \App\Waypoint\Events\LeaseTenantCreatedEvent(
                    $LeaseTenantObj,
                    [
                        'event_trigger_message'        => 'Called from LeaseTenantRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($LeaseTenantObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param LeaseTenant $LeaseTenantObj
     */
    public function triggerUpdatedEvent($LeaseTenantObj)
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
            $this->ObjectEnabledForEvents($LeaseTenantObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\LeaseTenantUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\LeaseTenantUpdatedEvent(
                    $LeaseTenantObj,
                    [
                        'event_trigger_message'        => 'Called from LeaseTenantRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($LeaseTenantObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param LeaseTenant $LeaseTenantObj
     */
    public function triggerDeletedEvent($LeaseTenantObj)
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
            $this->ObjectEnabledForEvents($LeaseTenantObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\LeaseTenantDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\LeaseTenantDeletedEvent(
                    $LeaseTenantObj,
                    [
                        'event_trigger_message'        => 'Called from LeaseTenantRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($LeaseTenantObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
