<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\AdvancedVarianceExplanationType;
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
 * Class AdvancedVarianceExplanationTypeRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method AdvancedVarianceExplanationType findWithoutFail($id, $columns = ['*']) desc
 * @method AdvancedVarianceExplanationType find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method AdvancedVarianceExplanationType first($columns = ['*']) desc
 */
class AdvancedVarianceExplanationTypeRepositoryBase extends BaseRepository
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
        return AdvancedVarianceExplanationType::class;
    }

    /**
     * Save a new AdvancedVarianceExplanationType in repository
     *
     * @param array $attributes
     * @return AdvancedVarianceExplanationType
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $AdvancedVarianceExplanationTypeObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($AdvancedVarianceExplanationTypeObj instanceof Model)
        {
            $this->triggerCreatedEvent($AdvancedVarianceExplanationTypeObj);
        }
        return $AdvancedVarianceExplanationTypeObj;
    }

    /**
     * Update a AdvancedVarianceExplanationType entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return AdvancedVarianceExplanationType
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $AdvancedVarianceExplanationTypeObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($AdvancedVarianceExplanationTypeObj instanceof Model)
        {
            $this->triggerUpdatedEvent($AdvancedVarianceExplanationTypeObj);
        }
        return $AdvancedVarianceExplanationTypeObj;
    }

    /**
     * Delete a AdvancedVarianceExplanationType entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $AdvancedVarianceExplanationTypeObj = $this->find($id);
        $result                             = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($AdvancedVarianceExplanationTypeObj instanceof Model)
        {
            $this->triggerDeletedEvent($AdvancedVarianceExplanationTypeObj);
        }

        return $result;
    }

    /**
     * @param AdvancedVarianceExplanationType $AdvancedVarianceExplanationTypeObj
     */
    public function triggerCreatedEvent($AdvancedVarianceExplanationTypeObj)
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
            $this->ObjectEnabledForEvents($AdvancedVarianceExplanationTypeObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AdvancedVarianceExplanationTypeCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\AdvancedVarianceExplanationTypeCreatedEvent(
                    $AdvancedVarianceExplanationTypeObj,
                    [
                        'event_trigger_message'        => 'Called from AdvancedVarianceExplanationTypeRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($AdvancedVarianceExplanationTypeObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param AdvancedVarianceExplanationType $AdvancedVarianceExplanationTypeObj
     */
    public function triggerUpdatedEvent($AdvancedVarianceExplanationTypeObj)
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
            $this->ObjectEnabledForEvents($AdvancedVarianceExplanationTypeObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AdvancedVarianceExplanationTypeUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\AdvancedVarianceExplanationTypeUpdatedEvent(
                      $AdvancedVarianceExplanationTypeObj,
                      [
                          'event_trigger_message'        => 'Called from AdvancedVarianceExplanationTypeRepositoryBase',
                          'event_trigger_id'             => waypoint_generate_uuid(),
                          'event_trigger_class'          => self::class,
                          'event_trigger_class_instance' => get_class($this),
                          'event_trigger_object_class'   => get_class($AdvancedVarianceExplanationTypeObj),
                          'event_trigger_absolute_class' => __CLASS__,
                          'event_trigger_file'           => __FILE__,
                          'event_trigger_line'           => __LINE__,
                      ]
                  )
            );
        }
    }

    /**
     * @param AdvancedVarianceExplanationType $AdvancedVarianceExplanationTypeObj
     */
    public function triggerDeletedEvent($AdvancedVarianceExplanationTypeObj)
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
            $this->ObjectEnabledForEvents($AdvancedVarianceExplanationTypeObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AdvancedVarianceExplanationTypeDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\AdvancedVarianceExplanationTypeDeletedEvent(
                    $AdvancedVarianceExplanationTypeObj,
                    [
                        'event_trigger_message'        => 'Called from AdvancedVarianceExplanationTypeRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($AdvancedVarianceExplanationTypeObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
