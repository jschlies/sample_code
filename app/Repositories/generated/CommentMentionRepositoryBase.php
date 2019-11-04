<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Events\RepositoryEventBase;
use App\Waypoint\Model;
use App\Waypoint\Models\CommentMention;
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
 * Class CommentMentionRepositoryBase
 *
 * @method Collection findByField($field, $value = null, $columns = ['*']) desc
 * @method CommentMention findWithoutFail($id, $columns = ['*']) desc
 * @method CommentMention find($id, $columns = ['*']) desc
 * @method Collection findWhereIn($field, array $values, $columns = ['*']) desc
 * @method Collection findWhereNotIn($field, array $values, $columns = ['*']) desc
 * @method Collection all($columns = ['*']) desc
 * @method Collection findWhere(array $where, $columns = ['*']) desc
 * @method CommentMention first($columns = ['*']) desc
 */
class CommentMentionRepositoryBase extends BaseRepository
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
        return CommentMention::class;
    }

    /**
     * Save a new CommentMention in repository
     *
     * @param array $attributes
     * @return CommentMention
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $CommentMentionObj = parent::create($attributes);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($CommentMentionObj instanceof Model)
        {
            $this->triggerCreatedEvent($CommentMentionObj);
        }
        return $CommentMentionObj;
    }

    /**
     * Update a CommentMention entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return CommentMention
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $CommentMentionObj = parent::update($attributes, $id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($CommentMentionObj instanceof Model)
        {
            $this->triggerUpdatedEvent($CommentMentionObj);
        }
        return $CommentMentionObj;
    }

    /**
     * Delete a CommentMention entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $CommentMentionObj = $this->find($id);
        $result            = parent::delete($id);

        /**
         * @todo FIX ME this little check is needed to deal with the entrust objects, User, Role and Permission
         */
        if ($CommentMentionObj instanceof Model)
        {
            $this->triggerDeletedEvent($CommentMentionObj);
        }

        return $result;
    }

    /**
     * @param CommentMention $CommentMentionObj
     */
    public function triggerCreatedEvent($CommentMentionObj)
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
            $this->ObjectEnabledForEvents($CommentMentionObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\CommentMentionCreatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\CommentMentionCreatedEvent(
                    $CommentMentionObj,
                    [
                        'event_trigger_message'        => 'Called from CommentMentionRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($CommentMentionObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }

    /**
     * @param CommentMention $CommentMentionObj
     */
    public function triggerUpdatedEvent($CommentMentionObj)
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
            $this->ObjectEnabledForEvents($CommentMentionObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\CommentMentionUpdatedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(new \App\Waypoint\Events\CommentMentionUpdatedEvent(
                      $CommentMentionObj,
                      [
                          'event_trigger_message'        => 'Called from CommentMentionRepositoryBase',
                          'event_trigger_id'             => waypoint_generate_uuid(),
                          'event_trigger_class'          => self::class,
                          'event_trigger_class_instance' => get_class($this),
                          'event_trigger_object_class'   => get_class($CommentMentionObj),
                          'event_trigger_absolute_class' => __CLASS__,
                          'event_trigger_file'           => __FILE__,
                          'event_trigger_line'           => __LINE__,
                      ]
                  )
            );
        }
    }

    /**
     * @param CommentMention $CommentMentionObj
     */
    public function triggerDeletedEvent($CommentMentionObj)
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
            $this->ObjectEnabledForEvents($CommentMentionObj)
        )
        {
            /**
             * Note how \App\Waypoint\Events\CommentMentionDeletedEvent might not exist,
             * This is OK. The above if check keeps this line unexecutable in that case.
             */
            event(
                new \App\Waypoint\Events\CommentMentionDeletedEvent(
                    $CommentMentionObj,
                    [
                        'event_trigger_message'        => 'Called from CommentMentionRepositoryBase',
                        'event_trigger_id'             => waypoint_generate_uuid(),
                        'event_trigger_class'          => self::class,
                        'event_trigger_class_instance' => get_class($this),
                        'event_trigger_object_class'   => get_class($CommentMentionObj),
                        'event_trigger_absolute_class' => __CLASS__,
                        'event_trigger_file'           => __FILE__,
                        'event_trigger_line'           => __LINE__,
                    ]
                )
            );
        }
    }
}
