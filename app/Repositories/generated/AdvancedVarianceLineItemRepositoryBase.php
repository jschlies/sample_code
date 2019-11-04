<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\AdvancedVarianceLineItem;
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
 * Class AdvancedVarianceLineItemRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method AdvancedVarianceLineItem findWithoutFail($id, $columns = ['*']) desc
 * @method AdvancedVarianceLineItem find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method AdvancedVarianceLineItem first($columns = ['*']) desc
 */
class AdvancedVarianceLineItemRepositoryBase extends BaseRepository
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
        return AdvancedVarianceLineItem::class;
    }

    /**
     * Save a new AdvancedVarianceLineItem in repository
     *
     * @param array $attributes
     * @return AdvancedVarianceLineItem
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $AdvancedVarianceLineItemObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($AdvancedVarianceLineItemObj instanceof Model)
        {
            $this->triggerCreatedEvent($AdvancedVarianceLineItemObj);
        }
        return $AdvancedVarianceLineItemObj;
    }

    /**
     * Update a AdvancedVarianceLineItem entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return AdvancedVarianceLineItem
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $AdvancedVarianceLineItemObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($AdvancedVarianceLineItemObj instanceof Model)
        {
            $this->triggerUpdatedEvent($AdvancedVarianceLineItemObj);
        }
        return $AdvancedVarianceLineItemObj;
    }

    /**
     * Delete a AdvancedVarianceLineItem entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $AdvancedVarianceLineItemObj = $this->find($id);
        $result                      = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($AdvancedVarianceLineItemObj instanceof Model)
        {
            $this->triggerDeletedEvent($AdvancedVarianceLineItemObj);
        }

        return $result;
    }

    /**
     * @param AdvancedVarianceLineItem $AdvancedVarianceLineItemObj
     */
    public function triggerCreatedEvent($AdvancedVarianceLineItemObj)
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
            $this->ObjectEnabledForEvents($AdvancedVarianceLineItemObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AdvancedVarianceLineItemCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\AdvancedVarianceLineItemCreatedEvent(
                    $AdvancedVarianceLineItemObj,
                    [
                        'event_trigger_message'        => 'Called from AdvancedVarianceLineItemRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($AdvancedVarianceLineItemObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param AdvancedVarianceLineItem $AdvancedVarianceLineItemObj
     */
    public function triggerUpdatedEvent($AdvancedVarianceLineItemObj)
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
            $this->ObjectEnabledForEvents($AdvancedVarianceLineItemObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AdvancedVarianceLineItemUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\AdvancedVarianceLineItemUpdatedEvent(
                      $AdvancedVarianceLineItemObj,
                      [
                          'event_trigger_message'        => 'Called from AdvancedVarianceLineItemRepositoryBase',
                          'event_trigger_id'             => waypoint_generate_uuid(),
                          'event_trigger_class'          => self::class,
                          'event_trigger_class_instance' => get_class($this),
                          'event_trigger_object_class'   => get_class($AdvancedVarianceLineItemObj),
                          'event_trigger_absolute_class' => __CLASS__,
                          'event_trigger_file'           => __FILE__,
                          'event_trigger_line'           => __LINE__,
                      ]
                  )
            );
        }
    }

    /**
     * @param AdvancedVarianceLineItem $AdvancedVarianceLineItemObj
     */
    public function triggerDeletedEvent($AdvancedVarianceLineItemObj)
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
            $this->ObjectEnabledForEvents($AdvancedVarianceLineItemObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AdvancedVarianceLineItemDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\AdvancedVarianceLineItemDeletedEvent(
                    $AdvancedVarianceLineItemObj,
                    [
                        'event_trigger_message'        => 'Called from AdvancedVarianceLineItemRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($AdvancedVarianceLineItemObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
