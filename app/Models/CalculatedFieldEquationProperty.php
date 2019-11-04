<?php

namespace App\Waypoint\Models;

use App;
use App\Waypoint\AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Contracts\UserResolver;

/**
 * Class AccessList
 * @package App\Waypoint\Models
 */
class CalculatedFieldEquationProperty extends CalculatedFieldEquationPropertyModelBase implements AuditableContract, UserResolver
{
    use AuditableTrait;

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"                           => $this->id,
            'calculated_field_equation_id' => $this->calculated_field_equation_id,
            "property_id"                  => $this->property_id,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }

    /**
     * @var array
     * See http://www.laravel-auditing.com/docs/4.0/behavior-settings
     */
    protected $auditInclude = [
        'calculated_field_equation_id',
        'property_id',
    ];
}
