<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\EntityTag;
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
 * Class EntityTagRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method EntityTag findWithoutFail($id, $columns = ['*']) desc
 * @method EntityTag find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method EntityTag first($columns = ['*']) desc
 */
class EntityTagRepositoryBase extends BaseRepository
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
        return EntityTag::class;
    }

    /**
     * Save a new EntityTag in repository
     *
     * @param array $attributes
     * @return EntityTag
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $EntityTagObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ( $EntityTagObj instanceof Model)
        {
            $this->triggerCreatedEvent($EntityTagObj);
        }
        return $EntityTagObj;
    }

    /**
     * Update a EntityTag entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return EntityTag
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $EntityTagObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($EntityTagObj instanceof Model)
        {
            $this->triggerUpdatedEvent($EntityTagObj);
        }
        return $EntityTagObj;
    }

    /**
     * Delete a EntityTag entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $EntityTagObj = $this->find($id);
        $result = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($EntityTagObj instanceof Model)
        {
            $this->triggerDeletedEvent($EntityTagObj);
        }

        return $result;
    }

    /**
     * @param EntityTag $EntityTagObj
     */
    public function triggerCreatedEvent($EntityTagObj)
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
            $this->ObjectEnabledForEvents($EntityTagObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\EntityTagCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                    new \App\Waypoint\Events\EntityTagCreatedEvent(
                    $EntityTagObj,
                    [
                        'event_trigger_message'        => 'Called from EntityTagRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($EntityTagObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param EntityTag $EntityTagObj
     */
    public function triggerUpdatedEvent($EntityTagObj)
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
            $this->ObjectEnabledForEvents($EntityTagObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\EntityTagUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\EntityTagUpdatedEvent(
                    $EntityTagObj,
                    [
                        'event_trigger_message'        => 'Called from EntityTagRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($EntityTagObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param EntityTag $EntityTagObj
     */
    public function triggerDeletedEvent($EntityTagObj)
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
            $this->ObjectEnabledForEvents($EntityTagObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\EntityTagDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\EntityTagDeletedEvent(
                    $EntityTagObj,
                    [
                        'event_trigger_message'        => 'Called from EntityTagRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($EntityTagObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
