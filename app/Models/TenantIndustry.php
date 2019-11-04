<?php

namespace App\Waypoint\Models;

use App;

/**
 * Class TenantIndustry
 * @package App\Waypoint\Models
 */
class TenantIndustry extends TenantIndustryModelBase
{
    const TENANT_TYPE_CATEGORY_PRIMARY_INDUSTRTY   = 'primary industry';
    const TENANT_TYPE_CATEGORY_SECONDARY_INDUSTRTY = 'secondary industry';
    const TENANT_TYPE_CATEGORY_SIZE                = 'size';
    const TENANT_TYPE_CATEGORY_DEFAULT             = self::TENANT_TYPE_CATEGORY_PRIMARY_INDUSTRTY;
    public static $tenant_industry_category_value_arr = [
        self::TENANT_TYPE_CATEGORY_PRIMARY_INDUSTRTY,
        self::TENANT_TYPE_CATEGORY_SECONDARY_INDUSTRTY,
        self::TENANT_TYPE_CATEGORY_SIZE,
    ];

    const TENANT_TYPE_TECHNOLOGY        = 'technology';
    const TENANT_TYPE_FINANCIAL         = 'financial';
    const TENANT_TYPE_HEALTHCARE        = 'healthcare';
    const TENANT_TYPE_EDUCATION         = 'education';
    const TENANT_TYPE_TRANSPORTATION    = 'transportation';
    const TENANT_TYPE_PUBLISHING        = 'publishing';
    const TENANT_TYPE_INSURANCE         = 'insurance';
    const TENANT_TYPE_TELECOMMUNICATION = 'telecommunication';
    const TENANT_TYPE_NONE              = 'none';
    const TENANT_TYPE_DEFAULT           = self::TENANT_TYPE_NONE;
    public static $tenant_industry_default_name_arr = [
        self::TENANT_TYPE_TECHNOLOGY,
        self::TENANT_TYPE_FINANCIAL,
        self::TENANT_TYPE_HEALTHCARE,
        self::TENANT_TYPE_EDUCATION,
        self::TENANT_TYPE_PUBLISHING,
        self::TENANT_TYPE_INSURANCE,
        self::TENANT_TYPE_TELECOMMUNICATION,
        self::TENANT_TYPE_NONE,
    ];

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
            "id"                       => $this->id,
            "name"                     => $this->name,
            "description"              => $this->description,
            "tenant_industry_category" => $this->tenant_industry_category,
            "client_id"                => $this->client_id,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
