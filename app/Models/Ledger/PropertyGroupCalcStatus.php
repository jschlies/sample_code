<?php

namespace App\Waypoint\Models\Ledger;

/**
 * Class PropertyGroupCalcStatus
 */
class PropertyGroupCalcStatus extends Ledger
{
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        "FK_ACCOUNT_CLIENT_ID" => "string",
        "REF_GROUP_ID"         => "string",
        "STEP"                 => "string",
        "STEP_DESCRIPTION"     => "string",
        "LINUX_SYSTEM_TIME"    => "string",
        "LOCAL_TIME"           => "string",
        "STATUS"               => "string",
        "STATUS_DESCRIPTION"   => "string",
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    /**
     * Repository constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->setFillable(
            [
                "FK_ACCOUNT_CLIENT_ID",
                "REF_GROUP_ID",
                "STEP",
                "STEP_DESCRIPTION",
                "LINUX_SYSTEM_TIME",
                "LOCAL_TIME",
                "STATUS",
                "STATUS_DESCRIPTION",
            ]
        );
        parent::__construct($attributes);
    }

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "FK_ACCOUNT_CLIENT_ID" => $this->FK_ACCOUNT_CLIENT_ID,
            "REF_GROUP_ID"         => $this->REF_GROUP_ID,
            "STEP"                 => $this->STEP,
            "STEP_DESCRIPTION"     => $this->STEP_DESCRIPTION,
            "LINUX_SYSTEM_TIME"    => $this->LINUX_SYSTEM_TIME,
            "LOCAL_TIME"           => $this->LOCAL_TIME,
            "STATUS"               => $this->STATUS,
            "STATUS_DESCRIPTION"   => $this->STATUS_DESCRIPTION,
        ];
    }
}
