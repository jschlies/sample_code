<?php

namespace App\Waypoint\Models;

use App;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class UserInvitation
 * @package App\Waypoint\Models
 */
class UserInvitation extends UserInvitationModelBase
{
    const INVITATION_STATUS_PENDING  = 'pending';
    const INVITATION_STATUS_REVOKED  = 'revoked';
    const INVITATION_STATUS_ACCEPTED = 'accepted';
    const INVITATION_STATUS_EXPIRED  = 'expired';
    public static $invitation_status_value_arr = [
        self::INVITATION_STATUS_PENDING,
        self::INVITATION_STATUS_REVOKED,
        self::INVITATION_STATUS_ACCEPTED,
        self::INVITATION_STATUS_EXPIRED,
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'invitee_user_id'       => 'required|integer',
        'inviter_user_id'       => 'required|integer',
        'inviter_ip'            => 'required|ip',
        'acceptance_ip'         => 'sometimes|nullable|ip',
        'invitation_status'     => 'required',
        'one_time_token_expiry' => 'required',
    ];

    /**
     * UserInvitation constructor.
     * @param array $attributes
     * @throws GeneralException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            "id"                    => $this->id,
            "invitee_user_id"       => $this->invitee_user_id,
            "invitation_status"     => $this->invitation_status,
            "one_time_token_expiry" => $this->perhaps_format_date($this->one_time_token_expiry),
            "one_time_token"        => $this->one_time_token,
            "inviter_user_id"       => $this->inviter_user_id,
            "inviter_ip"            => $this->inviter_ip,
            "acceptance_time"       => $this->perhaps_format_date($this->acceptance_time),
            "acceptance_ip"         => $this->acceptance_ip,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }

    /**
     * @param null $rules
     * @param null $object_id
     * @return array|null
     * @throws GeneralException
     */
    public static function get_model_rules($rules = null, $object_id = null)
    {
        if ($rules == null)
        {
            $rules = array_merge(self::$baseRules, self::$rules);
        }
        $rules                      = parent::get_model_rules($rules, $object_id);
        $rules['invitation_status'] = 'required|string|max:255|in:' . implode(',', self::$invitation_status_value_arr);
        return $rules;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function inviterUser()
    {
        return $this->belongsTo(
            User::class,
            'inviter_user_id',
            'id'
        );
    }

    public function inviteeUser()
    {
        return $this->belongsTo(
            User::class,
            'invitee_user_id',
            'id'
        );
    }

}
