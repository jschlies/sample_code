<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\PasswordRule;
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
 * Class PasswordRuleRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method PasswordRule findWithoutFail($id, $columns = ['*']) desc
 * @method PasswordRule find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method PasswordRule first($columns = ['*']) desc
 */
class PasswordRuleRepositoryBase extends BaseRepository
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
        return PasswordRule::class;
    }

    /**
     * Save a new PasswordRule in repository
     *
     * @param array $attributes
     * @return PasswordRule
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $PasswordRuleObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ( $PasswordRuleObj instanceof Model)
        {
            $this->triggerCreatedEvent($PasswordRuleObj);
        }
        return $PasswordRuleObj;
    }

    /**
     * Update a PasswordRule entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return PasswordRule
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $PasswordRuleObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($PasswordRuleObj instanceof Model)
        {
            $this->triggerUpdatedEvent($PasswordRuleObj);
        }
        return $PasswordRuleObj;
    }

    /**
     * Delete a PasswordRule entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $PasswordRuleObj = $this->find($id);
        $result = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($PasswordRuleObj instanceof Model)
        {
            $this->triggerDeletedEvent($PasswordRuleObj);
        }

        return $result;
    }

    /**
     * @param PasswordRule $PasswordRuleObj
     */
    public function triggerCreatedEvent($PasswordRuleObj)
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
            $this->ObjectEnabledForEvents($PasswordRuleObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\PasswordRuleCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                    new \App\Waypoint\Events\PasswordRuleCreatedEvent(
                    $PasswordRuleObj,
                    [
                        'event_trigger_message'        => 'Called from PasswordRuleRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($PasswordRuleObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param PasswordRule $PasswordRuleObj
     */
    public function triggerUpdatedEvent($PasswordRuleObj)
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
            $this->ObjectEnabledForEvents($PasswordRuleObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\PasswordRuleUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\PasswordRuleUpdatedEvent(
                    $PasswordRuleObj,
                    [
                        'event_trigger_message'        => 'Called from PasswordRuleRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($PasswordRuleObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param PasswordRule $PasswordRuleObj
     */
    public function triggerDeletedEvent($PasswordRuleObj)
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
            $this->ObjectEnabledForEvents($PasswordRuleObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\PasswordRuleDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\PasswordRuleDeletedEvent(
                    $PasswordRuleObj,
                    [
                        'event_trigger_message'        => 'Called from PasswordRuleRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($PasswordRuleObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
