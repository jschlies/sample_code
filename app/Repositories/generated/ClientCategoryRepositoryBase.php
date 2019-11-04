<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\ClientCategory;
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
 * Class ClientCategoryRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method ClientCategory findWithoutFail($id, $columns = ['*']) desc
 * @method ClientCategory find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method ClientCategory first($columns = ['*']) desc
 */
class ClientCategoryRepositoryBase extends BaseRepository
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
        return ClientCategory::class;
    }

    /**
     * Save a new ClientCategory in repository
     *
     * @param array $attributes
     * @return ClientCategory
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $ClientCategoryObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($ClientCategoryObj instanceof Model)
        {
            $this->triggerCreatedEvent($ClientCategoryObj);
        }
        return $ClientCategoryObj;
    }

    /**
     * Update a ClientCategory entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return ClientCategory
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $ClientCategoryObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($ClientCategoryObj instanceof Model)
        {
            $this->triggerUpdatedEvent($ClientCategoryObj);
        }
        return $ClientCategoryObj;
    }

    /**
     * Delete a ClientCategory entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $ClientCategoryObj = $this->find($id);
        $result            = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($ClientCategoryObj instanceof Model)
        {
            $this->triggerDeletedEvent($ClientCategoryObj);
        }

        return $result;
    }

    /**
     * @param ClientCategory $ClientCategoryObj
     */
    public function triggerCreatedEvent($ClientCategoryObj)
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
            $this->ObjectEnabledForEvents($ClientCategoryObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\ClientCategoryCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\ClientCategoryCreatedEvent(
                    $ClientCategoryObj,
                    [
                        'event_trigger_message'        => 'Called from ClientCategoryRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($ClientCategoryObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param ClientCategory $ClientCategoryObj
     */
    public function triggerUpdatedEvent($ClientCategoryObj)
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
            $this->ObjectEnabledForEvents($ClientCategoryObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\ClientCategoryUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\ClientCategoryUpdatedEvent(
                      $ClientCategoryObj,
                      [
                          'event_trigger_message'        => 'Called from ClientCategoryRepositoryBase',
                          'event_trigger_id'             => waypoint_generate_uuid(),
                          'event_trigger_class'          => self::class,
                          'event_trigger_class_instance' => get_class($this),
                          'event_trigger_object_class'   => get_class($ClientCategoryObj),
                          'event_trigger_absolute_class' => __CLASS__,
                          'event_trigger_file'           => __FILE__,
                          'event_trigger_line'           => __LINE__,
                      ]
                  )
            );
        }
    }

    /**
     * @param ClientCategory $ClientCategoryObj
     */
    public function triggerDeletedEvent($ClientCategoryObj)
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
            $this->ObjectEnabledForEvents($ClientCategoryObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\ClientCategoryDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\ClientCategoryDeletedEvent(
                    $ClientCategoryObj,
                    [
                        'event_trigger_message'        => 'Called from ClientCategoryRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($ClientCategoryObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
