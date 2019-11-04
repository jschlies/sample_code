<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\CalculatedField;
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
 * Class CalculatedFieldRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method CalculatedField findWithoutFail($id, $columns = ['*']) desc
 * @method CalculatedField find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method CalculatedField first($columns = ['*']) desc
 */
class CalculatedFieldRepositoryBase extends BaseRepository
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
        return CalculatedField::class;
    }

    /**
     * Save a new CalculatedField in repository
     *
     * @param array $attributes
     * @return CalculatedField
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $CalculatedFieldObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($CalculatedFieldObj instanceof Model)
        {
            $this->triggerCreatedEvent($CalculatedFieldObj);
        }
        return $CalculatedFieldObj;
    }

    /**
     * Update a CalculatedField entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return CalculatedField
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $CalculatedFieldObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($CalculatedFieldObj instanceof Model)
        {
            $this->triggerUpdatedEvent($CalculatedFieldObj);
        }
        return $CalculatedFieldObj;
    }

    /**
     * Delete a CalculatedField entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $CalculatedFieldObj = $this->find($id);
        $result             = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($CalculatedFieldObj instanceof Model)
        {
            $this->triggerDeletedEvent($CalculatedFieldObj);
        }

        return $result;
    }

    /**
     * @param CalculatedField $CalculatedFieldObj
     */
    public function triggerCreatedEvent($CalculatedFieldObj)
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
            $this->ObjectEnabledForEvents($CalculatedFieldObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\CalculatedFieldCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\CalculatedFieldCreatedEvent(
                    $CalculatedFieldObj,
                    [
                        'event_trigger_message'        => 'Called from CalculatedFieldRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($CalculatedFieldObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param CalculatedField $CalculatedFieldObj
     */
    public function triggerUpdatedEvent($CalculatedFieldObj)
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
            $this->ObjectEnabledForEvents($CalculatedFieldObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\CalculatedFieldUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\CalculatedFieldUpdatedEvent(
                      $CalculatedFieldObj,
                      [
                          'event_trigger_message'        => 'Called from CalculatedFieldRepositoryBase',
                          'event_trigger_id'             => waypoint_generate_uuid(),
                          'event_trigger_class'          => self::class,
                          'event_trigger_class_instance' => get_class($this),
                          'event_trigger_object_class'   => get_class($CalculatedFieldObj),
                          'event_trigger_absolute_class' => __CLASS__,
                          'event_trigger_file'           => __FILE__,
                          'event_trigger_line'           => __LINE__,
                      ]
                  )
            );
        }
    }

    /**
     * @param CalculatedField $CalculatedFieldObj
     */
    public function triggerDeletedEvent($CalculatedFieldObj)
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
            $this->ObjectEnabledForEvents($CalculatedFieldObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\CalculatedFieldDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\CalculatedFieldDeletedEvent(
                    $CalculatedFieldObj,
                    [
                        'event_trigger_message'        => 'Called from CalculatedFieldRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($CalculatedFieldObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
