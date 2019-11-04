<?php

namespace App\Waypoint\Models;

/**
 * Class RoleDetail
 * @package App\Waypoint\Models
 */
class RoleDetail extends Role
{
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"           => $this->id,
            "name"         => $this->name,
            "display_name" => $this->display_name,
            "description"  => $this->description,
            "permissions"  => $this->permissions()->get()->toArray(),

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
