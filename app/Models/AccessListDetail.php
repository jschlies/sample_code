<?php

namespace App\Waypoint\Models;

/**
 * Class AccessListDetail
 * @package App\Waypoint\Models
 */
class AccessListDetail extends AccessList
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            "id"                           => $this->id,
            "name"                         => $this->name,
            "description"                  => $this->description,
            "client_id"                    => $this->client_id,
            "access_list_users_count"      => $this->accessListUsers->count(),
            "access_list_users"            => $this->accessListUsers->count() ? $this->accessListUsers->toArray() : [],
            "access_list_properties_count" => $this->accessListProperties->count(),
            "access_list_properties"       => $this->accessListProperties->count() ? $this->accessListProperties->toArray() : [],
            "is_all_access_list"           => $this->is_all_access_list ? true : false,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => get_class($this),
        ];
    }
}
