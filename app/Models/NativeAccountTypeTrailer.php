<?php

namespace App\Waypoint\Models;

use App;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class NativeAccountType
 * @package App\Waypoint\Models
 */
class NativeAccountTypeTrailer extends NativeAccountTypeTrailerModelBase
{
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
    ];

    /**
     * AccessList constructor.
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
            "id"                            => $this->id,
            "native_coa_id"                 => $this->native_coa_id,
            "native_account_type_id"        => $this->native_account_type_id,
            "property_id"                   => $this->property_id,
            "actual_coefficient"            => $this->actual_coefficient,
            "budgeted_coefficient"          => $this->budgeted_coefficient,
            "advanced_variance_coefficient" => $this->advanced_variance_coefficient,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
