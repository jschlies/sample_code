<?php

namespace App\Waypoint\Models;

use App\Waypoint\AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Contracts\UserResolver;

/**
 * Class AccessListProperty
 * @package App\Waypoint\Models
 */
class AccessListProperty extends AccessListPropertyModelBase implements AuditableContract, UserResolver
{
    use AuditableTrait;

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'access_list_id' => 'required|integer|unique_with:access_list_properties,property_id,object_id',
        'property_id'    => 'required|integer|',
    ];

    /**
     * @var array
     * See http://www.laravel-auditing.com/docs/4.0/behavior-settings
     */
    protected $auditInclude = [
        'access_list_id',
        'property_id',
    ];

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
            "id"             => $this->id,
            "access_list_id" => $this->access_list_id,
            "property_id"    => $this->property_id,
            "client_id"      => $this->accessList->client_id,
            "property"       => $this->property->toArray(),

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function propertySummary()
    {
        return $this->belongsTo(
            PropertySummary::class,
            'property_id',
            'id'
        );
    }
}
