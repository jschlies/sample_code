<?php

namespace App\Waypoint\Models;

/**
 * @method static User find($id, $columns = ['*'])
 * see https://github.com/chrisbjr/api-guard
 */
class UserApiKey extends User
{
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
            "name"                        => $this->name,
            "roles"                       => $this->getRoleNamesArr(),
            "client_id"                   => $this->client_id,
            "email"                       => $this->email,
            "active_status"               => $this->active_status,
            "active_status_date"          => $this->perhaps_format_date($this->active_status_date),
            "first_login_date"            => $this->perhaps_format_date($this->first_login_date),
            "last_login_date"             => $this->perhaps_format_date($this->last_login_date),
            "user_name"                   => $this->user_name,
            "favorite_property_id_arr"    => $this->apiKey,
            "user_invitation_status"      => $this->user_invitation_status,
            "user_invitation_status_date" => $this->user_invitation_status_date,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
