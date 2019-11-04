<?php

namespace App\Waypoint\Models;

use App\Waypoint\GetEntityTagsTrait;

class HeartbeatDetail extends Heartbeat
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
            "client_id"                   => $this->client_id,
            "email"                       => $this->email,
            "active_status"               => $this->active_status,
            "active_status_date"          => $this->perhaps_format_date($this->active_status_date),
            "is_hidden"                   => $this->is_hidden,
            "user_name"                   => $this->user_name,
            "total_square_footage"        => ! is_null($this->allPropertyGroup) ? $this->allPropertyGroup->total_square_footage : 0,
            "num_buildings"               => ! is_null($this->getAccessiblePropertyObjArr()->pluck('id')->toArray()) ? count($this->getAccessiblePropertyObjArr()
                                                                                                                                  ->pluck('id')
                                                                                                                                  ->toArray()) : 0,
            'salutation'                  => $this->salutation,
            'suffix'                      => $this->suffix,
            'work_number'                 => $this->work_number,
            'mobile_number'               => $this->mobile_number,
            'company'                     => $this->company,
            'location'                    => $this->location,
            'job_title'                   => $this->job_title,
            'user_invitation_status'      => $this->user_invitation_status,
            'user_invitation_status_date' => $this->user_invitation_status_date,
            "config_json"                 => json_decode($this->config_json, true),
            "image_json"                  => json_decode($this->image_json, true),

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
