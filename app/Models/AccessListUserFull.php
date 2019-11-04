<?php

namespace App\Waypoint\Models;

/**
 * Class AccessListUserFull
 * @package App\Waypoint\Models
 */
class AccessListUserFull extends AccessListUser
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"             => $this->id,
            "user_id"        => $this->user_id,
            "access_list_id" => $this->access_list_id,
            "client_id"      => $this->user->client_id,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
