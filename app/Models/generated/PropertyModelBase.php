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
 * Class Property
 *
 * @method static Property find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static Property|Collection findOrFail($id, $columns = ['*']) desc
 */
class PropertyModelBase extends Model
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
        'id'                          => 'sometimes|integer',
        'client_id'                   => 'sometimes|integer',
        'asset_type_id'               => 'sometimes|nullable|integer',
        'name'                        => 'required|max:255',
        'wp_property_id_old'          => 'sometimes|integer|nullable',
        'load_factor_old'             => 'sometimes|integer',
        'display_name'                => 'required|nullable|min:3|max:255',
        'property_code'               => 'sometimes|max:255',
        'original_property_code'      => 'sometimes|max:255',
        'property_owned'              => 'sometimes|max:255',
        'description'                 => 'sometimes|nullable|min:3|max:255',
        'active_status'               => 'required|max:255',
        'active_status_date'          => 'required',
        'property_id_old'             => 'sometimes|integer',
        'accounting_system'           => 'sometimes|max:255',
        'street_address'              => 'required|max:255',
        'display_address'             => 'required|max:255',
        'smartystreets_metadata'      => 'sometimes',
        'postal_code'                 => 'required|max:255',
        'city'                        => 'required|max:255',
        'state'                       => 'required|max:255',
        'state_abbr'                  => 'required|max:255',
        'country'                     => 'required|max:255',
        'country_abbr'                => 'required|max:255',
        'raw_upload'                  => 'sometimes',
        'longitude'                   => 'required|max:255',
        'latitude'                    => 'required|max:255',
        'census_tract'                => 'sometimes|max:255',
        'time_zone'                   => 'required|max:255',
        'suppress_address_validation' => 'sometimes|boolean',
        'address_validation_failed'   => 'sometimes|boolean',
        'square_footage'              => 'sometimes|numeric',
        'year_built'                  => 'sometimes|integer',
        'year_renovated'              => 'sometimes|integer',
        'number_of_buildings'         => 'sometimes|nullable|integer',
        'number_of_floors'            => 'sometimes|nullable|integer',
        'management_type'             => 'sometimes|max:255',
        'lease_type'                  => 'sometimes|max:255',
        'property_class'              => 'sometimes|max:255',
        'custom_attributes'           => 'sometimes|array_or_json_string',
        'image_json'                  => 'sometimes|array_or_json_string',
        'config_json'                 => 'sometimes|array_or_json_string',
        'region'                      => 'sometimes|max:255',
        'sub_region'                  => 'sometimes|max:255',
        'acquisition_date'            => 'sometimes',
        'investment_type'             => 'sometimes|max:255',
        'fund'                        => 'sometimes|max:255',
        'property_sub_type'           => 'sometimes|max:255',
        'ownership_entity'            => 'sometimes|max:255',
        'created_at'                  => 'sometimes',
        'updated_at'                  => 'sometimes',
    ];

    /**
     * @var array
     */
    public static $hasMany_arr = [
        "accessListProperty",
        "advancedVarianceThreshold",
        "advancedVariance",
        "calculatedFieldEquationProperty",
        "customReport",
        "ecmProject",
        "leaseSchedule",
        "lease",
        "nativeAccountAmount",
        "nativeAccountTypeTrailer",
        "opportunity",
        "propertyGroupProperty",
        "propertyNativeCoa",
        "suite",
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
        "assetType",
        "client",
    ];

    /**
     * @var array
     */
    public static $belongsToMany_arr = [
        "accessList",
        "calculatedFieldEquation",
        "propertyGroup",
        "nativeCoa",
    ];

    public function construct_scaffold()
    {
        $this->setTable('properties');
        $this->setFillable(
            [

                'client_id',
                'asset_type_id',
                'name',
                'wp_property_id_old',
                'load_factor_old',
                'display_name',
                'property_code',
                'original_property_code',
                'property_owned',
                'description',
                'active_status',
                'active_status_date',
                'property_id_old',
                'accounting_system',
                'street_address',
                'display_address',
                'smartystreets_metadata',
                'postal_code',
                'city',
                'state',
                'state_abbr',
                'country',
                'country_abbr',
                'raw_upload',
                'longitude',
                'latitude',
                'census_tract',
                'time_zone',
                'suppress_address_validation',
                'address_validation_failed',
                'square_footage',
                'year_built',
                'year_renovated',
                'number_of_buildings',
                'number_of_floors',
                'management_type',
                'lease_type',
                'property_class',
                'custom_attributes',
                'image_json',
                'config_json',
                'region',
                'sub_region',
                'acquisition_date',
                'investment_type',
                'fund',
                'property_sub_type',
                'ownership_entity',

            ]
        );
        $this->setCasts(
            [

                'id'                          => 'integer',
                'client_id'                   => 'integer',
                'asset_type_id'               => 'integer',
                'name'                        => 'string',
                'wp_property_id_old'          => 'integer',
                'load_factor_old'             => 'integer',
                'display_name'                => 'string',
                'property_code'               => 'string',
                'original_property_code'      => 'string',
                'property_owned'              => 'string',
                'description'                 => 'string',
                'active_status'               => 'string',
                'active_status_date'          => 'datetime',
                'property_id_old'             => 'integer',
                'accounting_system'           => 'string',
                'street_address'              => 'string',
                'display_address'             => 'string',
                'smartystreets_metadata'      => 'string',
                'postal_code'                 => 'string',
                'city'                        => 'string',
                'state'                       => 'string',
                'state_abbr'                  => 'string',
                'country'                     => 'string',
                'country_abbr'                => 'string',
                'raw_upload'                  => 'string',
                'longitude'                   => 'string',
                'latitude'                    => 'string',
                'census_tract'                => 'string',
                'time_zone'                   => 'string',
                'suppress_address_validation' => 'boolean',
                'address_validation_failed'   => 'boolean',
                'square_footage'              => 'float',
                'number_of_buildings'         => 'integer',
                'number_of_floors'            => 'integer',
                'management_type'             => 'string',
                'lease_type'                  => 'string',
                'property_class'              => 'string',
                'custom_attributes'           => 'string',
                'image_json'                  => 'string',
                'config_json'                 => 'string',
                'region'                      => 'string',
                'sub_region'                  => 'string',
                'acquisition_date'            => 'datetime',
                'investment_type'             => 'string',
                'fund'                        => 'string',
                'property_sub_type'           => 'string',
                'ownership_entity'            => 'string',

            ]
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function assetType()
    {
        return $this->belongsTo(
            AssetType::class,
            'asset_type_id',
            'id'
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
    public function accessLists()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            AccessList::class,
            'access_list_properties',
            'property_id',
            'access_list_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function calculatedFieldEquations()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            CalculatedFieldEquation::class,
            'calculated_field_equation_properties',
            'property_id',
            'calculated_field_equation_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function propertyGroups()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            PropertyGroup::class,
            'property_group_properties',
            'property_id',
            'property_group_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function nativeCoas()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            NativeCoa::class,
            'property_native_coas',
            'property_id',
            'native_coa_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function accessListProperties()
    {
        return $this->hasMany(
            AccessListProperty::class,
            'property_id',
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
            'property_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function advancedVariances()
    {
        return $this->hasMany(
            AdvancedVariance::class,
            'property_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function calculatedFieldEquationProperties()
    {
        return $this->hasMany(
            CalculatedFieldEquationProperty::class,
            'property_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function customReports()
    {
        return $this->hasMany(
            CustomReport::class,
            'property_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function ecmProjects()
    {
        return $this->hasMany(
            EcmProject::class,
            'property_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function leaseSchedules()
    {
        return $this->hasMany(
            LeaseSchedule::class,
            'property_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function leases()
    {
        return $this->hasMany(
            Lease::class,
            'property_id',
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
            'property_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function nativeAccountTypeTrailers()
    {
        return $this->hasMany(
            NativeAccountTypeTrailer::class,
            'property_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function opportunities()
    {
        return $this->hasMany(
            Opportunity::class,
            'property_id',
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
            'property_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function propertyNativeCoas()
    {
        return $this->hasMany(
            PropertyNativeCoa::class,
            'property_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function suites()
    {
        return $this->hasMany(
            Suite::class,
            'property_id',
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
            $rules = array_merge(Property::$baseRules, Property::$rules);
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
        return Property::class;
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
