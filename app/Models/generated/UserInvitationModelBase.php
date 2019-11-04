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
 * Class UserInvitation
 *
 * @method static UserInvitation find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static UserInvitation|Collection findOrFail($id, $columns = ['*']) desc
 */
class UserInvitationModelBase extends Model
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
        'id'                    => 'sometimes|integer',
        'invitation_status'     => 'required|max:255',
        'one_time_token_expiry' => 'required',
        'one_time_token'        => 'required|max:255',
        'inviter_ip'            => 'required|max:255',
        'inviter_user_id'       => 'required|integer',
        'invitee_user_id'       => 'sometimes|integer',
        'acceptance_ip'         => 'required|max:255',
        'acceptance_time'       => 'sometimes',
        'created_at'            => 'sometimes',
        'updated_at'            => 'sometimes',
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
        "user",
    ];

    /**
     * @var array
     */
    public static $belongsToMany_arr = [

    ];

    public function construct_scaffold()
    {
        $this->setTable('user_invitations');
        $this->setFillable(
            [

                'invitation_status',
                'one_time_token_expiry',
                'one_time_token',
                'inviter_ip',
                'inviter_user_id',
                'invitee_user_id',
                'acceptance_ip',
                'acceptance_time',

            ]
        );
        $this->setCasts(
            [

                'id'                    => 'integer',
                'invitation_status'     => 'string',
                'one_time_token_expiry' => 'datetime',
                'one_time_token'        => 'string',
                'inviter_ip'            => 'string',
                'inviter_user_id'       => 'integer',
                'invitee_user_id'       => 'integer',
                'acceptance_ip'         => 'string',
                'acceptance_time'       => 'datetime',

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
            'invitee_user_id',
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
            $rules = array_merge(UserInvitation::$baseRules, UserInvitation::$rules);
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
        return UserInvitation::class;
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
