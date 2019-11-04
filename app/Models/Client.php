<?php

namespace App\Waypoint\Models;

use \Actuallymab\LaravelComment\Commentable;
use App;
use App\Waypoint\AuditableTrait;
use App\Waypoint\CanConfigJSONTrait;
use App\Waypoint\CanImageJSONTrait;
use App\Waypoint\CanPreCalcJSONTrait;
use App\Waypoint\CanStyleJSONTrait;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Model;
use App\Waypoint\RelatedUserTrait;
use App\Waypoint\Repositories\ReportTemplateRepository;
use App\Waypoint\Collection;
use App\Waypoint\GetEntityTagsTrait;
use App\Waypoint\Repositories\ClientRepository;
use App\Waypoint\Repositories\AccessListUserRepository;
use Cache;
use Carbon\Carbon;
use DB;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Contracts\UserResolver;
use App\Waypoint\Repositories\NativeAccountTypeSummaryRepository;

/**
 * Class Client
 * @package App\Waypoint\Models
 */
class Client extends ClientModelBase implements AuditableContract, UserResolver
{
    use GetEntityTagsTrait;
    use AuditableTrait;
    use RelatedUserTrait;

    use CanImageJSONTrait;
    use CanConfigJSONTrait;
    use CanStyleJSONTrait;
    use CanPreCalcJSONTrait;
    use Commentable;

    /** @var null|[] */
    protected $property_group_id_arr = null;
    /** @var null|[] */
    protected $property_id_arr = null;
    /** @var object */
    protected $ClientConfigObj = null;

    /**
     * Validation rules which get 'merged' with self::$baseRules at $this::__constructor() time
     *
     * @var array
     */
    public static $rules = [

        'name'                               => 'required|min:3|string|max:255|unique_with:clients,object_id',
        'description'                        => 'sometimes|nullable|min:3|string|max:255',
        'client_code'                        => 'required|min:3|string|max:255',
        'display_name'                       => 'required|min:3|string|max:255|unique_with:clients,object_id',
        'client_id_old'                      => 'sometimes|nullable|integer',
        'display_name_old'                   => 'sometimes|max:255',
        'active_status_date'                 => 'sometimes',
        'sftp_host_name'                     => 'sometimes|max:255',
        'sftp_user_name'                     => 'sometimes|max:255',
        'sftp_password'                      => 'sometimes|max:255',
        'property_group_calc_last_requested' => 'sometimes',
    ];

    /**
     * @var array
     * See http://www.laravel-auditing.com/docs/4.0/behavior-settings
     */
    protected $auditInclude = [
        'name',
        'display_name',
        'description',
        'client_code',
        'active_status',
        'property_group_calc_status',
        'display_name_old',
        'sftp_host_name',
        'sftp_user_name',
        'sftp_password',
        'property_group_force_recalc',
        'property_group_force_first_time_calc',
        'property_group_force_calc_property_group_ids',
    ];

    const WAYPOINT_LEDGER_DROPDOWNS    = 'WAYPOINT_LEDGER_DROPDOWNS';
    const DEFAULTS_CONFIG_KEY          = 'DEFAULTS';
    const FILTERS_CONFIG_KEY           = 'FILTERS';
    const FEATURE_OCCUPANCY_TREND_FLAG = 'FEATURE_OCCUPANCY_TREND';
    const MULTIFAMILY_ASSET_TYPE_TEXT  = 'Multi-Family';

    /**
     * Client constructor.
     * @param array $attributes
     * @throws App\Waypoint\Exceptions\GeneralException
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
        $rules                  = parent::get_model_rules($rules, $object_id);
        $rules['active_status'] = 'required|in:' . implode(',', Client::$active_status_values);
        /**
         * Make this required after we re-vamp migrations
         */
        $rules['property_group_calc_status'] = 'sometimes|in:' . implode(',', Client::$property_group_calc_status_values);

        return $rules;
    }

    /** @var  PropertyGroup */
    protected $AllPropertyGroupObj;

    /** @var  AccessList */
    protected $AllAccessListObj;

    /** @var  Collection */
    protected $EcmProjects;

    const DUMMY_CLIENT_NAME = 'Dummy Client';

    const ACTIVE   = 'active';
    const INACTIVE = 'inactive';
    const LOCKED   = 'locked';
    const DUMMY    = 'dummy';
    public static $active_status_values = [
        self::ACTIVE,
        self::INACTIVE,
        self::LOCKED,
        self::DUMMY,
    ];

    const PROPERTY_GROUP_CALC_STATUS_WAITING = 'waiting';
    const PROPERTY_GROUP_CALC_STATUS_IDLE    = 'idle';
    public static $property_group_calc_status_values = [
        self::PROPERTY_GROUP_CALC_STATUS_WAITING,
        self::PROPERTY_GROUP_CALC_STATUS_IDLE,
    ];

    const CUSTOM_REPORT_TEMPLATE_ANALYTICS_FLAG = 'CUSTOM_REPORT_TEMPLATE_ANALYTICS';
    const DECIMAL_DISPLAY_FLAG                  = 'DECIMAL_DISPLAY';
    const DECIMAL_DISPLAY_DEFUALT_VALUE         = true;
    const NEGATIVE_VALUE_SYMBOLS_FLAG           = 'NEGATIVE_VALUE_SYMBOLS';
    const NEGATIVE_VALUE_SYMBOLS_DEFAULT_VALUE  = 'CURRENCY';

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"                                           => $this->id,
            'client_id_old'                                => $this->client_id_old,
            "name"                                         => $this->name,
            "description"                                  => $this->description,
            "display_name_old"                             => $this->display_name_old ? $this->display_name_old : null,
            "active_status"                                => $this->active_status,
            "client_code"                                  => $this->client_code,
            "display_name"                                 => $this->display_name,
            "property_group_calc_status"                   => $this->property_group_calc_status,
            "property_group_force_first_time_calc"         => $this->property_group_force_first_time_calc,
            "property_group_force_calc_property_group_ids" => $this->property_group_force_calc_property_group_ids,
            /**
             * @todo get consistant about dates
             */
            "property_group_force_recalc"                  => $this->property_group_force_recalc,
            "property_group_calc_last_requested"           => $this->perhaps_format_date($this->property_group_calc_last_requested),
            "active_status_date"                           => $this->perhaps_format_date($this->active_status_date),

            "config_json" => json_decode($this->config_json, true),
            "style_json"  => json_decode($this->style_json, true),
            "image_json"  => json_decode($this->image_json, true),

            "dormant_user_switch" => $this->dormant_user_switch,
            "dormant_user_ttl"    => $this->dormant_user_ttl,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function userDetails()
    {
        return $this->hasMany(
            UserDetail::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return Collection
     */
    public function getEcmProjects()
    {
        return $this->properties
            ->map(
                function (Property $PropertyObj)
                {
                    return $PropertyObj->ecmProjects;
                }
            )
            ->flatten();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function propertyDetails()
    {
        return $this->hasMany(
            PropertyDetail::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     **/
    public function allAccessList()
    {
        return $this->hasOne(
            AccessList::class,
            'client_id',
            'id'
        )->where('is_all_access_list', 1);
    }

    /**
     * @return array
     **/
    public function getPropertyGroupIdArr()
    {
        if ($this->property_group_id_arr)
        {
            return $this->property_group_id_arr;
        }
        $this->property_group_id_arr = $this->propertyGroups->pluck('id');

        return $this->property_group_id_arr;
    }

    /**
     * @return null
     */
    public function getPropertyIdArr()
    {
        if ($this->property_id_arr)
        {
            return $this->property_id_arr;
        }
        $this->property_id_arr = $this->properties->pluck('id')->toArray();

        return $this->property_id_arr;
    }

    /**
     * @param integer $user_id
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function addUserToAllAccessList($user_id)
    {
        /** @var AccessListUserRepository $AccessListUserRepositoryObj */
        $AccessListUserRepositoryObj = App::make(AccessListUserRepository::class);
        /** @var AccessListUserRepository $AccessListUserObj */
        if ( ! $AccessListUserObj = $AccessListUserRepositoryObj->findWhere(
            [
                'user_id'        => $user_id,
                'access_list_id' => $this->allAccessList->id,
            ]
        )->first()
        )
        {
            $AccessListUserRepositoryObj->create(
                [
                    'user_id'        => $user_id,
                    'access_list_id' => $this->allAccessList->id,
                ]
            );
        }
    }

    /**
     * @param integer $user_id
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function removeUserToAllAccessList($user_id)
    {
        /** @var AccessListUserRepository $AccessListUserRepositoryObj */
        $AccessListUserRepositoryObj = App::make(AccessListUserRepository::class);
        if ($AccessListUserObj = $AccessListUserRepositoryObj->findWhere(
            [
                'user_id'        => $user_id,
                'access_list_id' => $this->allAccessList->id,
            ]
        )->first()
        )
        {
            $AccessListUserRepositoryObj->delete($AccessListUserObj->id);
        }
    }

    /**
     * @return array
     *
     *  This is used to populate the native account types for the client config object only
     *  as the analytics default report template is now stored in the user config
     */
    public function getNativeAccountTypesSummarySpecial()
    {
        $return_me = [];

        /** @var NativeAccountTypeSummary $NativeAccountTypeSummaryObj */
        foreach ($this->nativeAccountTypeSummaries as $NativeAccountTypeSummaryObj)
        {
            $NativeAccountTypeSummaryArr                                     = $NativeAccountTypeSummaryObj->toArray();
            $report_template_account_group_id                                = $this->getReportTemplateAccountGroupFromNativeAccountType($NativeAccountTypeSummaryArr['id'])->id;
            $NativeAccountTypeSummaryArr['report_template_account_group_id'] = $report_template_account_group_id;
            if ( ! is_null($NativeAccountTypeSummaryArr['report_template_account_group_id']))
            {
                $return_me[] = $NativeAccountTypeSummaryArr;
            }
        }
        return $return_me;
    }

    /**
     * @param $native_account_type_id
     * @return mixed
     */
    public function getReportTemplateAccountGroupFromNativeAccountType($native_account_type_id)
    {
        $ReportTemplateObj = $this->defaultAdvancedVarianceReportTemplate;

        $ReportTemplateAccountGroupRepositoryObj = App::make(App\Waypoint\Repositories\ReportTemplateAccountGroupRepository::class);
        return $ReportTemplateAccountGroupRepositoryObj
            ->findWhere(
                [
                    'report_template_id'                      => $ReportTemplateObj->id,
                    'parent_report_template_account_group_id' => null,
                    'native_account_type_id'                  => $native_account_type_id,
                ]
            )->first();
    }

    /**
     * @param $native_account_type_id
     * @return mixed
     */
    public function getNativeAccountTypeSummaryWithReportTemplateAccountGroupFromNativeAccountType($native_account_type_id)
    {
        $ReportTemplateObj = $this->defaultAdvancedVarianceReportTemplate;

        $ReportTemplateAccountGroupRepositoryObj = App::make(App\Waypoint\Repositories\ReportTemplateAccountGroupRepository::class);
        $NativeAccountTypeSummaryRepositoryObj   = App::make(NativeAccountTypeSummaryRepository::class);

        $ReportTemplateAccountGroupObj =
            $ReportTemplateAccountGroupRepositoryObj
                ->findWhere(
                    [
                        'report_template_id'                      => $ReportTemplateObj->id,
                        'parent_report_template_account_group_id' => null,
                        'native_account_type_id'                  => $native_account_type_id,
                    ]
                )->first();

        if ( ! $NativeAccountTypeSummaryObj = $NativeAccountTypeSummaryRepositoryObj->find($native_account_type_id))
        {
            throw new GeneralException('could not find account type summary from id given');
        }

        $NativeAccountTypeSummaryObj->report_template_account_group_id = $ReportTemplateAccountGroupObj->id;

        return $NativeAccountTypeSummaryObj;
    }

    /**
     * @param ReportTemplateAccountGroup $ReportTemplateAccountGroupObj
     * @return mixed
     */
    public function getNativeAccountTypeSummaryIncludingRTAG($ReportTemplateAccountGroupObj)
    {
        $NativeAccountTypeSummaryRepositoryObj = App::make(NativeAccountTypeSummaryRepository::class);

        if ( ! $NativeAccountTypeSummaryObj = $NativeAccountTypeSummaryRepositoryObj->find($ReportTemplateAccountGroupObj->native_account_type_id))
        {
            throw new GeneralException('could not find account type summary from id given');
        }

        $NativeAccountTypeSummaryObj->report_template_account_group_id = $ReportTemplateAccountGroupObj->id;

        return $NativeAccountTypeSummaryObj;
    }

    /**
     * @return array
     */
    public function getNativeAccountTypesHash()
    {
        $return_me = [];

        foreach ($this->reportTemplates as $ReportTemplateObj)
        {
            /** @var ReportTemplate */
            foreach ($ReportTemplateObj->reportTemplateAccountGroups->filter(
                function ($ReportTemplateAccountGroupObj)
                {
                    return $ReportTemplateAccountGroupObj->parent_report_template_account_group_id === null;
                }
            ) as $ReportTemplateAccountGroupObj)
            {
                $return_me_temp                                          = $ReportTemplateAccountGroupObj->nativeAccountType->toArray();
                $return_me_temp['report_template_account_group_id']      = $ReportTemplateAccountGroupObj->id;
                $return_me['ReportTemplate_' . $ReportTemplateObj->id][] = $return_me_temp;
            }
        }

        return $return_me;
    }

    /**
     * @return mixed
     */
    public function getStandardAttributeUniqueValues()
    {
        $return_me_arr = [];
        foreach ($this->properties as $PropertyObj)
        {
            foreach ((array) json_decode($PropertyObj->custom_attributes) as $custom_attribute_name => $custom_attribute_value)
            {
                if (isset($return_me_arr[$custom_attribute_name][$custom_attribute_value]))
                {
                    $return_me_arr[$custom_attribute_name][$custom_attribute_value]++;
                }
                else
                {
                    $return_me_arr[$custom_attribute_name][$custom_attribute_value] = 1;
                }
            }
        }
        return $return_me_arr;
    }

    /**
     * @return mixed
     */
    public function getCustomAttributeUniqueValues()
    {
        $return_me_arr = [];
        foreach ($this->properties as $PropertyObj)
        {
            foreach (Property::$standard_attributes_arr as $standard_attribute)
            {
                if (is_object($PropertyObj->$standard_attribute) && get_class($PropertyObj->$standard_attribute) == Carbon::class)
                {
                    $standard_attribute_value = $PropertyObj->$standard_attribute->format('Y-m-d H:i:s');
                }
                else
                {
                    $standard_attribute_value = $PropertyObj->$standard_attribute;
                }
                if ($standard_attribute_value)
                {
                    if ( ! isset($return_me_arr[$standard_attribute][$standard_attribute_value]['properties']))
                    {
                        $return_me_arr[$standard_attribute][$standard_attribute_value]['properties'] = [];
                    }

                    $return_me_arr[$standard_attribute][$standard_attribute_value]['properties'] = $PropertyObj->id;
                }
            }
        }
        return $return_me_arr;
    }

    /**
     * @return Collection|mixed
     */
    public function getOpportunities()
    {
        return App::make(ClientRepository::class)->getOpportunityObjArrForClient($this->id);
    }

    /**
     * @return Collection|mixed
     */
    public function getAdvancedVariances()
    {
        return App::make(ClientRepository::class)->getAdvancedVarianceObjArrForClient($this->id);
    }

    /**
     * @return mixed
     */
    public function getBomaReportTemplateObj()
    {
        $BomaReportTemplateObj = App::make(ReportTemplateRepository::class)->findWhere(
            [
                'client_id'               => $this->id,
                'is_boma_report_template' => true,
            ]
        )->first();

        return $BomaReportTemplateObj;
    }

    /**
     * @return mixed
     *
     * Something to note here is that instead of checking the client config object for
     * the default analytics report template (which used to be referenced on the report
     * template object at a client level), we check the user config object due to the default
     * analytics report template being stored for each user independently from now on
     */
    public function getDefaultAnalyticsReportTemplate()
    {
        return App::make(App\Waypoint\Repositories\UserRepository::class)->getDefaultAnalyticsReportTemplate();
    }

    /**
     * @return mixed
     */
    public function getDefaultAdvancedVarianceThresholds()
    {
        return $this->advancedVarianceThresholds->filter(
            function ($AdvancedVarianceThresholdObj)
            {
                return ! $AdvancedVarianceThresholdObj->native_account_id &&
                       ! $AdvancedVarianceThresholdObj->native_account_type_id &&
                       ! $AdvancedVarianceThresholdObj->report_template_account_group_id &&
                       ! $AdvancedVarianceThresholdObj->calculated_field_id;
            }
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function tenantDetails()
    {
        return $this->hasMany(
            TenantDetail::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function tenantIndustryDetails()
    {
        return $this->hasMany(
            TenantIndustryDetail::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function tenantAttributeDetails()
    {
        return $this->hasMany(
            TenantAttributeDetail::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function nativeAccountTypeSummaries()
    {
        return $this->hasMany(
            NativeAccountTypeSummary::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     **/
    public function defaultAdvancedVarianceReportTemplate()
    {
        return $this->hasOne(
            ReportTemplate::class,
            'client_id',
            'id'
        )->where('is_default_advance_variance_report_template', true);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     **/
    public function bomaReportTemplate()
    {
        return $this->hasOne(
            ReportTemplate::class,
            'client_id',
            'id'
        )->where('is_boma_report_template', -true);
    }

    /**
     * @return mixed
     */
    public function suppress_pre_calc_events()
    {
        if (isset($this->getConfigJSON()->SUPPRESS_PRE_CALC_EVENTS))
        {
            return $this->getConfigJSON()->SUPPRESS_PRE_CALC_EVENTS;
        }
        return config('waypoint.suppress_pre_calc_events', true);
    }

    /**
     * @return mixed
     */
    public function suppress_pre_calc_usage()
    {
        if (isset($this->getConfigJSON()->SUPPRESS_PRE_CALC_USAGE))
        {
            return $this->getConfigJSON()->SUPPRESS_PRE_CALC_USAGE;
        }
        return config('waypoint.suppress_pre_calc_usage', true);
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function relatedUserTypesSlimForProperty()
    {
        return $this->hasMany(
            RelatedUserTypeSlim::class,
            'client_id',
            'id'
        )->where('related_object_type', '=', Property::class);
    }

    /**
     * @return Carbon
     */
    public function get_client_asof_date()
    {
        $client_id_old = $this->client_id_old;
        $minutes       = config('cache.cache_on', false)
            ? config('cache.cache_tags.Client.ttl', Model::CACHE_TAG_DEFAULT_TTL) / 60
            :
            0;
        $key           = "get_client_asof_date_Client_id_" . $this->id;
        $return_me     =
            Cache::tags([
                            'Client_' . $this->id,
                        ])
                 ->remember(
                     $key,
                     $minutes,
                     function () use ($client_id_old)
                     {
                         $DatabaseConnection = DB::connection('mysql_WAYPOINT_LEDGER_' . $client_id_old);
                         $result             = $DatabaseConnection
                             ->table('TARGET_ASOF_MONTH')
                             ->select(
                                 'TARGET_ASOF_MONTH.FROM_YEAR as FROM_YEAR',
                                 'TARGET_ASOF_MONTH.COVERED_YEAR as COVERED_YEAR',
                                 'TARGET_ASOF_MONTH.MOY as MOY',
                                 'TARGET_ASOF_MONTH.YEARMONTH as YEARMONTH',
                                 'TARGET_ASOF_MONTH.FROM_MONTH as FROM_MONTH'
                             )
                             ->first();
                         return Carbon::create($result->FROM_YEAR, $result->MOY, 1, 0, 0, 0)->modify('last day of this month');
                     }
                 );

        return $return_me;
    }
}
