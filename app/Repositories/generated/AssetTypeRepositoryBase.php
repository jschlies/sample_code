<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\AssetType;
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
 * Class AssetTypeRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method AssetType findWithoutFail($id, $columns = ['*']) desc
 * @method AssetType find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method AssetType first($columns = ['*']) desc
 */
class AssetTypeRepositoryBase extends BaseRepository
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
        return AssetType::class;
    }

    /**
     * Save a new AssetType in repository
     *
     * @param array $attributes
     * @return AssetType
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $AssetTypeObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($AssetTypeObj instanceof Model)
        {
            $this->triggerCreatedEvent($AssetTypeObj);
        }
        return $AssetTypeObj;
    }

    /**
     * Update a AssetType entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return AssetType
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $AssetTypeObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($AssetTypeObj instanceof Model)
        {
            $this->triggerUpdatedEvent($AssetTypeObj);
        }
        return $AssetTypeObj;
    }

    /**
     * Delete a AssetType entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $AssetTypeObj = $this->find($id);
        $result       = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($AssetTypeObj instanceof Model)
        {
            $this->triggerDeletedEvent($AssetTypeObj);
        }

        return $result;
    }

    /**
     * @param AssetType $AssetTypeObj
     */
    public function triggerCreatedEvent($AssetTypeObj)
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
            $this->ObjectEnabledForEvents($AssetTypeObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AssetTypeCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\AssetTypeCreatedEvent(
                    $AssetTypeObj,
                    [
                        'event_trigger_message'        => 'Called from AssetTypeRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($AssetTypeObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param AssetType $AssetTypeObj
     */
    public function triggerUpdatedEvent($AssetTypeObj)
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
            $this->ObjectEnabledForEvents($AssetTypeObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AssetTypeUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\AssetTypeUpdatedEvent(
                      $AssetTypeObj,
                      [
                          'event_trigger_message'        => 'Called from AssetTypeRepositoryBase',
                          'event_trigger_id'             => waypoint_generate_uuid(),
                          'event_trigger_class'          => self::class,
                          'event_trigger_class_instance' => get_class($this),
                          'event_trigger_object_class'   => get_class($AssetTypeObj),
                          'event_trigger_absolute_class' => __CLASS__,
                          'event_trigger_file'           => __FILE__,
                          'event_trigger_line'           => __LINE__,
                      ]
                  )
            );
        }
    }

    /**
     * @param AssetType $AssetTypeObj
     */
    public function triggerDeletedEvent($AssetTypeObj)
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
            $this->ObjectEnabledForEvents($AssetTypeObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AssetTypeDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\AssetTypeDeletedEvent(
                    $AssetTypeObj,
                    [
                        'event_trigger_message'        => 'Called from AssetTypeRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($AssetTypeObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
