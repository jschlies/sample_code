<?php

namespace App\Waypoint\Models;

use App\Waypoint\GetEntityTagsTrait;

/**
 * Class UserSummary
 * @package App\Waypoint\Models
 */
class UserSummary extends User
{
    use GetEntityTagsTrait;

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
            "active_status"               => $this->active_status,
            "user_name"                   => $this->user_name,
            "active_status_date"          => $this->perhaps_format_date($this->active_status_date),
            "is_hidden"                   => $this->is_hidden ? true : false,
            'salutation'                  => $this->salutation,
            'suffix'                      => $this->suffix,
            'work_number'                 => $this->work_number,
            'mobile_number'               => $this->mobile_number,
            'company'                     => $this->company,
            'location'                    => $this->location,
            'job_title'                   => $this->job_title,
            "user_invitation_status"      => $this->user_invitation_status,
            "user_invitation_status_date" => $this->perhaps_format_date($this->user_invitation_status_date),
            "config_json"                 => json_decode($this->config_json, true),
            "image_json"                  => json_decode($this->image_json, true),

            "authenticating_entity_id" => $this->authenticating_entity_id,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
