<?php

namespace App\Waypoint\Models;

use App\Waypoint\AuditableTrait;
use App\Waypoint\Exceptions\GeneralException;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Contracts\UserResolver;

/**
 * Class AccessListUser
 * @package App\Waypoint\Models
 */
class AccessListUser extends AccessListUserModelBase implements AuditableContract, UserResolver
{
    use AuditableTrait;

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'access_list_id' => 'required|integer|unique_with:access_list_users,user_id,object_id',
        'user_id'        => 'required|integer',
    ];

    /**
     * @var array
     * See http://www.laravel-auditing.com/docs/4.0/behavior-settings
     */
    protected $auditInclude = [
        'access_list_id',
        'user_id',
    ];

    /**
     * AccessListUser constructor.
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
     */
    public function toArray(): array
    {
        return [
            "id"             => $this->id,
            "user_id"        => $this->user_id,
            "access_list_id" => $this->access_list_id,
            "client_id"      => $this->accessList->client_id,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
