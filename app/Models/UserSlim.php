<?php

namespace App\Waypoint\Models;

use App;
use function is_object;
use function json_decode;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method static User find($id, $columns = ['*'])
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
class UserSlim extends User
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        $access_list_names_arr = $this->accessLists->pluck('name')->toArray();

        return [
            "id"                          => $this->id,
            "firstname"                   => $this->firstname,
            "lastname"                    => $this->lastname,
            "client_id"                   => $this->client_id,
            "email"                       => $this->email,
            "active_status"               => $this->active_status,
            "active_status_date"          => is_object($this->active_status_date) ? $this->active_status_date->format('Y-m-d H:i:s') : $this->active_status_date,
            "first_login_date"            => $this->perhaps_format_date($this->first_login_date),
            "last_login_date"             => $this->perhaps_format_date($this->last_login_date),
            "user_name"                   => $this->user_name,
            "is_hidden"                   => $this->is_hidden ? true : false,
            'salutation'                  => $this->salutation,
            'suffix'                      => $this->suffix,
            'work_number'                 => $this->work_number,
            'mobile_number'               => $this->mobile_number,
            'company'                     => $this->company,
            'location'                    => $this->location,
            'job_title'                   => $this->job_title,
            'user_invitation_status'      => $this->user_invitation_status,
            "user_invitation_status_date" => is_object($this->user_invitation_status_date) ? $this->user_invitation_status_date->format('Y-m-d H:i:s') : $this->user_invitation_status_date,

            "config_json" => $this->config_json ? json_decode($this->config_json, true) : [],
            "image_json"  => $this->image_json ? json_decode($this->image_json, true) : [],

            "roles"                    => $this->getRoleNamesArr(),
            'highest_role'             => $this->getHighestRole(),
            "authenticating_entity_id" => $this->authenticating_entity_id,
            'access_list_names_arr'    => $access_list_names_arr,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
