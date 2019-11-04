<?php

namespace App\Waypoint\Models;

use App\Waypoint\AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class AdvancedVarianceExplanationType
 * @package App\Waypoint\Models
 */
class AdvancedVarianceExplanationType extends AdvancedVarianceExplanationTypeModelBase implements AuditableContract
{
    use AuditableTrait;

    /**
     * @var array
     * See http://www.laravel-auditing.com/docs/4.0/behavior-settings
     */
    protected $auditInclude = [
        'client_id',
        'name',
        'description',
        'color',
    ];

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            "id"          => $this->id,
            "client_id"   => $this->client_id,
            "name"        => $this->name,
            "description" => $this->description,
            "color"       => $this->color,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
