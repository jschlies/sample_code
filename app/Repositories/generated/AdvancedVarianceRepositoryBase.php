<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\AdvancedVariance;
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
 * Class AdvancedVarianceRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method AdvancedVariance findWithoutFail($id, $columns = ['*']) desc
 * @method AdvancedVariance find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method AdvancedVariance first($columns = ['*']) desc
 */
class AdvancedVarianceRepositoryBase extends BaseRepository
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
        return AdvancedVariance::class;
    }

    /**
     * Save a new AdvancedVariance in repository
     *
     * @param array $attributes
     * @return AdvancedVariance
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $AdvancedVarianceObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($AdvancedVarianceObj instanceof Model)
        {
            $this->triggerCreatedEvent($AdvancedVarianceObj);
        }
        return $AdvancedVarianceObj;
    }

    /**
     * Update a AdvancedVariance entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return AdvancedVariance
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $AdvancedVarianceObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($AdvancedVarianceObj instanceof Model)
        {
            $this->triggerUpdatedEvent($AdvancedVarianceObj);
        }
        return $AdvancedVarianceObj;
    }

    /**
     * Delete a AdvancedVariance entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $AdvancedVarianceObj = $this->find($id);
        $result              = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($AdvancedVarianceObj instanceof Model)
        {
            $this->triggerDeletedEvent($AdvancedVarianceObj);
        }

        return $result;
    }

    /**
     * @param AdvancedVariance $AdvancedVarianceObj
     */
    public function triggerCreatedEvent($AdvancedVarianceObj)
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
            $this->ObjectEnabledForEvents($AdvancedVarianceObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AdvancedVarianceCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\AdvancedVarianceCreatedEvent(
                    $AdvancedVarianceObj,
                    [
                        'event_trigger_message'        => 'Called from AdvancedVarianceRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($AdvancedVarianceObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param AdvancedVariance $AdvancedVarianceObj
     */
    public function triggerUpdatedEvent($AdvancedVarianceObj)
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
            $this->ObjectEnabledForEvents($AdvancedVarianceObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AdvancedVarianceUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\AdvancedVarianceUpdatedEvent(
                      $AdvancedVarianceObj,
                      [
                          'event_trigger_message'        => 'Called from AdvancedVarianceRepositoryBase',
                          'event_trigger_id'             => waypoint_generate_uuid(),
                          'event_trigger_class'          => self::class,
                          'event_trigger_class_instance' => get_class($this),
                          'event_trigger_object_class'   => get_class($AdvancedVarianceObj),
                          'event_trigger_absolute_class' => __CLASS__,
                          'event_trigger_file'           => __FILE__,
                          'event_trigger_line'           => __LINE__,
                      ]
                  )
            );
        }
    }

    /**
     * @param AdvancedVariance $AdvancedVarianceObj
     */
    public function triggerDeletedEvent($AdvancedVarianceObj)
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
            $this->ObjectEnabledForEvents($AdvancedVarianceObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AdvancedVarianceDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\AdvancedVarianceDeletedEvent(
                    $AdvancedVarianceObj,
                    [
                        'event_trigger_message'        => 'Called from AdvancedVarianceRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($AdvancedVarianceObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
