<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\TenantIndustry;
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
 * Class TenantIndustryRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method TenantIndustry findWithoutFail($id, $columns = ['*']) desc
 * @method TenantIndustry find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method TenantIndustry first($columns = ['*']) desc
 */
class TenantIndustryRepositoryBase extends BaseRepository
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
        return TenantIndustry::class;
    }

    /**
     * Save a new TenantIndustry in repository
     *
     * @param array $attributes
     * @return TenantIndustry
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $TenantIndustryObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ( $TenantIndustryObj instanceof Model)
        {
            $this->triggerCreatedEvent($TenantIndustryObj);
        }
        return $TenantIndustryObj;
    }

    /**
     * Update a TenantIndustry entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return TenantIndustry
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $TenantIndustryObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($TenantIndustryObj instanceof Model)
        {
            $this->triggerUpdatedEvent($TenantIndustryObj);
        }
        return $TenantIndustryObj;
    }

    /**
     * Delete a TenantIndustry entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $TenantIndustryObj = $this->find($id);
        $result = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($TenantIndustryObj instanceof Model)
        {
            $this->triggerDeletedEvent($TenantIndustryObj);
        }

        return $result;
    }

    /**
     * @param TenantIndustry $TenantIndustryObj
     */
    public function triggerCreatedEvent($TenantIndustryObj)
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
            $this->ObjectEnabledForEvents($TenantIndustryObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\TenantIndustryCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                    new \App\Waypoint\Events\TenantIndustryCreatedEvent(
                    $TenantIndustryObj,
                    [
                        'event_trigger_message'        => 'Called from TenantIndustryRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($TenantIndustryObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param TenantIndustry $TenantIndustryObj
     */
    public function triggerUpdatedEvent($TenantIndustryObj)
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
            $this->ObjectEnabledForEvents($TenantIndustryObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\TenantIndustryUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\TenantIndustryUpdatedEvent(
                    $TenantIndustryObj,
                    [
                        'event_trigger_message'        => 'Called from TenantIndustryRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($TenantIndustryObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param TenantIndustry $TenantIndustryObj
     */
    public function triggerDeletedEvent($TenantIndustryObj)
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
            $this->ObjectEnabledForEvents($TenantIndustryObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\TenantIndustryDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\TenantIndustryDeletedEvent(
                    $TenantIndustryObj,
                    [
                        'event_trigger_message'        => 'Called from TenantIndustryRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($TenantIndustryObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
