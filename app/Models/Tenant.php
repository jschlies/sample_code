<?php

namespace App\Waypoint\Models;

use App;

/**
 * Class Tenant
 * @package App\Waypoint\Models
 */
class Tenant extends TenantModelBase
{
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
    ];

    /**
     * Tenant constructor.
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
            "id"                 => $this->id,
            "name"               => $this->name,
            "description"        => $this->description,
            "client_id"          => $this->client_id,
            "tenant_industry_id" => $this->tenant_industry_id,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function tenantIndustryDetail()
    {
        return $this->belongsTo(
            TenantIndustryDetail::class,
            'tenant_industry_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function leaseDetails()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            LeaseDetail::class,
            'lease_tenants',
            'tenant_id',
            'lease_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function suiteDetails()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            SuiteDetail::class,
            'suite_tenants',
            'tenant_id',
            'suite_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function tenantAttributeDetails()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            TenantAttributeDetail::class,
            'tenant_tenant_attributes',
            'tenant_id',
            'tenant_attribute_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function leaseTenantDetails()
    {
        return $this->hasMany(
            LeaseTenantDetail::class,
            'tenant_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function suiteTenantDetails()
    {
        return $this->hasMany(
            SuiteTenantDetail::class,
            'tenant_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function tenantTenantAttributeDetails()
    {
        return $this->hasMany(
            TenantTenantAttributeDetail::class,
            'tenant_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function clientDetail()
    {
        return $this->belongsTo(
            ClientDetail::class,
            'client_id',
            'id'
        );
    }
}
