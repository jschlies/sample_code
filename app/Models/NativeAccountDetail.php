<?php

namespace App\Waypoint\Models;

/**
 * Class NativeAccount
 * @package App\Waypoint\Models
 */
class NativeAccountDetail extends NativeAccount
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"                       => $this->id,
            "native_coa_name"          => $this->nativeCoa->name,
            "native_account_name"      => $this->native_account_name,
            "native_account_code"      => $this->native_account_code,
            'native_account_type_id'   => $this->native_account_type_id,
            "nativeAccountTypeDetail"  => $this->nativeAccountTypeDetail->toArray(),
            "parent_native_account_id" => $this->parent_native_account_id,
            "is_category"              => $this->is_category,
            "is_recoverable"           => $this->is_recoverable,
            "native_coa_type"          => $this->native_coa_type,
            "native_coa_id"            => $this->native_coa_id,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
