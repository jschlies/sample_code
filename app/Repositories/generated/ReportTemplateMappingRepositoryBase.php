<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\ReportTemplateMapping;
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
 * Class ReportTemplateMappingRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method ReportTemplateMapping findWithoutFail($id, $columns = ['*']) desc
 * @method ReportTemplateMapping find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method ReportTemplateMapping first($columns = ['*']) desc
 */
class ReportTemplateMappingRepositoryBase extends BaseRepository
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
        return ReportTemplateMapping::class;
    }

    /**
     * Save a new ReportTemplateMapping in repository
     *
     * @param array $attributes
     * @return ReportTemplateMapping
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $ReportTemplateMappingObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ( $ReportTemplateMappingObj instanceof Model)
        {
            $this->triggerCreatedEvent($ReportTemplateMappingObj);
        }
        return $ReportTemplateMappingObj;
    }

    /**
     * Update a ReportTemplateMapping entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return ReportTemplateMapping
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $ReportTemplateMappingObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($ReportTemplateMappingObj instanceof Model)
        {
            $this->triggerUpdatedEvent($ReportTemplateMappingObj);
        }
        return $ReportTemplateMappingObj;
    }

    /**
     * Delete a ReportTemplateMapping entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $ReportTemplateMappingObj = $this->find($id);
        $result = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($ReportTemplateMappingObj instanceof Model)
        {
            $this->triggerDeletedEvent($ReportTemplateMappingObj);
        }

        return $result;
    }

    /**
     * @param ReportTemplateMapping $ReportTemplateMappingObj
     */
    public function triggerCreatedEvent($ReportTemplateMappingObj)
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
            $this->ObjectEnabledForEvents($ReportTemplateMappingObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\ReportTemplateMappingCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                    new \App\Waypoint\Events\ReportTemplateMappingCreatedEvent(
                    $ReportTemplateMappingObj,
                    [
                        'event_trigger_message'        => 'Called from ReportTemplateMappingRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($ReportTemplateMappingObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param ReportTemplateMapping $ReportTemplateMappingObj
     */
    public function triggerUpdatedEvent($ReportTemplateMappingObj)
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
            $this->ObjectEnabledForEvents($ReportTemplateMappingObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\ReportTemplateMappingUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\ReportTemplateMappingUpdatedEvent(
                    $ReportTemplateMappingObj,
                    [
                        'event_trigger_message'        => 'Called from ReportTemplateMappingRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($ReportTemplateMappingObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param ReportTemplateMapping $ReportTemplateMappingObj
     */
    public function triggerDeletedEvent($ReportTemplateMappingObj)
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
            $this->ObjectEnabledForEvents($ReportTemplateMappingObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\ReportTemplateMappingDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\ReportTemplateMappingDeletedEvent(
                    $ReportTemplateMappingObj,
                    [
                        'event_trigger_message'        => 'Called from ReportTemplateMappingRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($ReportTemplateMappingObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
