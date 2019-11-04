<?php

namespace App\Waypoint\Models;

use App\Waypoint\Exceptions\GeneralException;

/**
 * Class NativeAccountAmount
 * @package App\Waypoint\Models
 */
class NativeAccountAmount extends NativeAccountAmountModelBase
{
    /**
     * NativeAccountAmount constructor.
     * @param array $attributes
     * @throws GeneralException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            "id"                   => $this->id,
            "client_id"            => $this->client_id,
            "property_id"          => $this->property_id,
            "native_account_id"    => $this->native_account_id,
            "month"                => $this->month,
            "year"                 => $this->year,
            "month_year_timestamp" => $this->month_year_timestamp,
            "actual"               => $this->actual,
            "budget"               => $this->budget,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
