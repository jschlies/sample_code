<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\AdvancedVarianceThreshold;
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
 * Class AdvancedVarianceThresholdRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method AdvancedVarianceThreshold findWithoutFail($id, $columns = ['*']) desc
 * @method AdvancedVarianceThreshold find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method AdvancedVarianceThreshold first($columns = ['*']) desc
 */
class AdvancedVarianceThresholdRepositoryBase extends BaseRepository
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
        return AdvancedVarianceThreshold::class;
    }

    /**
     * Save a new AdvancedVarianceThreshold in repository
     *
     * @param array $attributes
     * @return AdvancedVarianceThreshold
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $AdvancedVarianceThresholdObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($AdvancedVarianceThresholdObj instanceof Model)
        {
            $this->triggerCreatedEvent($AdvancedVarianceThresholdObj);
        }
        return $AdvancedVarianceThresholdObj;
    }

    /**
     * Update a AdvancedVarianceThreshold entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return AdvancedVarianceThreshold
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $AdvancedVarianceThresholdObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($AdvancedVarianceThresholdObj instanceof Model)
        {
            $this->triggerUpdatedEvent($AdvancedVarianceThresholdObj);
        }
        return $AdvancedVarianceThresholdObj;
    }

    /**
     * Delete a AdvancedVarianceThreshold entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $AdvancedVarianceThresholdObj = $this->find($id);
        $result                       = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($AdvancedVarianceThresholdObj instanceof Model)
        {
            $this->triggerDeletedEvent($AdvancedVarianceThresholdObj);
        }

        return $result;
    }

    /**
     * @param AdvancedVarianceThreshold $AdvancedVarianceThresholdObj
     */
    public function triggerCreatedEvent($AdvancedVarianceThresholdObj)
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
            $this->ObjectEnabledForEvents($AdvancedVarianceThresholdObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AdvancedVarianceThresholdCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\AdvancedVarianceThresholdCreatedEvent(
                    $AdvancedVarianceThresholdObj,
                    [
                        'event_trigger_message'        => 'Called from AdvancedVarianceThresholdRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($AdvancedVarianceThresholdObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param AdvancedVarianceThreshold $AdvancedVarianceThresholdObj
     */
    public function triggerUpdatedEvent($AdvancedVarianceThresholdObj)
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
            $this->ObjectEnabledForEvents($AdvancedVarianceThresholdObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AdvancedVarianceThresholdUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\AdvancedVarianceThresholdUpdatedEvent(
                      $AdvancedVarianceThresholdObj,
                      [
                          'event_trigger_message'        => 'Called from AdvancedVarianceThresholdRepositoryBase',
                          'event_trigger_id'             => waypoint_generate_uuid(),
                          'event_trigger_class'          => self::class,
                          'event_trigger_class_instance' => get_class($this),
                          'event_trigger_object_class'   => get_class($AdvancedVarianceThresholdObj),
                          'event_trigger_absolute_class' => __CLASS__,
                          'event_trigger_file'           => __FILE__,
                          'event_trigger_line'           => __LINE__,
                      ]
                  )
            );
        }
    }

    /**
     * @param AdvancedVarianceThreshold $AdvancedVarianceThresholdObj
     */
    public function triggerDeletedEvent($AdvancedVarianceThresholdObj)
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
            $this->ObjectEnabledForEvents($AdvancedVarianceThresholdObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AdvancedVarianceThresholdDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\AdvancedVarianceThresholdDeletedEvent(
                    $AdvancedVarianceThresholdObj,
                    [
                        'event_trigger_message'        => 'Called from AdvancedVarianceThresholdRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($AdvancedVarianceThresholdObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
