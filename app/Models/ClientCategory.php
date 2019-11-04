<?php

namespace App\Waypoint\Models;

use App\Waypoint\Exceptions\GeneralException;

/**
 * Class ClientCategory
 * @package App\Waypoint\Models
 */
class ClientCategory extends ClientCategoryModelBase
{

    public static $default_client_categories_arr =
        [
            'Administrative',
            'Fixed Expenses',
            'Cleaning Expenses',
            'Utilities',
            'Repairs/Maintenance',
            'Security Expenses',
            'Roads/Grounds Expenses',
        ];
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name'        => 'required|min:3|string|max:255',
        'description' => 'sometimes|string',
        'client_id'   => 'required|integer|unique_with:client_categories,name,object_id',
    ];

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
        $rules = parent::get_model_rules($rules, $object_id);
        return $rules;
    }

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"          => $this->id,
            "name"        => $this->name,
            "description" => $this->description,
            "client_id"   => $this->client_id,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
