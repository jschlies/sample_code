<?php

namespace App\Waypoint\Models;

use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;

/**
 * README - README - README - README - README
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 * See readme.md
 * This file is generated - edits to this file will be lost.
 * Please read and understand the info on generating models/controllers/requests/test in the readme.md
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 */

use App\Waypoint\Model;

/**
 * Class PropertyGroup
 *
 * @method static PropertyGroup find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static PropertyGroup|Collection findOrFail($id, $columns = ['*']) desc
 */
class PropertyGroupModelBase extends Model
{
    /**
     * Generated
     */

    /**
     * PropertyModelBase constructor.
     * @param array $attributes
     * @throws GeneralException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Validation rules which get 'merged' with self::$baseRules into self::$rules at $this::__constructor() time
     *
     * @var array
     */
    public static $baseRules = [
        'id'                       => 'sometimes|integer',
        'user_id'                  => 'sometimes|integer',
        'client_id'                => 'sometimes|integer',
        'name'                     => 'sometimes|max:255',
        'total_square_footage'     => 'sometimes|integer',
        'property_id_md5'          => 'sometimes|max:255',
        'description'              => 'sometimes|nullable|min:3|max:255',
        'parent_property_group_id' => 'sometimes|integer',
        'created_at'               => 'sometimes',
        'updated_at'               => 'sometimes',
    ];

    /**
     * @var array
     */
    public static $hasMany_arr = [
        "customReport",
        "propertyGroupProperty",
        "propertyGroup",
    ];

    /**
     * @var array
     */
    public static $hasOne_arr = [

    ];

    /**
     * @var array
     */
    public static $belongsTo_arr = [
        "client",
        "propertyGroup",
        "user",
    ];

    /**
     * @var array
     */
    public static $belongsToMany_arr = [
        "property",
    ];

    public function construct_scaffold()
    {
        $this->setTable('property_groups');
        $this->setFillable(
            [

                'user_id',
                'client_id',
                'name',
                'total_square_footage',
                'property_id_md5',
                'description',
                'is_all_property_group',
                'is_public',
                'parent_property_group_id',

            ]
        );
        $this->setCasts(
            [

                'id'                       => 'integer',
                'user_id'                  => 'integer',
                'client_id'                => 'integer',
                'name'                     => 'string',
                'total_square_footage'     => 'integer',
                'property_id_md5'          => 'string',
                'description'              => 'string',
                'is_all_property_group'    => 'boolean',
                'is_public'                => 'boolean',
                'parent_property_group_id' => 'integer',

            ]
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function client()
    {
        return $this->belongsTo(
            Client::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function propertyGroup()
    {
        return $this->belongsTo(
            PropertyGroup::class,
            'parent_property_group_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function properties()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            Property::class,
            'property_group_properties',
            'property_group_id',
            'property_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function customReports()
    {
        return $this->hasMany(
            CustomReport::class,
            'property_group_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function propertyGroupProperties()
    {
        return $this->hasMany(
            PropertyGroupProperty::class,
            'property_group_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function propertyGroups()
    {
        return $this->hasMany(
            PropertyGroup::class,
            'parent_property_group_id',
            'id'
        );
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
            $rules = array_merge(PropertyGroup::$baseRules, PropertyGroup::$rules);
        }
        $rules = parent::get_model_rules($rules, $object_id);
        return $rules;
    }

    /**
     * Get the class name for polymorphic relations.
     *
     * This is needed to get Audits to work
     *
     * @return string
     */
    public function getMorphClass()
    {
        return PropertyGroup::class;
    }

    /**
     * @return array
     */
    public function getHasManyArr()
    {
        return self::$hasMany_arr;
    }

    /**
     * @return array
     */
    public function getHasOneArr()
    {
        return self::$hasOne_arr;
    }

    /**
     * @return array
     */
    public function getBelongsToArr()
    {
        return self::$belongsTo_arr;
    }

    /**
     * @return array
     */
    public function getBelongsToManyArr()
    {
        return self::$belongsToMany_arr;
    }

    /**
     * End Of Generated
     */
}
