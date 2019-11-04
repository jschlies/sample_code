<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\CalculatedFieldVariable;
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
 * Class CalculatedFieldVariableRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method CalculatedFieldVariable findWithoutFail($id, $columns = ['*']) desc
 * @method CalculatedFieldVariable find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method CalculatedFieldVariable first($columns = ['*']) desc
 */
class CalculatedFieldVariableRepositoryBase extends BaseRepository
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
        return CalculatedFieldVariable::class;
    }

    /**
     * Save a new CalculatedFieldVariable in repository
     *
     * @param array $attributes
     * @return CalculatedFieldVariable
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $CalculatedFieldVariableObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($CalculatedFieldVariableObj instanceof Model)
        {
            $this->triggerCreatedEvent($CalculatedFieldVariableObj);
        }
        return $CalculatedFieldVariableObj;
    }

    /**
     * Update a CalculatedFieldVariable entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return CalculatedFieldVariable
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $CalculatedFieldVariableObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($CalculatedFieldVariableObj instanceof Model)
        {
            $this->triggerUpdatedEvent($CalculatedFieldVariableObj);
        }
        return $CalculatedFieldVariableObj;
    }

    /**
     * Delete a CalculatedFieldVariable entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $CalculatedFieldVariableObj = $this->find($id);
        $result                     = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($CalculatedFieldVariableObj instanceof Model)
        {
            $this->triggerDeletedEvent($CalculatedFieldVariableObj);
        }

        return $result;
    }

    /**
     * @param CalculatedFieldVariable $CalculatedFieldVariableObj
     */
    public function triggerCreatedEvent($CalculatedFieldVariableObj)
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
            $this->ObjectEnabledForEvents($CalculatedFieldVariableObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\CalculatedFieldVariableCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\CalculatedFieldVariableCreatedEvent(
                    $CalculatedFieldVariableObj,
                    [
                        'event_trigger_message'        => 'Called from CalculatedFieldVariableRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($CalculatedFieldVariableObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param CalculatedFieldVariable $CalculatedFieldVariableObj
     */
    public function triggerUpdatedEvent($CalculatedFieldVariableObj)
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
            $this->ObjectEnabledForEvents($CalculatedFieldVariableObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\CalculatedFieldVariableUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\CalculatedFieldVariableUpdatedEvent(
                      $CalculatedFieldVariableObj,
                      [
                          'event_trigger_message'        => 'Called from CalculatedFieldVariableRepositoryBase',
                          'event_trigger_id'             => waypoint_generate_uuid(),
                          'event_trigger_class'          => self::class,
                          'event_trigger_class_instance' => get_class($this),
                          'event_trigger_object_class'   => get_class($CalculatedFieldVariableObj),
                          'event_trigger_absolute_class' => __CLASS__,
                          'event_trigger_file'           => __FILE__,
                          'event_trigger_line'           => __LINE__,
                      ]
                  )
            );
        }
    }

    /**
     * @param CalculatedFieldVariable $CalculatedFieldVariableObj
     */
    public function triggerDeletedEvent($CalculatedFieldVariableObj)
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
            $this->ObjectEnabledForEvents($CalculatedFieldVariableObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\CalculatedFieldVariableDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\CalculatedFieldVariableDeletedEvent(
                    $CalculatedFieldVariableObj,
                    [
                        'event_trigger_message'        => 'Called from CalculatedFieldVariableRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($CalculatedFieldVariableObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
