<?php

namespace App\Waypoint\Models;

use App;
use App\Waypoint\CommentableTrait;

/**
 * Class Suite
 * @package App\Waypoint\Models
 */
class Suite extends SuiteModelBase
{
    use CommentableTrait;
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
            "id"                     => $this->id,
            "property_id"            => $this->property_id,
            "suite_id_code"          => $this->suite_id_code,
            "suite_id_number"        => $this->suite_id_number,
            "name"                   => $this->name,
            "description"            => $this->description,
            "square_footage"         => $this->square_footage,
            "original_property_code" => $this->original_property_code,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function leaseDetails()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            LeaseDetail::class,
            'suite_leases',
            'suite_id',
            'lease_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function propertyDetail()
    {
        return $this->belongsTo(
            PropertyDetail::class,
            'property_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function tenantDetails()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            TenantDetail::class,
            'suite_tenants',
            'suite_id',
            'tenant_id'
        );
    }
}
