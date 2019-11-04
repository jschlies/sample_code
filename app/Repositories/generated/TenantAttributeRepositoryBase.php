<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\TenantAttribute;
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
 * Class TenantAttributeRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method TenantAttribute findWithoutFail($id, $columns = ['*']) desc
 * @method TenantAttribute find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method TenantAttribute first($columns = ['*']) desc
 */
class TenantAttributeRepositoryBase extends BaseRepository
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
        return TenantAttribute::class;
    }

    /**
     * Save a new TenantAttribute in repository
     *
     * @param array $attributes
     * @return TenantAttribute
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $TenantAttributeObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ( $TenantAttributeObj instanceof Model)
        {
            $this->triggerCreatedEvent($TenantAttributeObj);
        }
        return $TenantAttributeObj;
    }

    /**
     * Update a TenantAttribute entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return TenantAttribute
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $TenantAttributeObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($TenantAttributeObj instanceof Model)
        {
            $this->triggerUpdatedEvent($TenantAttributeObj);
        }
        return $TenantAttributeObj;
    }

    /**
     * Delete a TenantAttribute entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $TenantAttributeObj = $this->find($id);
        $result = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($TenantAttributeObj instanceof Model)
        {
            $this->triggerDeletedEvent($TenantAttributeObj);
        }

        return $result;
    }

    /**
     * @param TenantAttribute $TenantAttributeObj
     */
    public function triggerCreatedEvent($TenantAttributeObj)
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
            $this->ObjectEnabledForEvents($TenantAttributeObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\TenantAttributeCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                    new \App\Waypoint\Events\TenantAttributeCreatedEvent(
                    $TenantAttributeObj,
                    [
                        'event_trigger_message'        => 'Called from TenantAttributeRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($TenantAttributeObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param TenantAttribute $TenantAttributeObj
     */
    public function triggerUpdatedEvent($TenantAttributeObj)
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
            $this->ObjectEnabledForEvents($TenantAttributeObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\TenantAttributeUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\TenantAttributeUpdatedEvent(
                    $TenantAttributeObj,
                    [
                        'event_trigger_message'        => 'Called from TenantAttributeRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($TenantAttributeObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param TenantAttribute $TenantAttributeObj
     */
    public function triggerDeletedEvent($TenantAttributeObj)
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
            $this->ObjectEnabledForEvents($TenantAttributeObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\TenantAttributeDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\TenantAttributeDeletedEvent(
                    $TenantAttributeObj,
                    [
                        'event_trigger_message'        => 'Called from TenantAttributeRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($TenantAttributeObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
