<?php

namespace App\Waypoint\Models;

use App;
use App\Waypoint\AuditableTrait;
use App\Waypoint\Exceptions\GeneralException;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class AccessList
 * @package App\Waypoint\Models
 */
class AuthenticatingEntity extends AuthenticatingEntityModelBase implements AuditableContract
{
    const DEFAULT_AUTHENTICATINGENTITY_IDENTITY_CONNECTION = 'Username-Password-Authentication';

    use AuditableTrait;

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        /**
         * min length 2 to allow state abbr's
         */
        'name'                => 'required|min:2|max:255',
        'description'         => 'sometimes|nullable|min:3|max:255',
        'email_regex'         => 'required|min:2|max:255|regex:/^\/.*\/[i]{0,1}$/',
        'identity_connection' => 'required|min:2|max:255',
    ];

    /**
     * @var array
     * See http://www.laravel-auditing.com/docs/4.0/behavior-settings
     */
    protected $auditInclude = [
        'name',
        'description',
        'email_regex',
        'identity_connection',
        'is_default',
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
     */
    public function toArray(): array
    {
        return [
            "id"                  => $this->id,
            "name"                => $this->name,
            "description"         => $this->description,
            "email_regex"         => $this->email_regex,
            "identity_connection" => $this->identity_connection,
            "is_default"          => $this->is_default ? true : false,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
