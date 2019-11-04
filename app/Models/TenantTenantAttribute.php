<?php

namespace App\Waypoint\Models;

use App;

/**
 * Class TenantTenantAttribute
 * @package App\Waypoint\Models
 */
class TenantTenantAttribute extends TenantTenantAttributeModelBase
{
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
    ];

    /**
     * NativeCoa constructor.
     * @param array $attributes
     * @throws \App\Waypoint\Exceptions\GeneralException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"                  => $this->id,
            "tenant_id"           => $this->tenant_id,
            "tenant_attribute_id" => $this->tenant_attribute_id,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
