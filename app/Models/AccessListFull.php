<?php

namespace App\Waypoint\Models;

/**
 * Class AccessListFull
 * @package App\Waypoint\Models
 */
class AccessListFull extends AccessList
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
            "id"                       => $this->id,
            "name"                     => $this->name,
            "client_id"                => $this->client_id,
            "description"              => $this->description,
            "accessListPropertiesFull" => $this->accessListPropertiesFull->toArray(),
            "accessListUsersFull"      => $this->accessListUsersFull->toArray(),
            "num_properties"           => $this->properties ? $this->properties->count() : 0,
            "num_users"                => $this->users ? $this->users->count() : 0,
            "is_all_access_list"       => $this->is_all_access_list,
            "comments"                 => $this->getComments()->toArray(),

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function accessListPropertiesFull()
    {
        return $this->hasMany(
            AccessListPropertyFull::class,
            'access_list_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function accessListUsersFull()
    {
        return $this->hasMany(
            AccessListUserFull::class,
            'access_list_id',
            'id'
        );
    }
}
