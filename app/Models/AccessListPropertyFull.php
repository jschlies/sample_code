<?php

namespace App\Waypoint\Models;

/**
 * Class AccessListPropertyFull
 * @package App\Waypoint\Models
 */
class AccessListPropertyFull extends AccessListProperty
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"             => $this->id,
            "access_list_id" => $this->access_list_id,
            "property_id"    => $this->property_id,
            "client_id"      => $this->accessList->client_id,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
