<?php

namespace App\Waypoint\Models;

use App;

/**
 * Class NativeCoaFull
 * @package App\Waypoint\Models
 */
class NativeCoaFull extends NativeCoa
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"             => $this->id,
            "name"           => $this->name,
            "client_id"      => $this->client_id,
            "nativeAccounts" => $this->nativeAccounts->toArray(),

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
