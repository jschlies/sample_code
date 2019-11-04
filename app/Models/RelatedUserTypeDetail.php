<?php

namespace App\Waypoint\Models;

use App\Waypoint\Collection;

/**
 * Class RelatedUserType
 *
 * @method static RelatedUserType find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static RelatedUserType|Collection findOrFail($id, $columns = ['*']) desc
 */
class RelatedUserTypeDetail extends RelatedUserType
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
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
