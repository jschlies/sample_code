<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\SuiteLease;
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
 * Class SuiteLeaseRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method SuiteLease findWithoutFail($id, $columns = ['*']) desc
 * @method SuiteLease find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method SuiteLease first($columns = ['*']) desc
 */
class SuiteLeaseRepositoryBase extends BaseRepository
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
        return SuiteLease::class;
    }

    /**
     * Save a new SuiteLease in repository
     *
     * @param array $attributes
     * @return SuiteLease
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $SuiteLeaseObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ( $SuiteLeaseObj instanceof Model)
        {
            $this->triggerCreatedEvent($SuiteLeaseObj);
        }
        return $SuiteLeaseObj;
    }

    /**
     * Update a SuiteLease entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return SuiteLease
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $SuiteLeaseObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($SuiteLeaseObj instanceof Model)
        {
            $this->triggerUpdatedEvent($SuiteLeaseObj);
        }
        return $SuiteLeaseObj;
    }

    /**
     * Delete a SuiteLease entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $SuiteLeaseObj = $this->find($id);
        $result = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($SuiteLeaseObj instanceof Model)
        {
            $this->triggerDeletedEvent($SuiteLeaseObj);
        }

        return $result;
    }

    /**
     * @param SuiteLease $SuiteLeaseObj
     */
    public function triggerCreatedEvent($SuiteLeaseObj)
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
            $this->ObjectEnabledForEvents($SuiteLeaseObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\SuiteLeaseCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                    new \App\Waypoint\Events\SuiteLeaseCreatedEvent(
                    $SuiteLeaseObj,
                    [
                        'event_trigger_message'        => 'Called from SuiteLeaseRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($SuiteLeaseObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param SuiteLease $SuiteLeaseObj
     */
    public function triggerUpdatedEvent($SuiteLeaseObj)
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
            $this->ObjectEnabledForEvents($SuiteLeaseObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\SuiteLeaseUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\SuiteLeaseUpdatedEvent(
                    $SuiteLeaseObj,
                    [
                        'event_trigger_message'        => 'Called from SuiteLeaseRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($SuiteLeaseObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param SuiteLease $SuiteLeaseObj
     */
    public function triggerDeletedEvent($SuiteLeaseObj)
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
            $this->ObjectEnabledForEvents($SuiteLeaseObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\SuiteLeaseDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\SuiteLeaseDeletedEvent(
                    $SuiteLeaseObj,
                    [
                        'event_trigger_message'        => 'Called from SuiteLeaseRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($SuiteLeaseObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
