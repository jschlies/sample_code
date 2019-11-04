<?php

namespace App\Waypoint\Models;

use App;
use App\Waypoint\AuditableTrait;
use App\Waypoint\CommentableTrait;
use App\Waypoint\ModelAsOfDateTrait;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class Lease
 * @package App\Waypoint\Models
 */
class Lease extends LeaseModelBase implements AuditableContract
{
    use AuditableTrait;
    use CommentableTrait;
    use ModelAsOfDateTrait;
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'lease_start_date'      => 'date|nullable',
        'lease_expiration_date' => 'date|nullable',
    ];

    /** @var array */
    protected $auditInclude = [
        'property_id',
        'lease_id_code',
        'least_id_staging',
        'lease_name',
        'lease_type',
        'square_footage',
        'description',
        'lease_start_date',
        'lease_expiration_date',
        'lease_term',
        'tenancy_year',
        'monthly_rent',
        'monthly_rent_area',
        'annual_rent',
        'annual_rent_area',
        'annual_rec_area',
        'annual_misc_area',
        'security_deposit',
        'letter_cr_amt',
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
            "id"                    => $this->id,
            "property_id"           => $this->property_id,
            "lease_id_code"         => $this->lease_id_code,
            "least_id_staging"      => $this->least_id_staging,
            "lease_name"            => $this->lease_name,
            "lease_type"            => $this->lease_type,
            "description"           => $this->description,
            "lease_start_date"      => $this->perhaps_format_date($this->lease_start_date),
            "lease_expiration_date" => $this->perhaps_format_date($this->lease_expiration_date),

            "lease_term"       => empty($this->lease_term) ? null : $this->lease_term,
            "tenancy_year"     => $this->tenancy_year,
            "monthly_rent"     => $this->monthly_rent,
            "annual_rent"      => $this->monthly_rent * 12,
            "security_deposit" => $this->security_deposit,
            "letter_cr_amt"    => $this->letter_cr_amt,

            "monthly_rent_per_square_foot" => ($this->square_footage && $this->monthly_rent) ? ($this->monthly_rent / $this->square_footage) : null,
            "annual_rent_per_square_foot"  => ($this->square_footage && $this->monthly_rent) ? ($this->monthly_rent * 12 / $this->square_footage) : null,

            "total_suite_square_footage" =>
                $this->suites
                    ->sum(
                        function ($SuiteObj)
                        {
                            return $SuiteObj->square_footage;
                        }
                    ),
            "lease_square_footage"       => $this->square_footage,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function suiteDetails()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            SuiteDetail::class,
            'suite_leases',
            'lease_id',
            'suite_id'
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
            'lease_tenants',
            'lease_id',
            'tenant_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tenantDetailsForPropertyGroups()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            TenantDetailForPropertyGroups::class,
            'lease_tenants',
            'lease_id',
            'tenant_id'
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
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function leaseTenantDetails()
    {
        return $this->hasMany(
            LeaseTenantDetail::class,
            'lease_id',
            'id'
        );
    }
}
