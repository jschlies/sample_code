<?php

namespace App\Waypoint\Models;

use App\Waypoint\GetEntityTagsTrait;
use Illuminate\Database\Eloquent\Builder;

/**
 *
 * @method static User find($id, $columns = ['*']) desc
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and') desc
 *
 */
class UserAdmin extends UserDetail
{
    use GetEntityTagsTrait;

    /** @var string $fillable */
    public $fillable = [
        "id",
        "firstname",
        "lastname",
        "email",
        "active_status",
        "active_status_date",
        "client_id",
        "user_name",
        "is_hidden", // $fillable is here because is_hidden is only editable via UserAdmin
        'salutation',
        'suffix',
        'work_number',
        'mobile_number',
        'company',
        'location',
        'job_title',
        'user_invitation_status',
        'user_invitation_status_date',
        "creation_auth0_response",
        'authenticating_entity_id',
    ];

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"                          => $this->id,
            "firstname"                   => $this->firstname,
            "lastname"                    => $this->lastname,
            "roles"                       => $this->getRoleNamesArr(),
            "role_highest"                => $this->getHighestRole(),
            "client_id"                   => $this->client_id,
            "email"                       => $this->email,
            "user_name"                   => $this->user_name,
            "active_status"               => $this->active_status,
            "active_status_date"          => $this->perhaps_format_date($this->active_status_date),
            "first_login_date"            => $this->perhaps_format_date($this->first_login_date),
            "last_login_date"             => $this->perhaps_format_date($this->last_login_date),
            "favorite_things"             => $this->getFavoriteThings(),
            "is_hidden"                   => $this->is_hidden,
            "creation_auth0_response"     => $this->creation_auth0_response,
            'salutation'                  => $this->salutation,
            'suffix'                      => $this->suffix,
            'work_number'                 => $this->work_number,
            'mobile_number'               => $this->mobile_number,
            'company'                     => $this->company,
            'location'                    => $this->location,
            'job_title'                   => $this->job_title,
            'user_invitation_status'      => $this->user_invitation_status,
            'user_invitation_status_date' => $this->perhaps_format_date($this->user_invitation_status_date),
            "config_json"                 => json_decode($this->config_json, true),
            "style_json"                  => json_decode($this->style_json, true),
            "image_json"                  => json_decode($this->image_json, true),
            "authenticating_entity_id"    => $this->authenticating_entity_id,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
