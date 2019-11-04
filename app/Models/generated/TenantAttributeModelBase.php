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
 * Class TenantAttribute
 *
 * @method static TenantAttribute find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static TenantAttribute|Collection findOrFail($id, $columns = ['*']) desc
 */
class TenantAttributeModelBase extends Model
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
        'id'                        => 'sometimes|integer',
        'name'                      => 'required|max:255',
        'description'               => 'sometimes|nullable|min:3|max:255',
        'tenant_attribute_category' => 'sometimes|nullable|min:3|max:255',
        'client_id'                 => 'sometimes|integer',
        'created_at'                => 'sometimes',
        'updated_at'                => 'sometimes',
    ];

    /**
     * @var array
     */
    public static $hasMany_arr = [
        "tenantTenantAttribute",
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
    ];

    /**
     * @var array
     */
    public static $belongsToMany_arr = [
        "tenant",
    ];

    public function construct_scaffold()
    {
        $this->setTable('tenant_attributes');
        $this->setFillable(
            [

                'name',
                'description',
                'tenant_attribute_category',
                'client_id',

            ]
        );
        $this->setCasts(
            [

                'id'                        => 'integer',
                'name'                      => 'string',
                'description'               => 'string',
                'tenant_attribute_category' => 'string',
                'client_id'                 => 'integer',

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
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function tenants()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            Tenant::class,
            'tenant_tenant_attributes',
            'tenant_attribute_id',
            'tenant_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function tenantTenantAttributes()
    {
        return $this->hasMany(
            TenantTenantAttribute::class,
            'tenant_attribute_id',
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
            $rules = array_merge(TenantAttribute::$baseRules, TenantAttribute::$rules);
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
        return TenantAttribute::class;
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
