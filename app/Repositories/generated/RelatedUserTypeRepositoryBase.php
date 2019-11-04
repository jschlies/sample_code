<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\RelatedUserType;
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
 * Class RelatedUserTypeRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method RelatedUserType findWithoutFail($id, $columns = ['*']) desc
 * @method RelatedUserType find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method RelatedUserType first($columns = ['*']) desc
 */
class RelatedUserTypeRepositoryBase extends BaseRepository
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
        return RelatedUserType::class;
    }

    /**
     * Save a new RelatedUserType in repository
     *
     * @param array $attributes
     * @return RelatedUserType
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $RelatedUserTypeObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ( $RelatedUserTypeObj instanceof Model)
        {
            $this->triggerCreatedEvent($RelatedUserTypeObj);
        }
        return $RelatedUserTypeObj;
    }

    /**
     * Update a RelatedUserType entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return RelatedUserType
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $RelatedUserTypeObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($RelatedUserTypeObj instanceof Model)
        {
            $this->triggerUpdatedEvent($RelatedUserTypeObj);
        }
        return $RelatedUserTypeObj;
    }

    /**
     * Delete a RelatedUserType entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $RelatedUserTypeObj = $this->find($id);
        $result = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($RelatedUserTypeObj instanceof Model)
        {
            $this->triggerDeletedEvent($RelatedUserTypeObj);
        }

        return $result;
    }

    /**
     * @param RelatedUserType $RelatedUserTypeObj
     */
    public function triggerCreatedEvent($RelatedUserTypeObj)
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
            $this->ObjectEnabledForEvents($RelatedUserTypeObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\RelatedUserTypeCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                    new \App\Waypoint\Events\RelatedUserTypeCreatedEvent(
                    $RelatedUserTypeObj,
                    [
                        'event_trigger_message'        => 'Called from RelatedUserTypeRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($RelatedUserTypeObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param RelatedUserType $RelatedUserTypeObj
     */
    public function triggerUpdatedEvent($RelatedUserTypeObj)
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
            $this->ObjectEnabledForEvents($RelatedUserTypeObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\RelatedUserTypeUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\RelatedUserTypeUpdatedEvent(
                    $RelatedUserTypeObj,
                    [
                        'event_trigger_message'        => 'Called from RelatedUserTypeRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($RelatedUserTypeObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param RelatedUserType $RelatedUserTypeObj
     */
    public function triggerDeletedEvent($RelatedUserTypeObj)
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
            $this->ObjectEnabledForEvents($RelatedUserTypeObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\RelatedUserTypeDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\RelatedUserTypeDeletedEvent(
                    $RelatedUserTypeObj,
                    [
                        'event_trigger_message'        => 'Called from RelatedUserTypeRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($RelatedUserTypeObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
