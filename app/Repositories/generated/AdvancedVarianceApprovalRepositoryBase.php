<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\AdvancedVarianceApproval;
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
 * Class AdvancedVarianceApprovalRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method AdvancedVarianceApproval findWithoutFail($id, $columns = ['*']) desc
 * @method AdvancedVarianceApproval find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method AdvancedVarianceApproval first($columns = ['*']) desc
 */
class AdvancedVarianceApprovalRepositoryBase extends BaseRepository
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
        return AdvancedVarianceApproval::class;
    }

    /**
     * Save a new AdvancedVarianceApproval in repository
     *
     * @param array $attributes
     * @return AdvancedVarianceApproval
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $AdvancedVarianceApprovalObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($AdvancedVarianceApprovalObj instanceof Model)
        {
            $this->triggerCreatedEvent($AdvancedVarianceApprovalObj);
        }
        return $AdvancedVarianceApprovalObj;
    }

    /**
     * Update a AdvancedVarianceApproval entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return AdvancedVarianceApproval
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $AdvancedVarianceApprovalObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($AdvancedVarianceApprovalObj instanceof Model)
        {
            $this->triggerUpdatedEvent($AdvancedVarianceApprovalObj);
        }
        return $AdvancedVarianceApprovalObj;
    }

    /**
     * Delete a AdvancedVarianceApproval entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $AdvancedVarianceApprovalObj = $this->find($id);
        $result                      = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($AdvancedVarianceApprovalObj instanceof Model)
        {
            $this->triggerDeletedEvent($AdvancedVarianceApprovalObj);
        }

        return $result;
    }

    /**
     * @param AdvancedVarianceApproval $AdvancedVarianceApprovalObj
     */
    public function triggerCreatedEvent($AdvancedVarianceApprovalObj)
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
            $this->ObjectEnabledForEvents($AdvancedVarianceApprovalObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AdvancedVarianceApprovalCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\AdvancedVarianceApprovalCreatedEvent(
                    $AdvancedVarianceApprovalObj,
                    [
                        'event_trigger_message'        => 'Called from AdvancedVarianceApprovalRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($AdvancedVarianceApprovalObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param AdvancedVarianceApproval $AdvancedVarianceApprovalObj
     */
    public function triggerUpdatedEvent($AdvancedVarianceApprovalObj)
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
            $this->ObjectEnabledForEvents($AdvancedVarianceApprovalObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AdvancedVarianceApprovalUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\AdvancedVarianceApprovalUpdatedEvent(
                      $AdvancedVarianceApprovalObj,
                      [
                          'event_trigger_message'        => 'Called from AdvancedVarianceApprovalRepositoryBase',
                          'event_trigger_id'             => waypoint_generate_uuid(),
                          'event_trigger_class'          => self::class,
                          'event_trigger_class_instance' => get_class($this),
                          'event_trigger_object_class'   => get_class($AdvancedVarianceApprovalObj),
                          'event_trigger_absolute_class' => __CLASS__,
                          'event_trigger_file'           => __FILE__,
                          'event_trigger_line'           => __LINE__,
                      ]
                  )
            );
        }
    }

    /**
     * @param AdvancedVarianceApproval $AdvancedVarianceApprovalObj
     */
    public function triggerDeletedEvent($AdvancedVarianceApprovalObj)
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
            $this->ObjectEnabledForEvents($AdvancedVarianceApprovalObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\AdvancedVarianceApprovalDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\AdvancedVarianceApprovalDeletedEvent(
                    $AdvancedVarianceApprovalObj,
                    [
                        'event_trigger_message'        => 'Called from AdvancedVarianceApprovalRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($AdvancedVarianceApprovalObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
