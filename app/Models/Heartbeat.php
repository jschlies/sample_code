<?php

namespace App\Waypoint\Models;

/**
 * Class Heartbeat
 */
class Heartbeat extends User
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"                          => $this->id,
            "user_id"                     => $this->id,
            "firstname"                   => $this->firstname,
            "lastname"                    => $this->lastname,
            "client_id"                   => $this->client_id,
            "client_name"                 => $this->client->name,
            "email"                       => strtolower($this->email),
            "roles"                       => $this->getRolesAsString(),
            "active_status"               => $this->active_status,
            "is_hidden"                   => $this->is_hidden,
            "user_name"                   => $this->user_name,
            "active_status_date"          => $this->perhaps_format_date($this->active_status_date),
            "config_json"                 => json_decode($this->config_json, true),
            "image_json"                  => json_decode($this->image_json, true),
            'salutation'                  => $this->salutation,
            'suffix'                      => $this->suffix,
            'work_number'                 => $this->work_number,
            'mobile_number'               => $this->mobile_number,
            'company'                     => $this->company,
            'location'                    => $this->location,
            'job_title'                   => $this->job_title,
            'user_invitation_status'      => $this->user_invitation_status,
            'user_invitation_status_date' => $this->user_invitation_status_date,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
