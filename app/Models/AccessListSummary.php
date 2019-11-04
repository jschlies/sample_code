<?php

namespace App\Waypoint\Models;

/**
 * Class AccessListSummary
 * @package App\Waypoint\Models
 */
class AccessListSummary extends AccessList
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
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
            "access_list_properties"       => $this->accessListPropertiesSummary->count() ? $this->accessListPropertiesSummary->toArray() : [],
            "is_all_access_list"           => $this->is_all_access_list,
            "comments"                     => $this->getComments()->toArray(),

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => get_class($this),
        ];
    }
}
