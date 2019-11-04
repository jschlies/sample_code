<?php

namespace App\Waypoint\Models;

use App;

/**
 * Class TenantAttribute
 * @package App\Waypoint\Models
 */
class TenantAttribute extends TenantAttributeModelBase
{
    const TENANT_ATTRIBUTE_CATEGORY_COLOR  = 'color';
    const TENANT_ATTRIBUTE_CATEGORY_HEIGHT = 'height';
    const TENANT_ATTRIBUTE_CATEGORY_SIZE   = 'size';
    public static $tenant_attribute_category_value_arr = [
        self::TENANT_ATTRIBUTE_CATEGORY_COLOR,
        self::TENANT_ATTRIBUTE_CATEGORY_HEIGHT,
        self::TENANT_ATTRIBUTE_CATEGORY_SIZE,
    ];

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"          => $this->id,
            "name"        => $this->name,
            "description" => $this->description,
            "client_id"   => $this->client_id,

            'tenant_attribute_category' => $this->tenant_attribute_category,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
