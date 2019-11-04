<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\Client;
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
 * Class ClientRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method Client findWithoutFail($id, $columns = ['*']) desc
 * @method Client find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method Client first($columns = ['*']) desc
 */
class ClientRepositoryBase extends BaseRepository
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
        return Client::class;
    }

    /**
     * Save a new Client in repository
     *
     * @param array $attributes
     * @return Client
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $ClientObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($ClientObj instanceof Model)
        {
            $this->triggerCreatedEvent($ClientObj);
        }
        return $ClientObj;
    }

    /**
     * Update a Client entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return Client
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $ClientObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($ClientObj instanceof Model)
        {
            $this->triggerUpdatedEvent($ClientObj);
        }
        return $ClientObj;
    }

    /**
     * Delete a Client entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $ClientObj = $this->find($id);
        $result    = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($ClientObj instanceof Model)
        {
            $this->triggerDeletedEvent($ClientObj);
        }

        return $result;
    }

    /**
     * @param Client $ClientObj
     */
    public function triggerCreatedEvent($ClientObj)
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
            $this->ObjectEnabledForEvents($ClientObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\ClientCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\ClientCreatedEvent(
                    $ClientObj,
                    [
                        'event_trigger_message'        => 'Called from ClientRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($ClientObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param Client $ClientObj
     */
    public function triggerUpdatedEvent($ClientObj)
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
            $this->ObjectEnabledForEvents($ClientObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\ClientUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\ClientUpdatedEvent(
                      $ClientObj,
                      [
                          'event_trigger_message'        => 'Called from ClientRepositoryBase',
                          'event_trigger_id'             => waypoint_generate_uuid(),
                          'event_trigger_class'          => self::class,
                          'event_trigger_class_instance' => get_class($this),
                          'event_trigger_object_class'   => get_class($ClientObj),
                          'event_trigger_absolute_class' => __CLASS__,
                          'event_trigger_file'           => __FILE__,
                          'event_trigger_line'           => __LINE__,
                      ]
                  )
            );
        }
    }

    /**
     * @param Client $ClientObj
     */
    public function triggerDeletedEvent($ClientObj)
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
            $this->ObjectEnabledForEvents($ClientObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\ClientDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\ClientDeletedEvent(
                    $ClientObj,
                    [
                        'event_trigger_message'        => 'Called from ClientRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($ClientObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
