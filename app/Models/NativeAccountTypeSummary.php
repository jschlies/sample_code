<?php

namespace App\Waypoint\Models;

use App;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class NativeAccountTypeSummary
 * @package App\Waypoint\Models
 */
class NativeAccountTypeSummary extends NativeAccountType
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     *
     * @return array
     *
     */
    public function toArray(): array
    {
        return [
            "id"                       => $this->id,
            "native_account_type_name" => $this->native_account_type_name,
            "created_at"               => $this->perhaps_format_date($this->created_at),
            "updated_at"               => $this->perhaps_format_date($this->updated_at),
            "model_name"               => self::class,
        ];
    }

    public function toArrayWithAdditionalAttributes(): array
    {
        return [
            "id"                               => $this->id,
            "native_account_type_name"         => $this->native_account_type_name,
            "report_template_account_group_id" => $this->report_template_account_group_id ?? null,
            "created_at"                       => $this->perhaps_format_date($this->created_at),
            "updated_at"                       => $this->perhaps_format_date($this->updated_at),
            "model_name"                       => self::class,
        ];
    }
}
