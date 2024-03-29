<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\DownloadHistory;
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
 * Class DownloadHistoryRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method DownloadHistory findWithoutFail($id, $columns = ['*']) desc
 * @method DownloadHistory find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method DownloadHistory first($columns = ['*']) desc
 */
class DownloadHistoryRepositoryBase extends BaseRepository
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
        return DownloadHistory::class;
    }

    /**
     * Save a new DownloadHistory in repository
     *
     * @param array $attributes
     * @return DownloadHistory
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $DownloadHistoryObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ( $DownloadHistoryObj instanceof Model)
        {
            $this->triggerCreatedEvent($DownloadHistoryObj);
        }
        return $DownloadHistoryObj;
    }

    /**
     * Update a DownloadHistory entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return DownloadHistory
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $DownloadHistoryObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($DownloadHistoryObj instanceof Model)
        {
            $this->triggerUpdatedEvent($DownloadHistoryObj);
        }
        return $DownloadHistoryObj;
    }

    /**
     * Delete a DownloadHistory entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $DownloadHistoryObj = $this->find($id);
        $result = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($DownloadHistoryObj instanceof Model)
        {
            $this->triggerDeletedEvent($DownloadHistoryObj);
        }

        return $result;
    }

    /**
     * @param DownloadHistory $DownloadHistoryObj
     */
    public function triggerCreatedEvent($DownloadHistoryObj)
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
            $this->ObjectEnabledForEvents($DownloadHistoryObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\DownloadHistoryCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                    new \App\Waypoint\Events\DownloadHistoryCreatedEvent(
                    $DownloadHistoryObj,
                    [
                        'event_trigger_message'        => 'Called from DownloadHistoryRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($DownloadHistoryObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param DownloadHistory $DownloadHistoryObj
     */
    public function triggerUpdatedEvent($DownloadHistoryObj)
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
            $this->ObjectEnabledForEvents($DownloadHistoryObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\DownloadHistoryUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\DownloadHistoryUpdatedEvent(
                    $DownloadHistoryObj,
                    [
                        'event_trigger_message'        => 'Called from DownloadHistoryRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($DownloadHistoryObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param DownloadHistory $DownloadHistoryObj
     */
    public function triggerDeletedEvent($DownloadHistoryObj)
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
            $this->ObjectEnabledForEvents($DownloadHistoryObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\DownloadHistoryDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\DownloadHistoryDeletedEvent(
                    $DownloadHistoryObj,
                    [
                        'event_trigger_message'        => 'Called from DownloadHistoryRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($DownloadHistoryObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
