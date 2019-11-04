<?php

namespace App\Waypoint\Models;

use App;
use App\Waypoint\AuditableTrait;
use App\Waypoint\CanConfigJSONTrait;
use App\Waypoint\CanImageJSONTrait;
use App\Waypoint\CanPreCalcJSONTrait;
use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\GetEntityTagsTrait;
use App\Waypoint\RelatedUserTrait;
use App\Waypoint\Repositories\PropertyRepository;
use App\Waypoint\Repositories\RelatedUserRepository;
use App\Waypoint\WeightedAverageLeaseExpirationTrait;
use Carbon\Carbon;
use Exception;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Contracts\UserResolver;
use App\Waypoint\HasAttachment;

/**
 * Class Property
 * @package App\Waypoint\Models
 */
class Property extends PropertyModelBase implements AuditableContract, UserResolver
{
    use GetEntityTagsTrait;
    use AuditableTrait;
    use RelatedUserTrait;
    use CanImageJSONTrait;
    use CanConfigJSONTrait;
    use CanPreCalcJSONTrait;
    use WeightedAverageLeaseExpirationTrait;
    use HasAttachment;

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'year_built'             => 'sometimes|nullable|integer|min:1699|max:2050',
        'property_code'          => 'required|max:255',
        'name'                   => 'required|max:255',
        'client_id'              => 'required|integer',
        'wp_property_id_old'     => 'sometimes|nullable|integer',
        'load_factor_old'        => 'sometimes|nullable|integer',
        'display_name'           => 'sometimes|max:255',
        'original_property_code' => 'sometimes|nullable|max:255',
        'property_owned'         => 'sometimes|max:255',
        'description'            => 'sometimes',
        'active_status'          => 'required|max:255',
        'active_status_date'     => 'required',
        'property_id_old'        => 'sometimes|nullable|integer',
        'accounting_system'      => 'sometimes|max:255',
        'street_address'         => 'sometimes|max:255',
        'display_address'        => 'sometimes|max:255',
        'smartystreets_metadata' => 'sometimes|array_or_json_string',
        'postal_code'            => 'sometimes|max:255',
        'longitude'              => 'sometimes|max:255',
        'latitude'               => 'sometimes|max:255',
        'census_tract'           => 'sometimes|max:255',
        'time_zone'              => 'sometimes|max:255',
        'property_class'         => 'sometimes|max:255',
        'lease_type'             => 'sometimes|max:255',
        'year_renovated'         => 'sometimes|nullable|integer|min:1|max:2050',
        'number_of_buildings'    => 'sometimes|nullable|integer|max:100',
        'number_of_floors'       => 'sometimes|nullable|integer|max:200',
        'custom_attributes'      => 'sometimes|array_or_json_string',
        'region'                 => 'sometimes|max:255',
        'sub_region'             => 'sometimes|max:255',
        'acquisition_date'       => 'sometimes',
        'investment_type'        => 'sometimes|max:255',
        'fund'                   => 'sometimes|max:255',
        'property_sub_type'      => 'sometimes|max:255',
        'ownership_entity'       => 'sometimes|max:255',
    ];

    public static $standard_attributes_arr = [
        'postal_code',
        'year_built',
        'management_type',
        'lease_type',
        'property_class',
        'year_renovated',
        'number_of_buildings',
        'number_of_floors',
        'region',
        'sub_region',
        'acquisition_date',
        'investment_type',
        'fund',
        'property_sub_type',
        'ownership_entity',
    ];

    const ACTIVE_STATUS_ACTIVE   = 'active';
    const ACTIVE_STATUS_INACTIVE = 'inactive';
    const ACTIVE_STATUS_LOCKED   = 'locked';
    const ACTIVE_STATUS_DEFAULT  = self::ACTIVE_STATUS_ACTIVE;
    public static $active_status_value_arr = [
        self::ACTIVE_STATUS_ACTIVE,
        self::ACTIVE_STATUS_INACTIVE,
        self::ACTIVE_STATUS_LOCKED,
    ];

    const YARDI                   = 'yardi';
    const OTHER_ACCOUNTING_SYSTEM = 'other_accounting_system';
    public static $accounting_system_value_arr = [
        self::YARDI,
        self::OTHER_ACCOUNTING_SYSTEM,
    ];

    /**
     * @var array
     * @deprecated ????????
     */
    public static $old_to_new_asset_type_hash = [
        1  => self::ASSET_TYPE_OFFICE,
        2  => self::ASSET_TYPE_OTHER,
        4  => self::ASSET_TYPE_RETAIL,
        5  => self::ASSET_TYPE_MANUFACTURING,
        6  => self::ASSET_TYPE_MIXED_USE,
        7  => self::ASSET_TYPE_WAREHOUSE,
        8  => self::ASSET_TYPE_MEDICAL_OFFICE,
        11 => self::ASSET_TYPE_FINANCIAL_75_PERCENT,
        12 => self::ASSET_TYPE_INDUSTRIAL_75_PERCENT,
        13 => self::ASSET_TYPE_MEDICAL_75_PERCENT,
        14 => self::ASSET_TYPE_CORPORATE_FACILITY,
        15 => self::ASSET_TYPE_GENERAL_MULTI_TENANT,
        16 => self::ASSET_TYPE_GOVERNMENT_OWNED,
        17 => self::ASSET_TYPE_SINGLE_PURPOSE,
        18 => self::ASSET_TYPE_CORPORATE_OFFICE,
        19 => self::ASSET_TYPE_GOVERNMENT_OFFICE,
        20 => self::ASSET_TYPE_INDUSTRIAL,
        21 => self::ASSET_TYPE_MULTIFAMILY,
        22 => self::ASSET_TYPE_NOT_AVAILABLE,
    ];

    /**
     * @var array
     * @deprecated ????????
     */
    public static $new_to_old_asset_type_hash = [
        self::ASSET_TYPE_OFFICE                => 1,
        self::ASSET_TYPE_OTHER                 => 2,
        self::ASSET_TYPE_RETAIL                => 4,
        self::ASSET_TYPE_MANUFACTURING         => 5,
        self::ASSET_TYPE_MIXED_USE             => 6,
        self::ASSET_TYPE_WAREHOUSE             => 7,
        self::ASSET_TYPE_MEDICAL_OFFICE        => 8,
        self::ASSET_TYPE_FINANCIAL_75_PERCENT  => 11,
        self::ASSET_TYPE_INDUSTRIAL_75_PERCENT => 12,
        self::ASSET_TYPE_MEDICAL_75_PERCENT    => 13,
        self::ASSET_TYPE_CORPORATE_FACILITY    => 14,
        self::ASSET_TYPE_GENERAL_MULTI_TENANT  => 15,
        self::ASSET_TYPE_GOVERNMENT_OWNED      => 16,
        self::ASSET_TYPE_SINGLE_PURPOSE        => 17,
        self::ASSET_TYPE_CORPORATE_OFFICE      => 18,
        self::ASSET_TYPE_GOVERNMENT_OFFICE     => 19,
        self::ASSET_TYPE_INDUSTRIAL            => 20,
        self::ASSET_TYPE_MULTIFAMILY           => 21,
        self::ASSET_TYPE_NOT_AVAILABLE         => 22,
    ];

    /**
     * @deprecated ????????
     */
    const ASSET_TYPE_OFFICE                = 'Office';
    const ASSET_TYPE_OTHER                 = 'Other';
    const ASSET_TYPE_RETAIL                = 'Retail';
    const ASSET_TYPE_MANUFACTURING         = 'Manufacturing';
    const ASSET_TYPE_MIXED_USE             = 'Mixed-Use';
    const ASSET_TYPE_WAREHOUSE             = 'Warehouse';
    const ASSET_TYPE_MEDICAL_OFFICE        = 'Medical Office';
    const ASSET_TYPE_FINANCIAL_75_PERCENT  = '75% Financial';
    const ASSET_TYPE_INDUSTRIAL_75_PERCENT = '75% Industrial';
    const ASSET_TYPE_MEDICAL_75_PERCENT    = '75% Medical';
    const ASSET_TYPE_CORPORATE_FACILITY    = 'Corporate Facility';
    const ASSET_TYPE_CORPORATE_OFFICE      = 'Corporate Office';
    const ASSET_TYPE_GENERAL_MULTI_TENANT  = 'General Multi-Tenant';
    const ASSET_TYPE_GOVERNMENT_OWNED      = 'Government Owned';
    const ASSET_TYPE_SINGLE_PURPOSE        = 'Single Purpose';
    const ASSET_TYPE_INDUSTRIAL            = 'Industrial';
    const ASSET_TYPE_MULTIFAMILY           = 'Multifamily';
    const ASSET_TYPE_HOSPITALITY           = 'Hospitality';
    const ASSET_TYPE_GOVERNMENT_OFFICE     = 'Government Office';
    const ASSET_TYPE_NOT_AVAILABLE         = '#N/A';
    const ASSET_TYPE_DEFAULT               = self::ASSET_TYPE_OFFICE;
    public static $asset_type_value_arr = [
        self::ASSET_TYPE_OFFICE,
        self::ASSET_TYPE_OTHER,
        self::ASSET_TYPE_RETAIL,
        self::ASSET_TYPE_MANUFACTURING,
        self::ASSET_TYPE_MIXED_USE,
        self::ASSET_TYPE_WAREHOUSE,
        self::ASSET_TYPE_MEDICAL_OFFICE,
        self::ASSET_TYPE_FINANCIAL_75_PERCENT,
        self::ASSET_TYPE_INDUSTRIAL_75_PERCENT,
        self::ASSET_TYPE_MEDICAL_75_PERCENT,
        self::ASSET_TYPE_CORPORATE_FACILITY,
        self::ASSET_TYPE_GENERAL_MULTI_TENANT,
        self::ASSET_TYPE_GOVERNMENT_OWNED,
        self::ASSET_TYPE_SINGLE_PURPOSE,
        self::ASSET_TYPE_INDUSTRIAL,
        self::ASSET_TYPE_MULTIFAMILY,
        self::ASSET_TYPE_HOSPITALITY,
        self::ASSET_TYPE_GOVERNMENT_OFFICE,
        self::ASSET_TYPE_NOT_AVAILABLE,
        self::ASSET_TYPE_CORPORATE_OFFICE,
    ];

    const MANAGEMENT_TYPE_1       = 'management_type_1';
    const MANAGEMENT_TYPE_2       = 'management_type_2';
    const MANAGEMENT_TYPE_3       = 'management_type_3';
    const MANAGEMENT_TYPE_4       = 'management_type_4';
    const MANAGEMENT_TYPE_DEFAULT = self::MANAGEMENT_TYPE_1;
    public static $management_type_value_arr = [
        self::MANAGEMENT_TYPE_1,
        self::MANAGEMENT_TYPE_2,
        self::MANAGEMENT_TYPE_3,
        self::MANAGEMENT_TYPE_4,
    ];

    const PROPERTY_CLASS_A       = 'Class A';
    const PROPERTY_CLASS_B       = 'Class B';
    const PROPERTY_CLASS_C       = 'Class C';
    const PROPERTY_CLASS_D       = 'Class D';
    const PROPERTY_CLASS_NONE    = 'None';
    const PROPERTY_CLASS_DEFAULT = self::PROPERTY_CLASS_NONE;
    public static $property_class_value_arr = [
        self::PROPERTY_CLASS_A,
        self::PROPERTY_CLASS_B,
        self::PROPERTY_CLASS_C,
        self::PROPERTY_CLASS_D,
        self::PROPERTY_CLASS_NONE,
    ];

    const LEASE_TYPE_MODIFIED_GROSS     = 'Modified Gross';
    const LEASE_TYPE_FULL_SERVICE_GROSS = 'Full Service Gross';
    const LEASE_TYPE_TRIPLE_NET         = 'Triple Net';
    const LEASE_TYPE_OTHER              = 'Other';
    const LEASE_TYPE_NONE               = 'None';

    /**
     * Per HER-420:
     * 'Modified Gross Lease' -> Map to "Modified Gross"
     * 'Full Service Lease' -> Map to "Full Service Gross"
     * 'please select one' -> Map to "Null value"
     * 'NNN' -> Map to "Triple Net
     * 'Gross' -> Map to "Full Service Gross"
     * 'Mod Gross' -> Map to "Modified Gross"
     */
    const LEASE_TYPE_DEFAULT = self::LEASE_TYPE_NONE;
    public static $lease_type_value_arr = [
        self::LEASE_TYPE_FULL_SERVICE_GROSS,
        self::LEASE_TYPE_TRIPLE_NET,
        self::LEASE_TYPE_MODIFIED_GROSS,
        self::LEASE_TYPE_OTHER,
        self::LEASE_TYPE_NONE,
    ];

    const THE_LAND_OF_THE_FREE      = 'United States of America';
    const THE_LAND_OF_THE_FREE_ABBR = 'US';

    /** @var null|object */
    const STATE_ABBR_TO_STATE_NAME = [
        'AL' => 'Alabama',
        'AK' => 'Alaska',
        'AZ' => 'Arizona',
        'AR' => 'Arkansas',
        'CA' => 'California',
        'CO' => 'Colorado',
        'CT' => 'Connecticut',
        'DE' => 'Delaware',
        'DC' => 'District of Columbia',
        'FL' => 'Florida',
        'GA' => 'Georgia',
        'HI' => 'Hawaii',
        'ID' => 'Idaho',
        'IL' => 'Illinois',
        'IN' => 'Indiana',
        'IA' => 'Iowa',
        'KS' => 'Kansas',
        'KY' => 'Kentucky',
        'LA' => 'Louisiana',
        'ME' => 'Maine',
        'MD' => 'Maryland',
        'MA' => 'Massachusetts',
        'MI' => 'Michigan',
        'MN' => 'Minnesota',
        'MS' => 'Mississippi',
        'MO' => 'Missouri',
        'MT' => 'Montana',
        'NE' => 'Nebraska',
        'NV' => 'Nevada',
        'NH' => 'New Hampshire',
        'NJ' => 'New Jersey',
        'NM' => 'New Mexico',
        'NY' => 'New York',
        'NC' => 'North Carolina',
        'ND' => 'North Dakota',
        'OH' => 'Ohio',
        'OK' => 'Oklahoma',
        'OR' => 'Oregon',
        'PA' => 'Pennsylvania',
        'RI' => 'Rhode Island',
        'SC' => 'South Carolina',
        'SD' => 'South Dakota',
        'TN' => 'Tennessee',
        'TX' => 'Texas',
        'UT' => 'Utah',
        'VT' => 'Vermont',
        'VA' => 'Virginia',
        'WA' => 'Washington',
        'WV' => 'West Virginia',
        'WI' => 'Wisconsin',
        'WY' => 'Wyoming',
    ];
    const STATE_NAME_TO_ABBR_NAME  = [
        'Alabama'              => 'AL',
        'Alaska'               => 'AK',
        'Arizona'              => 'AZ',
        'Arkansas'             => 'AR',
        'California'           => 'CA',
        'Colorado'             => 'CO',
        'Connecticut'          => 'CT',
        'Delaware'             => 'DE',
        'District of Columbia' => 'DC',
        'Florida'              => 'FL',
        'Georgia'              => 'GA',
        'Hawaii'               => 'HI',
        'Idaho'                => 'ID',
        'Illinois'             => 'IL',
        'Indiana'              => 'IN',
        'Iowa'                 => 'IA',
        'Kansas'               => 'KS',
        'Kentucky'             => 'KY',
        'Louisiana'            => 'LA',
        'Maine'                => 'ME',
        'Maryland'             => 'MD',
        'Massachusetts'        => 'MA',
        'Michigan'             => 'MI',
        'Minnesota'            => 'MN',
        'Mississippi'          => 'MS',
        'Missouri'             => 'MO',
        'Montana'              => 'MT',
        'Nebraska'             => 'NE',
        'Nevada'               => 'NV',
        'New Hampshire'        => 'NH',
        'New Jersey'           => 'NJ',
        'New Mexico'           => 'NM',
        'New York'             => 'NY',
        'North Carolina'       => 'NC',
        'North Dakota'         => 'ND',
        'Ohio'                 => 'OH',
        'Oklahoma'             => 'OK',
        'Oregon'               => 'OR',
        'Pennsylvania'         => 'PA',
        'Rhode Island'         => 'RI',
        'South Carolina'       => 'SC',
        'South Dakota'         => 'SD',
        'Tennessee'            => 'TN',
        'Texas'                => 'TX',
        'Utah'                 => 'UT',
        'Vermont'              => 'VT',
        'Virginia'             => 'VA',
        'Washington'           => 'WA',
        'West Virginia'        => 'WV',
        'Wisconsin'            => 'WI',
        'Wyoming'              => 'WY',
    ];
    /**
     * @var array
     * See http://www.laravel-auditing.com/docs/4.0/behavior-settings
     */
    protected $auditInclude = [
        'name',
        'display_name',
        'property_code',
        'original_property_code',
        'property_owned',
        'description',
        'active_status',
        'active_status_date',
        'accounting_system',
        'street_address',
        'city',
        'state',
        'country',
        'postal_code',
        'longitude',
        'latitude',
        'census_tract',
        'time_zone',
        'square_footage',
        'asset_type_id',
        'year_built',
        'management_type',
        'lease_type',
        'user_name',
        'property_class',
        'year_renovated',
        'number_of_buildings',
        'number_of_floors',
        'custom_attributes',
        'region',
        'sub_region',
        'acquisition_date',
        'investment_type',
        'fund',
        'property_sub_type',
        'ownership_entity',
        'ownership_entity',
        'original_property_code',
        'property_owned',
        'year_renovated',
        'number_of_buildings',
        'number_of_floors',
        'property_class',
        'custom_attributes',
        'region',
        'sub_region',
        'acquisition_date',
        'investment_type',
        'fund',
        'property_sub_type',
        'ownership_entity',
    ];

    /** @var Collection|null $ActiveLeaseDetailObjArr */
    protected $ActiveLeaseDetailObjArr = null;
    /** @var Collection|null $ActiveUniqueLeaseDetailObjArr */
    protected $ActiveUniqueLeaseDetailObjArr = null;
    /** @var boolean */
    public $auditIncludeRelated = false;
    /** @var \stdClass */
    private $CustomAttributeJSONObj = null;
    /** @var null|Collection */
    private $AccessibleUserObjArr = null;

    /**
     * Property constructor.
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
        $rules                   = parent::get_model_rules($rules, $object_id);
        $rules['property_class'] = 'sometimes|nullable|string|max:255|in:' . implode(',', Property::$property_class_value_arr);
        $rules['lease_type']     = 'sometimes|nullable|string|max:255|in:' . implode(',', Property::$lease_type_value_arr);
        $rules['active_status']  = 'required|string|max:255|in:' . implode(',', Property::$active_status_value_arr);
        return $rules;
    }

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {

        self::setSuspendValidation(false);

        return [
            'id'                          => $this->id,
            'client_id'                   => $this->client_id,
            'name'                        => $this->name,
            'display_name'                => $this->display_name,
            'property_code'               => $this->property_code,
            'description'                 => $this->description,
            'active_status'               => $this->active_status,
            'active_status_date'          => $this->perhaps_format_date($this->active_status_date),
            'property_id_old'             => $this->property_id_old,
            'wp_property_id_old'          => $this->wp_property_id_old,
            'load_factor_old'             => $this->load_factor_old,
            'accounting_system'           => $this->accounting_system,
            'street_address'              => $this->street_address,
            'display_address'             => $this->display_address,
            'city'                        => $this->city,
            'state'                       => $this->state,
            'state_abbr'                  => $this->state_abbr,
            'postal_code'                 => $this->postal_code,
            'country'                     => $this->country,
            'country_abbr'                => $this->country_abbr,
            'longitude'                   => (float) $this->longitude,
            'latitude'                    => (float) $this->latitude,
            'suppress_address_validation' => $this->suppress_address_validation,
            'address_validation_failed'   => $this->address_validation_failed,
            'census_tract'                => $this->census_tract,
            'time_zone'                   => $this->time_zone,
            'square_footage'              => $this->square_footage,
            'year_built'                  => is_numeric($this->year_built) ? $this->year_built : null,
            'management_type'             => $this->management_type,
            'original_property_code'      => $this->original_property_code,
            'lease_type'                  => $this->lease_type,
            'property_class'              => $this->property_class,
            'year_renovated'              => $this->year_renovated,
            'number_of_buildings'         => $this->number_of_buildings,
            'number_of_floors'            => $this->number_of_floors,
            'property_owned'              => $this->property_owned,
            'custom_attributes'           => json_decode($this->custom_attributes, true),
            'region'                      => $this->region,
            'sub_region'                  => $this->sub_region,
            'acquisition_date'            => $this->perhaps_format_date($this->acquisition_date),
            'investment_type'             => $this->investment_type,
            'fund'                        => $this->fund,
            'property_sub_type'           => $this->property_sub_type,
            'ownership_entity'            => $this->ownership_entity,
            'relatedUserTypes'            => $this->getRelatedUserTypes(Property::class, $this->id)->toArray(),
            "config_json"                 => json_decode($this->config_json, true),
            "image_json"                  => json_decode($this->image_json, true),
            "asset_type_id"               => $this->asset_type_id,
            "assetType"                   => $this->assetType ? $this->assetType->toArray() : null,
            "created_at"                  => $this->perhaps_format_date($this->created_at),
            "updated_at"                  => $this->perhaps_format_date($this->updated_at),
            "model_name"                  => self::class,
        ];
    }

    /**
     * @param $attribute_name
     * @return null
     * @throws GeneralException
     */
    public function getCustomAttribute($attribute_name)
    {
        try
        {
            if (isset($this->getCustomAttributeJSONObj()->$attribute_name))
            {
                return $this->getCustomAttributeJSONObj()->$attribute_name;
            }
            else
            {
                return null;
            }
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException('invalid CustomAttributeJSONObj', 404, $e);
        }
    }

    /**
     * @param $attribute_name
     * @param null $attribute_value
     * @throws GeneralException
     */
    public function setCustomAttribute($attribute_name, $attribute_value = null)
    {
        /**
         * null custom attributes are not allowed, think about it
         */
        if ( ! $attribute_name)
        {
            throw new GeneralException('invalid $attribute_value', 404);
        }
        $CustomAttributeJSONObj = $this->getCustomAttributeJSONObj();
        if ($attribute_value === null)
        {
            if (isset($CustomAttributeJSONObj->$attribute_name))
            {
                unset($CustomAttributeJSONObj->$attribute_name);
            }
        }
        else
        {
            $CustomAttributeJSONObj->$attribute_name = $attribute_value;
        }
        $this->setCustomAttributeJSONObj($CustomAttributeJSONObj);
    }

    /**
     * @param array $options
     * @return $this
     * @throws \App\Waypoint\Exceptions\ValidationException
     *
     * @todo this could be better - learn to use messagebags https://laravel.com/docs/5.4/validation#working-with-error-messages
     */
    public function save(array $options = [])
    {
        parent::save($options);
        return $this;
    }

    /**
     * @param bool $return_array
     * @return array|null|object
     * @throws GeneralException
     */
    public function getCustomAttributeJSONObj($return_array = false)
    {
        if ( ! $this->CustomAttributeJSONObj)
        {
            if ( ! is_valid_json($this->custom_attributes))
            {
                throw new GeneralException('invalid custom_attributes detected for client ' . $this->client->name . ' property ' . $this->name);
            }
            $this->CustomAttributeJSONObj = (object) json_decode($this->custom_attributes);
        }
        if ($return_array)
        {
            return (array) $this->CustomAttributeJSONObj;
        }
        return $this->CustomAttributeJSONObj;
    }

    /**
     * @param null $CustomAttributeJSONObj
     */
    public function setCustomAttributeJSONObj($CustomAttributeJSONObj)
    {
        if (is_object($CustomAttributeJSONObj))
        {
            $custom_attribute_arr = (array) $CustomAttributeJSONObj;
        }
        elseif (is_array($CustomAttributeJSONObj))
        {
            $custom_attribute_arr = $CustomAttributeJSONObj;
        }
        else
        {
            throw new GeneralException('invalid $CustomAttributeJSONObj');
        }
        $custom_attribute_encoded = json_encode($custom_attribute_arr);
        $this->custom_attributes  = $custom_attribute_encoded;
        $this->save();
        $this->CustomAttributeJSONObj = (object) json_decode($this->custom_attributes);
    }

    /**
     * @return array|null|object
     * @throws GeneralException
     */
    public function getAccessibleUserObjArr()
    {
        if ($this->AccessibleUserObjArr !== null)
        {
            return $this->AccessibleUserObjArr;
        }
        $this->AccessibleUserObjArr =
            $this->accessLists
                ->map(
                    function (Accesslist $AccessListObj)
                    {
                        return $AccessListObj->accessListUsers;
                    }
                )
                ->flatten()
                ->unique('user_id')
                ->map(
                    function (AccessListUser $AccessListUserObj)
                    {
                        return $AccessListUserObj->user;
                    }
                )->filter(

                    function (User $UserObj)
                    {
                        return ! $UserObj->is_hidden;
                    }
                );
        return $this->AccessibleUserObjArr;
    }

    /**
     * @return App\Waypoint\Collection
     */
    public function getRelatedUsers()
    {
        $RelatedUserRepository = App::make(RelatedUserRepository::class);
        return $RelatedUserRepository->getRelatedUsersByProperty($this->id);
    }

    /**
     * @param integer $user_id
     * @param null $related_object_subtype
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function add_related_user($user_id, $related_object_subtype = null)
    {
        App::make(PropertyRepository::class)->add_user($user_id, $this->id, $related_object_subtype);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function advancedVarianceSummaries()
    {
        return $this->hasMany(
            AdvancedVarianceSummary::class,
            'property_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function accessListDetails()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            AccessListDetail::class,
            'access_list_properties',
            'property_id',
            'access_list_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function suiteDetails()
    {
        return $this->hasMany(
            SuiteDetail::class,
            'property_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function leaseDetails()
    {
        return $this->hasMany(
            LeaseDetail::class,
            'property_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function tenantDetails()
    {
        return $this->hasMany(
            TenantDetail::class,
            'tenant_id',
            'id'
        );
    }

    /**
     * @return Collection
     */
    public function getActiveLeaseDetailObjArr()
    {
        if ($this->ActiveLeaseDetailObjArr)
        {
            return $this->ActiveLeaseDetailObjArr;
        }

        $this->ActiveLeaseDetailObjArr =
            $this->leaseDetails
                ->filter(
                    function (LeaseDetail $LeaseDetailObj)
                    {
                        return
                            Lease::check_model_date_range($LeaseDetailObj);
                    }
                )
                ->flatten()
                ->filter(function ($item) { return $item; });  // remove nulls

        return $this->ActiveLeaseDetailObjArr;
    }

    /**
     * @return Collection
     */
    public function getActiveUniqueLeaseDetailObjArr(): Collection
    {
        if ($this->ActiveUniqueLeaseDetailObjArr)
        {
            return $this->ActiveUniqueLeaseDetailObjArr;
        }

        $this->ActiveUniqueLeaseDetailObjArr =
            $this->suiteDetails
                ->map(
                    function (SuiteDetail $SuiteDetailObj)
                    {
                        $LeaseObjArr = $SuiteDetailObj->leaseDetails
                            ->filter(
                                function (LeaseDetail $LeaseDetailObj)
                                {
                                    return
                                        Lease::check_model_date_range($LeaseDetailObj);
                                }
                            );

                        /**
                         * the earlier lease_expiration_date wins
                         */
                        $LeaseObjArr = $LeaseObjArr->sortByDesc('lease_expiration_date');
                        $LeaseObj    = $LeaseObjArr->first();
                        return $LeaseObj;
                    }
                )
                ->filter(function ($item) { return $item; }); // remove nulls

        return $this->ActiveUniqueLeaseDetailObjArr;
    }

    /**
     * See NativeChartAmountController
     * @var Carbon
     */
    public static $nativeAccountAmountsFilteredFromDate = null;
    /** @var Carbon */
    public static $nativeAccountAmountsFilteredToDate = null;
    /** @var [] */
    public static $nativeAccountAmountsFilteredNativeAccountIds = null;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function nativeAccountAmountsFiltered()
    {
        if (
            self::$nativeAccountAmountsFilteredFromDate &&
            self::$nativeAccountAmountsFilteredToDate &&
            self::$nativeAccountAmountsFilteredNativeAccountIds
        )
        {
            return
                $this
                    ->hasMany(
                        NativeAccountAmount::class,
                        'property_id',
                        'id'
                    )
                    ->whereDate('month_year_timestamp', '>=', self::$nativeAccountAmountsFilteredFromDate->format('Y-m-d') . ' 00:00:00')
                    ->whereDate('month_year_timestamp', '<=', self::$nativeAccountAmountsFilteredToDate->format('Y-m-d') . ' 00:00:00')
                    ->whereIn('native_account_id', self::$nativeAccountAmountsFilteredNativeAccountIds);
        }
        elseif (
            self::$nativeAccountAmountsFilteredFromDate &&
            self::$nativeAccountAmountsFilteredToDate
        )
        {
            return
                $this
                    ->hasMany(
                        NativeAccountAmount::class,
                        'property_id',
                        'id'
                    )
                    ->whereDate('month_year_timestamp', '>=', self::$nativeAccountAmountsFilteredFromDate)
                    ->whereDate('month_year_timestamp', '<=', self::$nativeAccountAmountsFilteredToDate);
        }
        elseif (self::$nativeAccountAmountsFilteredNativeAccountIds)
        {
            return
                $this
                    ->hasMany(
                        NativeAccountAmount::class,
                        'property_id',
                        'id'
                    )
                    ->whereIn('is_default_advance_variance_report_template', self::$nativeAccountAmountsFilteredNativeAccountIds);
        }
        /**
         * if flow of control gets here, you really know what you are doing or
         * doing something wrong
         */
        return
            $this
                ->hasMany(
                    NativeAccountAmount::class,
                    'property_id',
                    'id'
                );
    }

    /**
     * @return mixed
     */
    public function suppress_pre_calc_events()
    {
        if (isset($this->client->getConfigJSON()->SUPPRESS_PRE_CALC_EVENTS))
        {
            return $this->client->getConfigJSON()->SUPPRESS_PRE_CALC_EVENTS;
        }
        return config('waypoint.suppress_pre_calc_events', true);
    }

    /**
     * @return mixed
     */
    public function suppress_pre_calc_usage()
    {
        if (isset($this->client->getConfigJSON()->SUPPRESS_PRE_CALC_USAGE))
        {
            return $this->client->getConfigJSON()->SUPPRESS_PRE_CALC_USAGE;
        }
        return config('waypoint.suppress_pre_calc_usage', true);
    }
}
