<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\TenantTenantAttribute;
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
 * Class TenantTenantAttributeRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method TenantTenantAttribute findWithoutFail($id, $columns = ['*']) desc
 * @method TenantTenantAttribute find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method TenantTenantAttribute first($columns = ['*']) desc
 */
class TenantTenantAttributeRepositoryBase extends BaseRepository
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
        return TenantTenantAttribute::class;
    }

    /**
     * Save a new TenantTenantAttribute in repository
     *
     * @param array $attributes
     * @return TenantTenantAttribute
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $TenantTenantAttributeObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ( $TenantTenantAttributeObj instanceof Model)
        {
            $this->triggerCreatedEvent($TenantTenantAttributeObj);
        }
        return $TenantTenantAttributeObj;
    }

    /**
     * Update a TenantTenantAttribute entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return TenantTenantAttribute
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $TenantTenantAttributeObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($TenantTenantAttributeObj instanceof Model)
        {
            $this->triggerUpdatedEvent($TenantTenantAttributeObj);
        }
        return $TenantTenantAttributeObj;
    }

    /**
     * Delete a TenantTenantAttribute entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $TenantTenantAttributeObj = $this->find($id);
        $result = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($TenantTenantAttributeObj instanceof Model)
        {
            $this->triggerDeletedEvent($TenantTenantAttributeObj);
        }

        return $result;
    }

    /**
     * @param TenantTenantAttribute $TenantTenantAttributeObj
     */
    public function triggerCreatedEvent($TenantTenantAttributeObj)
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
            $this->ObjectEnabledForEvents($TenantTenantAttributeObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\TenantTenantAttributeCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                    new \App\Waypoint\Events\TenantTenantAttributeCreatedEvent(
                    $TenantTenantAttributeObj,
                    [
                        'event_trigger_message'        => 'Called from TenantTenantAttributeRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($TenantTenantAttributeObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param TenantTenantAttribute $TenantTenantAttributeObj
     */
    public function triggerUpdatedEvent($TenantTenantAttributeObj)
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
            $this->ObjectEnabledForEvents($TenantTenantAttributeObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\TenantTenantAttributeUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\TenantTenantAttributeUpdatedEvent(
                    $TenantTenantAttributeObj,
                    [
                        'event_trigger_message'        => 'Called from TenantTenantAttributeRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($TenantTenantAttributeObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param TenantTenantAttribute $TenantTenantAttributeObj
     */
    public function triggerDeletedEvent($TenantTenantAttributeObj)
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
            $this->ObjectEnabledForEvents($TenantTenantAttributeObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\TenantTenantAttributeDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\TenantTenantAttributeDeletedEvent(
                    $TenantTenantAttributeObj,
                    [
                        'event_trigger_message'        => 'Called from TenantTenantAttributeRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($TenantTenantAttributeObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
