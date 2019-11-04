<?php

namespace App\Waypoint\Models;

/**
 * Class PropertyNativeCoaFull
 * @package App\Waypoint\Models
 */
class PropertyNativeCoaFull extends PropertyNativeCoa
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray()
    {
        return [
            "id"            => $this->id,
            "property_id"   => $this->property_id,
            "native_coa_id" => $this->native_coa_id,
            "nativeCoa"     => $this->nativeCoaFull->toArray(),

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
