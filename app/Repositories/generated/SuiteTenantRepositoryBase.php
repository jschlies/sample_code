<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\SuiteTenant;
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
 * Class SuiteTenantRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method SuiteTenant findWithoutFail($id, $columns = ['*']) desc
 * @method SuiteTenant find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method SuiteTenant first($columns = ['*']) desc
 */
class SuiteTenantRepositoryBase extends BaseRepository
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
        return SuiteTenant::class;
    }

    /**
     * Save a new SuiteTenant in repository
     *
     * @param array $attributes
     * @return SuiteTenant
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $SuiteTenantObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ( $SuiteTenantObj instanceof Model)
        {
            $this->triggerCreatedEvent($SuiteTenantObj);
        }
        return $SuiteTenantObj;
    }

    /**
     * Update a SuiteTenant entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return SuiteTenant
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $SuiteTenantObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($SuiteTenantObj instanceof Model)
        {
            $this->triggerUpdatedEvent($SuiteTenantObj);
        }
        return $SuiteTenantObj;
    }

    /**
     * Delete a SuiteTenant entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $SuiteTenantObj = $this->find($id);
        $result = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($SuiteTenantObj instanceof Model)
        {
            $this->triggerDeletedEvent($SuiteTenantObj);
        }

        return $result;
    }

    /**
     * @param SuiteTenant $SuiteTenantObj
     */
    public function triggerCreatedEvent($SuiteTenantObj)
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
            $this->ObjectEnabledForEvents($SuiteTenantObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\SuiteTenantCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                    new \App\Waypoint\Events\SuiteTenantCreatedEvent(
                    $SuiteTenantObj,
                    [
                        'event_trigger_message'        => 'Called from SuiteTenantRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($SuiteTenantObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param SuiteTenant $SuiteTenantObj
     */
    public function triggerUpdatedEvent($SuiteTenantObj)
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
            $this->ObjectEnabledForEvents($SuiteTenantObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\SuiteTenantUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\SuiteTenantUpdatedEvent(
                    $SuiteTenantObj,
                    [
                        'event_trigger_message'        => 'Called from SuiteTenantRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($SuiteTenantObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param SuiteTenant $SuiteTenantObj
     */
    public function triggerDeletedEvent($SuiteTenantObj)
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
            $this->ObjectEnabledForEvents($SuiteTenantObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\SuiteTenantDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\SuiteTenantDeletedEvent(
                    $SuiteTenantObj,
                    [
                        'event_trigger_message'        => 'Called from SuiteTenantRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($SuiteTenantObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
