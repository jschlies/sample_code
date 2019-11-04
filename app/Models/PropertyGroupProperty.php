<?php

namespace App\Waypoint\Models;

use App\Waypoint\AuditableTrait;
use App\Waypoint\Exceptions\GeneralException;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Contracts\UserResolver;

/**
 * Class PropertyGroupProperty
 * @package App\Waypoint\Models
 */
class PropertyGroupProperty extends PropertyGroupPropertyModelBase implements AuditableContract, UserResolver
{
    use AuditableTrait;

    /**
     * @var array
     * See http://www.laravel-auditing.com/docs/4.0/behavior-settings
     */
    protected $auditInclude = [
        'property_group_id',
        'property_id',
    ];

    /**
     * PropertyGroupProperty constructor.
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
     */
    public function toArray(): array
    {
        return [
            "id"                => $this->id,
            "property_id"       => $this->property_id,
            "property_group_id" => $this->property_group_id,
            "client_id"         => $this->property->client_id,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'property_group_id' => 'required|integer|unique_with:property_group_properties,property_id,object_id',
        'property_id'       => 'required|integer',
    ];

}
