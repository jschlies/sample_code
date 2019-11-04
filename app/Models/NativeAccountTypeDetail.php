<?php

namespace App\Waypoint\Models;

use App;

/**
 * Class NativeAccountTypeDetail
 * @package App\Waypoint\Models
 */
class NativeAccountTypeDetail extends NativeAccountType
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
            "id"                              => $this->id,
            "client_id"                       => $this->client_id,
            "native_account_type_name"        => $this->native_account_type_name,
            "native_account_type_description" => $this->native_account_type_description,
            "nativeAccountTypeTrailers"       => $this->nativeAccountTypeTrailers->toArray(),

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
