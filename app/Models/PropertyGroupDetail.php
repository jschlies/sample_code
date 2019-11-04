<?php

namespace App\Waypoint\Models;

/**
 * Class PropertyGroupDetail
 * @package App\Waypoint\Models
 */
class PropertyGroupDetail extends PropertyGroup
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
            "id"                          => $this->id,
            "name"                        => $this->name,
            "description"                 => $this->description,
            "is_all_property_group"       => $this->is_all_property_group,
            "property_id_md5"             => $this->property_id_md5,
            "total_square_footage"        => $this->total_square_footage,
            "is_public"                   => $this->is_public,
            "client_id"                   => $this->user->client_id,
            "user_id"                     => $this->user_id,
            "parent_property_group_id"    => $this->parent_property_group_id,
            "child_property_groups"       => $this->propertyGroupChildren ? $this->propertyGroupChildren->toArray() : [],
            "property_count"              => $this->properties ? $this->properties->pluck('id')->count() : 0,
            "property_group_property_ids" => $this->properties ? $this->properties->pluck('id')->toArray() : [],

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
