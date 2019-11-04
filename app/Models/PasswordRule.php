<?php

namespace App\Waypoint\Models;

use App;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class PasswordRule
 * @package App\Waypoint\Models
 */
class PasswordRule extends PasswordRuleModelBase
{
    /**
     * See https://waypointbuilding.atlassian.net/wiki/spaces/HER/pages/154730693/Create+User+Sequence
     */
    const PASSWORD_RULE_TYPE_EXCELLENT = 'excellent';
    const PASSWORD_RULE_TYPE_STRONG    = 'strong';
    const PASSWORD_RULE_TYPE_FAIR      = 'fair';
    const PASSWORD_RULE_TYPE_LOW       = 'low';
    const ACTIVE_STATUS_DEFAULT        = self::PASSWORD_RULE_TYPE_FAIR;
    public static $passwword_rule_type_arr = [
        self::PASSWORD_RULE_TYPE_EXCELLENT,
        self::PASSWORD_RULE_TYPE_STRONG,
        self::PASSWORD_RULE_TYPE_FAIR,
        self::PASSWORD_RULE_TYPE_LOW,
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'description'        => 'sometimes|nullable|min:3|max:255',
        'regular_expression' => 'sometimes|nullable|min:1|max:255',
    ];

    /**
     * PasswordRule constructor.
     * @param array $attributes
     * @throws GeneralException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * @param null|array $rules
     * @return null|array
     * @throws GeneralException
     */
    public static function get_model_rules($rules = null, $object_id = null)
    {
        if ($rules == null)
        {
            $rules = array_merge(self::$baseRules, self::$rules);
        }
        $rules                       = parent::get_model_rules($rules, $object_id);
        $rules['password_rule_type'] = 'required|string|max:255|in:' . implode(',', self::$passwword_rule_type_arr);
        return $rules;
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
            "id"                 => $this->id,
            "points"             => $this->points,
            "description"        => $this->description,
            "password_rule_type" => $this->password_rule_type,
            "regular_expression" => $this->regular_expression,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
