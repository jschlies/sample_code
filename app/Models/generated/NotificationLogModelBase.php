<?php

namespace App\Waypoint\Models;

use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;

/**
 * README - README - README - README - README
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 * See readme.md
 * This file is generated - edits to this file will be lost.
 * Please read and understand the info on generating models/controllers/requests/test in the readme.md
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 */

use App\Waypoint\Model;

/**
 * Class NotificationLog
 *
 * @method static NotificationLog find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static NotificationLog|Collection findOrFail($id, $columns = ['*']) desc
 */
class NotificationLogModelBase extends Model
{
    /**
     * Generated
     */

    /**
     * PropertyModelBase constructor.
     * @param array $attributes
     * @throws GeneralException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Validation rules which get 'merged' with self::$baseRules into self::$rules at $this::__constructor() time
     *
     * @var array
     */
    public static $baseRules = [
        'id'                => 'sometimes|integer',
        'notification_time' => 'sometimes',
        'notification_uuid' => 'sometimes|max:255',
        'user_id'           => 'sometimes|integer',
        'user_json'         => 'sometimes',
        'channel'           => 'sometimes|max:255',
        'queue'             => 'sometimes|max:255',
        'response'          => 'sometimes|max:255',
        'data_json'         => 'sometimes',
        'created_at'        => 'sometimes',
        'updated_at'        => 'sometimes',
    ];

    /**
     * @var array
     */
    public static $hasMany_arr = [

    ];

    /**
     * @var array
     */
    public static $hasOne_arr = [

    ];

    /**
     * @var array
     */
    public static $belongsTo_arr = [
        "user",
    ];

    /**
     * @var array
     */
    public static $belongsToMany_arr = [

    ];

    public function construct_scaffold()
    {
        $this->setTable('notification_logs');
        $this->setFillable(
            [

                'notification_time',
                'notification_uuid',
                'user_id',
                'user_json',
                'channel',
                'queue',
                'response',
                'data_json',

            ]
        );
        $this->setCasts(
            [

                'id'                => 'integer',
                'notification_time' => 'datetime',
                'notification_uuid' => 'string',
                'user_id'           => 'integer',
                'user_json'         => 'string',
                'channel'           => 'string',
                'queue'             => 'string',
                'data_json'         => 'string',

            ]
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id'
        );
    }

    /**
     * @param null|array $rules
     * @return null|array
     * @throws GeneralException
     */
    public static function get_model_rules($rules = null, $object_id = null)
    {
        if ($rules == null)
        {
            $rules = array_merge(NotificationLog::$baseRules, NotificationLog::$rules);
        }
        $rules = parent::get_model_rules($rules, $object_id);
        return $rules;
    }

    /**
     * Get the class name for polymorphic relations.
     *
     * This is needed to get Audits to work
     *
     * @return string
     */
    public function getMorphClass()
    {
        return NotificationLog::class;
    }

    /**
     * @return array
     */
    public function getHasManyArr()
    {
        return self::$hasMany_arr;
    }

    /**
     * @return array
     */
    public function getHasOneArr()
    {
        return self::$hasOne_arr;
    }

    /**
     * @return array
     */
    public function getBelongsToArr()
    {
        return self::$belongsTo_arr;
    }

    /**
     * @return array
     */
    public function getBelongsToManyArr()
    {
        return self::$belongsToMany_arr;
    }

    /**
     * End Of Generated
     */
}
