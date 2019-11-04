<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\NativeAccountTypeTrailer;
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
 * Class NativeAccountTypeTrailerRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method NativeAccountTypeTrailer findWithoutFail($id, $columns = ['*']) desc
 * @method NativeAccountTypeTrailer find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method NativeAccountTypeTrailer first($columns = ['*']) desc
 */
class NativeAccountTypeTrailerRepositoryBase extends BaseRepository
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
        return NativeAccountTypeTrailer::class;
    }

    /**
     * Save a new NativeAccountTypeTrailer in repository
     *
     * @param array $attributes
     * @return NativeAccountTypeTrailer
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $NativeAccountTypeTrailerObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ( $NativeAccountTypeTrailerObj instanceof Model)
        {
            $this->triggerCreatedEvent($NativeAccountTypeTrailerObj);
        }
        return $NativeAccountTypeTrailerObj;
    }

    /**
     * Update a NativeAccountTypeTrailer entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return NativeAccountTypeTrailer
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $NativeAccountTypeTrailerObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($NativeAccountTypeTrailerObj instanceof Model)
        {
            $this->triggerUpdatedEvent($NativeAccountTypeTrailerObj);
        }
        return $NativeAccountTypeTrailerObj;
    }

    /**
     * Delete a NativeAccountTypeTrailer entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $NativeAccountTypeTrailerObj = $this->find($id);
        $result = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($NativeAccountTypeTrailerObj instanceof Model)
        {
            $this->triggerDeletedEvent($NativeAccountTypeTrailerObj);
        }

        return $result;
    }

    /**
     * @param NativeAccountTypeTrailer $NativeAccountTypeTrailerObj
     */
    public function triggerCreatedEvent($NativeAccountTypeTrailerObj)
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
            $this->ObjectEnabledForEvents($NativeAccountTypeTrailerObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\NativeAccountTypeTrailerCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                    new \App\Waypoint\Events\NativeAccountTypeTrailerCreatedEvent(
                    $NativeAccountTypeTrailerObj,
                    [
                        'event_trigger_message'        => 'Called from NativeAccountTypeTrailerRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($NativeAccountTypeTrailerObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param NativeAccountTypeTrailer $NativeAccountTypeTrailerObj
     */
    public function triggerUpdatedEvent($NativeAccountTypeTrailerObj)
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
            $this->ObjectEnabledForEvents($NativeAccountTypeTrailerObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\NativeAccountTypeTrailerUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\NativeAccountTypeTrailerUpdatedEvent(
                    $NativeAccountTypeTrailerObj,
                    [
                        'event_trigger_message'        => 'Called from NativeAccountTypeTrailerRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($NativeAccountTypeTrailerObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param NativeAccountTypeTrailer $NativeAccountTypeTrailerObj
     */
    public function triggerDeletedEvent($NativeAccountTypeTrailerObj)
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
            $this->ObjectEnabledForEvents($NativeAccountTypeTrailerObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\NativeAccountTypeTrailerDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\NativeAccountTypeTrailerDeletedEvent(
                    $NativeAccountTypeTrailerObj,
                    [
                        'event_trigger_message'        => 'Called from NativeAccountTypeTrailerRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($NativeAccountTypeTrailerObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
