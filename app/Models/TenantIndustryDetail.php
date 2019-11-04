<?php

namespace App\Waypoint\Models;

use App;

/**
 * Class TenantIndustry
 * @package App\Waypoint\Models
 */
class TenantIndustryDetail extends TenantIndustry
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"                       => $this->id,
            "name"                     => $this->name,
            "description"              => $this->description,
            "tenant_industry_category" => $this->tenant_industry_category,
            "client_id"                => $this->client_id,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
