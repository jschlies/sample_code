<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\CalculatedFieldEquation;
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
 * Class CalculatedFieldEquationRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method CalculatedFieldEquation findWithoutFail($id, $columns = ['*']) desc
 * @method CalculatedFieldEquation find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method CalculatedFieldEquation first($columns = ['*']) desc
 */
class CalculatedFieldEquationRepositoryBase extends BaseRepository
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
        return CalculatedFieldEquation::class;
    }

    /**
     * Save a new CalculatedFieldEquation in repository
     *
     * @param array $attributes
     * @return CalculatedFieldEquation
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $CalculatedFieldEquationObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($CalculatedFieldEquationObj instanceof Model)
        {
            $this->triggerCreatedEvent($CalculatedFieldEquationObj);
        }
        return $CalculatedFieldEquationObj;
    }

    /**
     * Update a CalculatedFieldEquation entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return CalculatedFieldEquation
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $CalculatedFieldEquationObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($CalculatedFieldEquationObj instanceof Model)
        {
            $this->triggerUpdatedEvent($CalculatedFieldEquationObj);
        }
        return $CalculatedFieldEquationObj;
    }

    /**
     * Delete a CalculatedFieldEquation entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $CalculatedFieldEquationObj = $this->find($id);
        $result                     = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($CalculatedFieldEquationObj instanceof Model)
        {
            $this->triggerDeletedEvent($CalculatedFieldEquationObj);
        }

        return $result;
    }

    /**
     * @param CalculatedFieldEquation $CalculatedFieldEquationObj
     */
    public function triggerCreatedEvent($CalculatedFieldEquationObj)
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
            $this->ObjectEnabledForEvents($CalculatedFieldEquationObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\CalculatedFieldEquationCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\CalculatedFieldEquationCreatedEvent(
                    $CalculatedFieldEquationObj,
                    [
                        'event_trigger_message'        => 'Called from CalculatedFieldEquationRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($CalculatedFieldEquationObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param CalculatedFieldEquation $CalculatedFieldEquationObj
     */
    public function triggerUpdatedEvent($CalculatedFieldEquationObj)
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
            $this->ObjectEnabledForEvents($CalculatedFieldEquationObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\CalculatedFieldEquationUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\CalculatedFieldEquationUpdatedEvent(
                      $CalculatedFieldEquationObj,
                      [
                          'event_trigger_message'        => 'Called from CalculatedFieldEquationRepositoryBase',
                          'event_trigger_id'             => waypoint_generate_uuid(),
                          'event_trigger_class'          => self::class,
                          'event_trigger_class_instance' => get_class($this),
                          'event_trigger_object_class'   => get_class($CalculatedFieldEquationObj),
                          'event_trigger_absolute_class' => __CLASS__,
                          'event_trigger_file'           => __FILE__,
                          'event_trigger_line'           => __LINE__,
                      ]
                  )
            );
        }
    }

    /**
     * @param CalculatedFieldEquation $CalculatedFieldEquationObj
     */
    public function triggerDeletedEvent($CalculatedFieldEquationObj)
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
            $this->ObjectEnabledForEvents($CalculatedFieldEquationObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\CalculatedFieldEquationDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\CalculatedFieldEquationDeletedEvent(
                    $CalculatedFieldEquationObj,
                    [
                        'event_trigger_message'        => 'Called from CalculatedFieldEquationRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($CalculatedFieldEquationObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
