<?php

namespace App\Waypoint\Models;

class RelatedUserTypeSlim extends RelatedUserType
{
    public function toArray(): array
    {
        return [
            "id"                     => $this->id,
            "client_id"              => $this->client_id,
            "name"                   => $this->name,
            "description"            => $this->description,
            "related_object_type"    => $this->related_object_type,
            "related_object_subtype" => $this->related_object_subtype,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
