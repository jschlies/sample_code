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
 * Class Client
 *
 * @method static Client find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static Client|Collection findOrFail($id, $columns = ['*']) desc
 */
class ClientModelBase extends Model
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
        'id'                                           => 'sometimes|integer',
        'client_id_old'                                => 'sometimes|integer',
        'name'                                         => 'sometimes|max:255',
        'description'                                  => 'sometimes|nullable|min:3|max:255',
        'client_code'                                  => 'sometimes|max:255',
        'display_name'                                 => 'textarea|nullable|min:3|max:255',
        'display_name_old'                             => 'sometimes|nullable|min:3|max:255',
        'active_status'                                => 'sometimes|max:255',
        'active_status_date'                           => 'sometimes',
        'sftp_host_name'                               => 'sometimes|max:255',
        'sftp_user_name'                               => 'sometimes|max:255',
        'sftp_password'                                => 'sometimes|max:255',
        'property_group_calc_status'                   => 'sometimes|max:255',
        'property_group_calc_last_requested'           => 'sometimes',
        'property_group_force_recalc'                  => 'sometimes',
        'property_group_force_first_time_calc'         => 'sometimes',
        'property_group_force_calc_property_group_ids' => 'sometimes',
        'config_json'                                  => 'sometimes|array_or_json_string',
        'style_json'                                   => 'sometimes|array_or_json_string',
        'image_json'                                   => 'sometimes|array_or_json_string',
        'session_ttl'                                  => 'sometimes|integer',
        'dormant_user_switch'                          => 'sometimes',
        'dormant_user_ttl'                             => 'sometimes|integer',
        'created_at'                                   => 'sometimes',
        'updated_at'                                   => 'sometimes',
    ];

    /**
     * @var array
     */
    public static $hasMany_arr = [
        "accessList",
        "advancedVarianceExplanationType",
        "advancedVarianceThreshold",
        "assetType",
        "clientCategory",
        "customReportType",
        "entityTagEntity",
        "nativeAccountAmount",
        "nativeAccountType",
        "nativeCoa",
        "property",
        "propertyGroup",
        "relatedUserType",
        "reportTemplate",
        "role",
        "tenantAttribute",
        "tenantIndustry",
        "tenant",
        "user",
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

    ];

    /**
     * @var array
     */
    public static $belongsToMany_arr = [

    ];

    public function construct_scaffold()
    {
        $this->setTable('clients');
        $this->setFillable(
            [

                'client_id_old',
                'name',
                'description',
                'client_code',
                'display_name',
                'display_name_old',
                'active_status',
                'active_status_date',
                'sftp_host_name',
                'sftp_user_name',
                'sftp_password',
                'property_group_calc_status',
                'property_group_calc_last_requested',
                'property_group_force_recalc',
                'property_group_force_first_time_calc',
                'property_group_force_calc_property_group_ids',
                'config_json',
                'style_json',
                'image_json',
                'session_ttl',
                'dormant_user_switch',
                'dormant_user_ttl',

            ]
        );
        $this->setCasts(
            [

                'id'                                           => 'integer',
                'client_id_old'                                => 'integer',
                'name'                                         => 'string',
                'description'                                  => 'string',
                'client_code'                                  => 'string',
                'display_name'                                 => 'string',
                'display_name_old'                             => 'string',
                'active_status'                                => 'string',
                'active_status_date'                           => 'datetime',
                'sftp_host_name'                               => 'string',
                'sftp_user_name'                               => 'string',
                'sftp_password'                                => 'string',
                'property_group_calc_status'                   => 'string',
                'property_group_calc_last_requested'           => 'datetime',
                'property_group_force_recalc'                  => 'boolean',
                'property_group_force_first_time_calc'         => 'boolean',
                'property_group_force_calc_property_group_ids' => 'string',
                'config_json'                                  => 'string',
                'style_json'                                   => 'string',
                'image_json'                                   => 'string',
                'session_ttl'                                  => 'integer',
                'dormant_user_switch'                          => 'boolean',
                'dormant_user_ttl'                             => 'integer',

            ]
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function accessLists()
    {
        return $this->hasMany(
            AccessList::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function advancedVarianceExplanationTypes()
    {
        return $this->hasMany(
            AdvancedVarianceExplanationType::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function advancedVarianceThresholds()
    {
        return $this->hasMany(
            AdvancedVarianceThreshold::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function assetTypes()
    {
        return $this->hasMany(
            AssetType::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function clientCategories()
    {
        return $this->hasMany(
            ClientCategory::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function customReportTypes()
    {
        return $this->hasMany(
            CustomReportType::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function entityTagEntities()
    {
        return $this->hasMany(
            EntityTagEntity::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function nativeAccountAmounts()
    {
        return $this->hasMany(
            NativeAccountAmount::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function nativeAccountTypes()
    {
        return $this->hasMany(
            NativeAccountType::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function nativeCoas()
    {
        return $this->hasMany(
            NativeCoa::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function properties()
    {
        return $this->hasMany(
            Property::class,
            'client_id',
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
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function relatedUserTypes()
    {
        return $this->hasMany(
            RelatedUserType::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function reportTemplates()
    {
        return $this->hasMany(
            ReportTemplate::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function roles()
    {
        return $this->hasMany(
            Role::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function tenantAttributes()
    {
        return $this->hasMany(
            TenantAttribute::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function tenantIndustries()
    {
        return $this->hasMany(
            TenantIndustry::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function tenants()
    {
        return $this->hasMany(
            Tenant::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function users()
    {
        return $this->hasMany(
            User::class,
            'client_id',
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
            $rules = array_merge(Client::$baseRules, Client::$rules);
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
        return Client::class;
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
