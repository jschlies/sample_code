<?php

namespace App\Waypoint\Models;

use App\Waypoint\AuditableTrait;
use App\Waypoint\Exceptions\GeneralException;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class AdvancedVarianceApproval
 * @package App\Waypoint\Models
 */
class AdvancedVarianceApproval extends AdvancedVarianceApprovalModelBase implements AuditableContract
{
    use AuditableTrait;

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
    ];

    /**
     * @var array
     * See http://www.laravel-auditing.com/docs/4.0/behavior-settings
     */
    protected $auditInclude = [
        'advanced_variance_id',
        'approving_user_id',
        'approval_date',
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
     * @throws \InvalidArgumentException
     */
    public function toArray(): array
    {
        return [
            "id"                   => $this->id,
            "advanced_variance_id" => $this->advanced_variance_id,
            "approving_user_id"    => $this->approving_user_id,
            "approval_date"        => $this->perhaps_format_date($this->approval_date),

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
