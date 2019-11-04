<?php

namespace App\Waypoint\Models;

/**
 * Class PropertyNativeCoa
 * @package App\Waypoint\Models
 */
class PropertyNativeCoa extends PropertyNativeCoaModelBase
{
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'property_id'   => 'required|integer|unique_with:property_native_coas,property_id,native_coa_id,object_id',
        'native_coa_id' => 'required|nullable|integer',
    ];

    /**
     * NativeCoaLedger constructor.
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
    public function toArray()
    {
        return [
            "id"            => $this->id,
            "property_id"   => $this->property_id,
            "native_coa_id" => $this->native_coa_id,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function nativeCoaFull()
    {
        return $this->belongsTo(
            NativeCoaFull::class,
            'native_coa_id',
            'id'
        );
    }
}
