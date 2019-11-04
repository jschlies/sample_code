<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\ClientCategory;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\NativeAccount;
use App\Waypoint\Models\TenantAttribute;
use App\Waypoint\Models\TenantIndustry;
use App\Waypoint\Models\User;
use ArrayObject;
use Cache;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Container\Container as Application;
use Prettus\Validator\Exceptions\ValidatorException;
use function array_filter;
use function array_unique;
use function json_encode;
use App\Waypoint\Models\NativeAccountType;
use App\Waypoint\Models\Ledger\Ledger;

/**
 * Class ClientRepository
 * @package App\Waypoint\Repositories
 */
class ClientRepository extends ClientRepositoryBase
{
    const CALENDAR_YEAR_ABBREV         = 'CY';
    const TRAILING_12_ABBREV           = '12';
    const TRAILING_12_ABBREV_FORMATTED = 'T12';
    const YEAR_TO_DATE_ABBREV          = 'YTD';
    const ACTUAL_OPTION_TEXT           = 'ACTUAL';
    const BUDGET_OPTION_TEXT           = 'BUDGET';
    const RENTABLE_OPTION_TEXT         = 'RENTABLE';
    const OCCUPIED_OPTION_TEXT         = 'OCCUPIED';
    const CALENDAR_YEAR_OPTION_TEXT    = 'CALENDAR YEAR';
    const YEAR_TO_DATE_OPTION_TEXT     = 'YEAR TO DATE';
    const TRAILING_12_OPTION_TEXT      = 'TRAILING 12';
    const PEER_YEAR_FILTER_OPTION_TEXT = '2015';

    /** @var NativeAccountTypeRepository */
    private $NativeAccountTypeRepositoryObj;
    /** @var NativeCoaRepository */
    private $NativeCoaRepositoryObj;
    /** @var ReportTemplateRepository */
    private $ReportTemplateRepositoryObj;
    /** @var PropertyRepository */
    private $PropertyRepositoryObj;
    /** @var PropertyGroupDetailRepository */
    private $PropertyGroupDetailRepositoryObj;
    /** @var TenantIndustryRepository */
    private $TenantIndustryRepositoryObj;
    /** @var TenantAttributeRepository */
    private $TenantAttributeRepositoryObj;

    private $client_config_arr = null;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->NativeAccountTypeRepositoryObj        = App::make(NativeAccountTypeRepository::class);
        $this->NativeCoaRepositoryObj                = App::make(NativeCoaRepository::class);
        $this->NativeAccountTypeTrailerRepositoryObj = App::make(NativeAccountTypeTrailerRepository::class);
        $this->ReportTemplateRepositoryObj           = App::make(ReportTemplateRepository::class);
        $this->PropertyRepositoryObj                 = App::make(PropertyRepository::class);
        $this->PropertyGroupDetailRepositoryObj      = App::make(PropertyGroupDetailRepository::class);
        $this->TenantIndustryRepositoryObj           = App::make(TenantIndustryRepository::class);
        $this->TenantAttributeRepositoryObj          = App::make(TenantAttributeRepository::class);
    }

    /**
     * Save a new Client in repository
     *
     * @param array $attributes
     * @return Client
     * @throws GeneralException
     * @throws \Exception
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function create(array $attributes)
    {
        $attributes['property_group_calc_status'] = Client::PROPERTY_GROUP_CALC_STATUS_IDLE;
        /**
         * because in MySQL, you can't default a blob
         */
        if ( ! isset($attributes['config_json']) || ! $attributes['config_json'])
        {
            $attributes['config_json'] = json_encode(new ArrayObject());
        }
        if ( ! isset($attributes['style_json']) || ! $attributes['style_json'])
        {
            $attributes['style_json'] = json_encode(new ArrayObject());
        }
        if ( ! isset($attributes['image_json']) || ! $attributes['image_json'])
        {
            $attributes['image_json'] = json_encode(new ArrayObject());
        }
        $ClientObj = parent::create($attributes);

        $this->initClient($ClientObj->id);

        if ( ! $this->suppress_events)
        {
            /**
             * run this here rather than in Job to avoid PreCalc* race condition
             */
            $CalculateVariousPropertyListsRepositoryObj = App::make(CalculateVariousPropertyListsRepository::class);
            $CalculateVariousPropertyListsRepositoryObj->CalculateVariousPropertyListsJobProcessor($ClientObj->id);
        }

        Cache::tags('Client_' . $ClientObj->id)->flush();
        return $ClientObj;
    }

    /**
     * NOTE NOTE NOTE NOTE
     *
     * this logic must be 'replicated' in the individiual model factories
     *
     * @param $attributes
     * @return mixed
     */
    public function applyAttributeDefaults($attributes)
    {
        $attributes['property_group_calc_status'] = Client::PROPERTY_GROUP_CALC_STATUS_IDLE;
        /**
         * because in MySQL, you can't default a blob
         */
        if ( ! isset($attributes['config_json']) || ! $attributes['config_json'])
        {
            $attributes['config_json'] = json_encode(new ArrayObject());
        }
        if ( ! isset($attributes['style_json']) || ! $attributes['style_json'])
        {
            $attributes['style_json'] = json_encode(new ArrayObject());
        }
        if ( ! isset($attributes['image_json']) || ! $attributes['image_json'])
        {
            $attributes['image_json'] = json_encode(new ArrayObject());
        }
        return $attributes;
    }

    /**
     * @param integer $client_id
     */
    public function initClientNativeAccountTypes($client_id)
    {
        $this->NativeAccountTypeRepositoryObj = App::make(NativeAccountTypeRepository::class);
        foreach (NativeAccount::$native_coa_type_arr as $native_account_type_name)
        {
            if ( ! $this->NativeAccountTypeRepositoryObj->findWhere(
                [
                    'client_id'                => $client_id,
                    'native_account_type_name' => $native_account_type_name,
                ]
            )->first())
            {
                $this->NativeAccountTypeRepositoryObj->create(
                    [
                        'client_id'                       => $client_id,
                        'native_account_type_name'        => $native_account_type_name,
                        'native_account_type_description' => $native_account_type_name,
                    ]
                );
            }
        }
    }

    /**
     * @param integer $client_id
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function initClientNativeAccountTypeTrailers($client_id)
    {
        $ClientObj = $this->find($client_id);
        if ($ClientObj->name == Client::DUMMY_CLIENT_NAME)
        {
            return;
        }

        foreach ($ClientObj->nativeAccountTypes as $NativeAccountTypeObj)
        {
            $advanced_variance_coefficient = 1;
            if (
                $NativeAccountTypeObj->native_account_type_name == NativeAccount::NATIVE_ACCOUNT_TYPE_REVENUE ||
                $NativeAccountTypeObj->native_account_type_name == NativeAccount::NATIVE_ACCOUNT_TYPE_ASSETS ||
                $NativeAccountTypeObj->native_account_type_name == NativeAccount::NATIVE_ACCOUNT_TYPE_EQUITY
            )
            {
                $advanced_variance_coefficient = -1;
            }

            foreach ($ClientObj->nativeCoas as $NativeCoaObj)
            {
                $this->NativeAccountTypeTrailerRepositoryObj->create(
                    [
                        'native_coa_id'                 => $NativeCoaObj->id,
                        'native_account_type_id'        => $NativeAccountTypeObj->id,
                        'property_id'                   => null,
                        'actual_coefficient'            => 1,
                        'budgeted_coefficient'          => 1,
                        'advanced_variance_coefficient' => $advanced_variance_coefficient,
                    ]
                );
            }
        }
    }

    /**
     * @param integer $client_id
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function initClientReportTemplates($client_id)
    {
        if ($client_id == 1)
        {
            return;
        }
        $this->ReportTemplateRepositoryObj->generateAccountTypeBasedReportTemplate($client_id);
        $this->ReportTemplateRepositoryObj->generateBomaBasedReportTemplate($client_id);
    }

    /**
     * @param integer $client_id
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function initTenantStuff($client_id)
    {
        if ($client_id == 1)
        {
            return;
        }
        foreach (TenantIndustry::$tenant_industry_default_name_arr as $tenant_industry_default_name)
        {

            $this->TenantIndustryRepositoryObj->create(
                [
                    'name'                     => $tenant_industry_default_name,
                    'description'              => 'Created via ClientRepository at ' . Carbon::now()->format('Y-m-d H:i:s'),
                    'tenant_industry_category' => TenantIndustry::TENANT_TYPE_CATEGORY_PRIMARY_INDUSTRTY,
                    'client_id'                => $client_id,
                ]
            );
        }
        foreach (TenantAttribute::$tenant_attribute_category_value_arr as $tenant_attribute_category_value)
        {
            if ($tenant_attribute_category_value == TenantAttribute::TENANT_ATTRIBUTE_CATEGORY_COLOR)
            {
                foreach (['red', 'white', 'blue'] as $tenant_attribute_value)
                {
                    $this->TenantAttributeRepositoryObj->create(
                        [
                            'name'                      => $tenant_attribute_value,
                            'description'               => $tenant_attribute_value,
                            'tenant_attribute_category' => $tenant_attribute_category_value,
                            'client_id'                 => $client_id,
                        ]
                    );
                }
            }
            if ($tenant_attribute_category_value == TenantAttribute::TENANT_ATTRIBUTE_CATEGORY_HEIGHT)
            {
                foreach (['tall', 'short', 'huge'] as $tenant_attribute_value)
                {
                    $this->TenantAttributeRepositoryObj->create(
                        [
                            'name'                      => $tenant_attribute_value,
                            'description'               => $tenant_attribute_value,
                            'tenant_attribute_category' => $tenant_attribute_category_value,
                            'client_id'                 => $client_id,
                        ]
                    );
                }
            }
            if ($tenant_attribute_category_value == TenantAttribute::TENANT_ATTRIBUTE_CATEGORY_SIZE)
            {
                foreach (['tall', 'short', 'huge'] as $tenant_attribute_value)
                {
                    $this->TenantAttributeRepositoryObj->create(
                        [
                            'name'                      => $tenant_attribute_value,
                            'description'               => $tenant_attribute_value,
                            'tenant_attribute_category' => $tenant_attribute_category_value,
                            'client_id'                 => $client_id,
                        ]
                    );
                }
            }
        }
    }

    /**
     * @param integer $client_id
     * @return $this|\Illuminate\Database\Eloquent\Model|mixed|void
     */
    public function initClientNativeCoa($client_id)
    {
        $ClientObj = $this->find($client_id);
        if ($ClientObj->name == Client::DUMMY_CLIENT_NAME)
        {
            return;
        }

        $NativeCoaObj = $this->NativeCoaRepositoryObj->create(
            [
                'client_id' => $ClientObj->id,
                'name'      => 'Native COA for ' . $ClientObj->name,
            ]
        );
        return $NativeCoaObj;
    }

    /**
     * @param integer $client_id
     * @throws GeneralException
     * @throws \Exception
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function initClient($client_id)
    {
        $ClientObj = $this->find($client_id);

        $ClientObj->updateConfig('FEATURE_OPPORTUNITIES', config('waypoint.default_client_opportunities', true));
        $ClientObj->updateConfig('NOTIFICATIONS', config('waypoint.default_client_notifications', false));
        $ClientObj->updateConfig(Client::DECIMAL_DISPLAY_FLAG, Client::DECIMAL_DISPLAY_DEFUALT_VALUE);
        $ClientObj->updateConfig(Client::NEGATIVE_VALUE_SYMBOLS_FLAG, Client::NEGATIVE_VALUE_SYMBOLS_DEFAULT_VALUE);

        $ClientCategoryRepositoryObj = App::make(ClientCategoryRepository::class);
        foreach (ClientCategory::$default_client_categories_arr as $default_client_category)
        {
            $ClientCategoryRepositoryObj->create(
                [
                    'client_id'   => $ClientObj->id,
                    'name'        => $default_client_category,
                    'description' => $default_client_category,
                ]
            );
        }

        $this->initClientNativeAccountTypes($ClientObj->id);
        $this->initClientNativeCoa($ClientObj->id);
        $this->initClientNativeAccountTypeTrailers($ClientObj->id);
        $this->initNativeAccountDropdownDefaults($ClientObj);
        $this->initClientReportTemplates($ClientObj->id);
        $this->initTenantStuff($ClientObj->id);
    }

    /**
     * @return array
     */
    public function getClientConfigDefaultValues()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getClientStyleDefaultValues()
    {
        return [
            'defaultCodes' => [
                '40 000'     => '#abdda4',
                '41 000'     => '#e6f598',
                '42 000'     => '#fce08b',
                '43 000'     => '#3588bd',
                '44 000'     => '#66c2a5',
                '45 000'     => '#f3ad60',
                '46 000'     => '#f3af60',
                '47 000'     => '#ec6c41',
                'properties' => '#32a3dc',
                'groups'     => '#2f2f2e',

                'rankCodes' => [
                    ['#4444'],
                    ['#4444', '#ffffbf'],
                    ['#4444', '#fc8d59', '#91cf60'],
                    ['#4444', '#f3ad60', '#fce08b', '#abdda4'],
                    ['#4444', '#d7191c', '#fdae61', '#a6d96a', '#1a9641'],
                    ['#4444', '#d7191c', '#fdae61', '#ffffbf', '#a6d96a', '#1a9641'],
                    ['#4444', '#d73027', '#fc8d59', '#fee08b', '#d9ef8b', '#91cf60', '#1a9850'],
                    ['#4444', '#d73027', '#fc8d59', '#fee08b', '#ffffbf', '#d9ef8b', '#91cf60', '#1a9850'],
                    ['#4444', '#d73027', '#f46d43', '#fdae61', '#fee08b', '#d9ef8b', '#a6d96a', '#66bd63', '#1a9850'],
                    ['#4444', '#d73027', '#f46d43', '#fdae61', '#fee08b', '#ffffbf', '#d9ef8b', '#a6d96a', '#66bd63', '#1a9850'],
                    ['#4444', '#a50026', '#d73027', '#f46d43', '#fdae61', '#fee08b', '#d9ef8b', '#a6d96a', '#66bd63', '#1a9850', '#006837'],
                    ['#4444', '#a50026', '#d73027', '#f46d43', '#fdae61', '#fee08b', '#ffffbf', '#d9ef8b', '#a6d96a', '#66bd63', '#1a9850', '#006837'],
                ],
            ],
        ];
    }

    /**
     * @param Client $ClientObj
     * @param bool $waypoint_benchmark_enabled
     * @return bool
     */
    private function peerTablesExist(Client $ClientObj, $waypoint_benchmark_enabled = false): bool
    {
        $peer_tables_needed = $waypoint_benchmark_enabled ? ['PEER_GROUP_INFO_CLIENT_' . $ClientObj->client_id_old] : [];
        return DatabaseConnectionRepository::tablesExist($ClientObj, 'waypoint_peer_' . $ClientObj->client_id_old, $peer_tables_needed);
    }

    /**
     * @param Client $ClientObj
     * @return bool
     */
    private function ledgerTablesExist(Client $ClientObj): bool
    {
        $ledger_tables_needed = ['CLIENT_BENCHMARKS', 'YEAR_MONTH_PERIOD', 'TARGET_ASOF_MONTH'];
        return DatabaseConnectionRepository::tablesExist($ClientObj, 'waypoint_ledger_' . $ClientObj->client_id_old, $ledger_tables_needed);
    }

    /**
     * @param array $client_config_arr
     * @return bool
     */
    private function clientConfigDropdownDefaultsExist(): bool
    {
        if ( ! $this->client_config_arr)
        {
            throw new GeneralException('please check that you have set the config array correctly');
        }
        return isset($this->client_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]);
    }

    /**
     * @return bool
     */
    private function varianceNativeAccountTypesExists()
    {
        return isset($this->client_config_arr[NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY][AdvancedVariance::ADVANCED_VARIANCE_CONFIG_KEY]);
    }

    private function getVarianceNativeAccountTypesFromClientConfig()
    {
        if ( ! $this->client_config_arr)
        {
            throw new GeneralException('please check that you have set the config array correctly');
        }
        if (isset($this->client_config_arr[NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY][AdvancedVariance::ADVANCED_VARIANCE_CONFIG_KEY]))
        {
            return $this->client_config_arr[NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY][AdvancedVariance::ADVANCED_VARIANCE_CONFIG_KEY];
        }
        return null;
    }

    /**
     * @param Client $ClientObj
     */
    public function initNativeAccountDropdownDefaults(Client $ClientObj)
    {
        $clientObjectShouldBeUpdated = false;

        // get client config
        $this->client_config_arr = $ClientObj->getConfigJSON(true);

        // setup analytics fallback defaults
        if ( ! $this->clientConfigDropdownDefaultsExist())
        {
            if ($this->varianceNativeAccountTypesExists())
            {
                $this->client_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['activeAccountTab']
                    = current($this->getVarianceNativeAccountTypesFromClientConfig())->native_account_type_name;

                foreach ($this->getVarianceNativeAccountTypesFromClientConfig() as $native_account_type_arr)
                {
                    $this->client_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['accountTypeFilters'][$native_account_type_arr->native_account_type_name]
                        = $native_account_type_arr->report_template_account_group_id;
                }

                $this->client_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['area']     = 'RENTABLE';
                $this->client_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['report']   = 'ACTUAL';
                $this->client_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['code']     = ['Operating Expenses' => '40_000_h2'];
                $this->client_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['period']   = 'T12';
                $this->client_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['year']     = '2017';
                $this->client_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['peerYear'] = '2015';

                $clientObjectShouldBeUpdated = true;
            }

            if ($clientObjectShouldBeUpdated)
            {
                $ClientObj->config_json = json_encode($this->client_config_arr);
                $ClientObj->save();
            }
        }
    }

    /**
     * @param Client $ClientObj
     * @return array
     * @throws GeneralException
     */
    public function getWaypointLedgerDropDownValues(Client $ClientObj)
    {
        try
        {
            $ClientConfigJSON_arr       = $ClientObj->getConfigJSON(true);
            $waypoint_benchmark_enabled = ! isset($ClientConfigJSON_arr['DISABLE_BENCHMARK']) || (isset($ClientConfigJSON_arr['DISABLE_BENCHMARK']) && ! (bool) $ClientConfigJSON_arr['DISABLE_BENCHMARK']);
            $peer_data_is_present       = $this->peerTablesExist($ClientObj, $waypoint_benchmark_enabled);
            $ledger_data_is_present     = $this->ledgerTablesExist($ClientObj);
            $config_arr                 = [
                Client::FILTERS_CONFIG_KEY  => [],
                Client::DEFAULTS_CONFIG_KEY => [],
            ];

            $client_config_dropdowns_are_present = isset($ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS])
                                                   && ! empty($ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS])
                                                   && isset($ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::FILTERS_CONFIG_KEY])
                                                   && ! empty($ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::FILTERS_CONFIG_KEY])
                                                   && isset($ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY])
                                                   && ! empty($ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]);

            /*********** AREA ****/
            // if area is present in client object, then populate with that value
            if (
                $client_config_dropdowns_are_present
                && isset($ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::FILTERS_CONFIG_KEY]['area'])
                && ! empty($ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::FILTERS_CONFIG_KEY]['area'])
            )
            {
                $config_arr[Client::FILTERS_CONFIG_KEY]['area'] = $ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::FILTERS_CONFIG_KEY]['area'];
            }
            // else choose reasonable filter dropdown options
            else
            {
                $config_arr[Client::FILTERS_CONFIG_KEY]['area'] = [self::RENTABLE_OPTION_TEXT, self::OCCUPIED_OPTION_TEXT];
            }

            // if the default dropdown value is present in the client object then use that
            if (
                $client_config_dropdowns_are_present
                && isset($ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['area'])
                && ! empty($ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['area'])
            )
            {
                $config_arr[Client::DEFAULTS_CONFIG_KEY]['area'] = $ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['area'];
            }
            // else choose reasonable filter dropdown default value
            else
            {
                $config_arr[Client::DEFAULTS_CONFIG_KEY]['area'] = self::RENTABLE_OPTION_TEXT;
            }

            /*********** BENCHMARK TYPE (REPORT) ****/
            $config_arr[Client::FILTERS_CONFIG_KEY]['report']  = [];
            $config_arr[Client::DEFAULTS_CONFIG_KEY]['report'] = '';

            if ($ledger_data_is_present)
            {
                $list_of_report_types_results = DatabaseConnectionRepository::getLedgerDatabaseConnection($ClientObj)
                                                                            ->table('CLIENT_BENCHMARKS')
                                                                            ->select('BENCHMARK_TYPE')
                                                                            ->distinct()
                                                                            ->get();

                // update benchmark type (report) list based on data
                foreach ($list_of_report_types_results as $result)
                {
                    if (
                        strpos($result->BENCHMARK_TYPE, self::ACTUAL_OPTION_TEXT) !== false
                        && ! in_array(self::ACTUAL_OPTION_TEXT, $config_arr[Client::FILTERS_CONFIG_KEY]['report'])
                    )
                    {
                        $config_arr[Client::FILTERS_CONFIG_KEY]['report'][] = self::ACTUAL_OPTION_TEXT;
                    }
                    elseif (
                        strpos($result->BENCHMARK_TYPE, self::BUDGET_OPTION_TEXT) !== false
                        && ! in_array(self::BUDGET_OPTION_TEXT, $config_arr[Client::FILTERS_CONFIG_KEY]['report'])
                    )
                    {
                        $config_arr[Client::FILTERS_CONFIG_KEY]['report'][] = self::BUDGET_OPTION_TEXT;
                    }
                }

                // if database turned up an empty set for filters based on the data, use the existing filter list from the client object if it's present
                if (
                    empty($config_arr[Client::FILTERS_CONFIG_KEY]['report'])
                    && isset($ClientConfigJSON_arr[Client::FILTERS_CONFIG_KEY]['report'])
                    && ! empty($ClientConfigJSON_arr[Client::FILTERS_CONFIG_KEY]['report'])
                )
                {
                    $config_arr[Client::FILTERS_CONFIG_KEY]['report'] = $ClientConfigJSON_arr[Client::FILTERS_CONFIG_KEY]['report'];
                }
                // else provide reasonable filter defaults
                else
                {
                    $config_arr[Client::FILTERS_CONFIG_KEY]['report'] = [self::ACTUAL_OPTION_TEXT, self::BUDGET_OPTION_TEXT];
                }

                // update bechmark type (report) default value based on previous filter list
                if (in_array(self::ACTUAL_OPTION_TEXT, $config_arr[Client::FILTERS_CONFIG_KEY]['report']))
                {
                    $config_arr[Client::DEFAULTS_CONFIG_KEY]['report'] = self::ACTUAL_OPTION_TEXT;
                }
                elseif (in_array(self::BUDGET_OPTION_TEXT, $config_arr[Client::FILTERS_CONFIG_KEY]['report']))
                {
                    $config_arr[Client::DEFAULTS_CONFIG_KEY]['report'] = self::BUDGET_OPTION_TEXT;
                }
            }
            else
            {
                // if filter options are present in client object then use those
                if (
                    $client_config_dropdowns_are_present
                    && isset($ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::FILTERS_CONFIG_KEY]['report'])
                    && ! empty($ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::FILTERS_CONFIG_KEY]['report'])
                )
                {
                    $config_arr[Client::FILTERS_CONFIG_KEY]['report'] = $ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::FILTERS_CONFIG_KEY]['report'];
                }
                // else choose reasonable defaults
                else
                {
                    $config_arr[Client::FILTERS_CONFIG_KEY]['report'] = [self::ACTUAL_OPTION_TEXT, self::BUDGET_OPTION_TEXT];
                }

                // if default filter option is present in client object then use it
                if (
                    $client_config_dropdowns_are_present
                    && isset($ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['report'])
                    && ! empty($ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['report'])
                )
                {
                    $config_arr[Client::DEFAULTS_CONFIG_KEY]['report'] = $ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['report'];
                }
                // else choose reasonable default value
                else
                {
                    $config_arr[Client::DEFAULTS_CONFIG_KEY]['report'] = self::ACTUAL_OPTION_TEXT;
                }
            }

            /*********** ACCOUNT_CODE ****/
            $config_arr[Client::DEFAULTS_CONFIG_KEY]['code'] = ['Operating Expenses' => '40_000_h2'];

            /*********** ANALYTICS DEFAULT ACTIVE TAB ****/
            if (isset($ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['activeAccountTab']))
            {
                $config_arr[Client::DEFAULTS_CONFIG_KEY]['activeAccountTab'] = $ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['activeAccountTab'];
            }

            /*********** ANALYTICS DEFAULT ACCOUNT TYPE FILTERS ****/
            if (isset($ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['accountTypeFilters']))
            {
                $config_arr[Client::DEFAULTS_CONFIG_KEY]['accountTypeFilters'] = $ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['accountTypeFilters'];
            }

            /*********** PERIOD / YEARS ****/
            if ($ledger_data_is_present)
            {
                $config_arr[Client::FILTERS_CONFIG_KEY]['period']  = [];
                $config_arr[Client::DEFAULTS_CONFIG_KEY]['period'] = '';

                $results = DatabaseConnectionRepository::getLedgerDatabaseConnection($ClientObj)
                                                       ->table('YEAR_MONTH_PERIOD')
                                                       ->select(['CY', 'YTD', 'TRAILING_12'])
                                                       ->get();

                $asOfYearResult = DatabaseConnectionRepository::getLedgerDatabaseConnection($ClientObj)
                                                              ->table('TARGET_ASOF_MONTH')
                                                              ->select('FROM_YEAR')
                                                              ->first();

                $yearListCY                                        = $results->pluck('CY')->unique()->toArray();
                $yearListYTD                                       = $results->pluck('YTD')->unique()->toArray();
                $yearListT12                                       = $results->pluck('TRAILING_12')->unique()->toArray();
                $yearListAllPeriods                                = array_filter(
                    array_unique(array_collapse([$yearListCY, $yearListYTD, $yearListT12])),
                    'strlen'
                );
                $mostRecentYearCY                                  = $results->pluck('CY')->max();
                $mostRecentYearYTD                                 = $results->pluck('YTD')->max();
                $mostRecentYearT12                                 = $results->pluck('TRAILING_12')->max();
                $config_arr[Client::FILTERS_CONFIG_KEY]['period']  = $this->getPeriodList($mostRecentYearCY, $mostRecentYearYTD, $mostRecentYearT12);
                $config_arr[Client::DEFAULTS_CONFIG_KEY]['period'] = $this->getDefaultPeriod($mostRecentYearCY, $mostRecentYearYTD, $mostRecentYearT12);
                $config_arr[Client::FILTERS_CONFIG_KEY]['year']    = collect($yearListAllPeriods)->sort()->reverse()->values()->toArray();
                $config_arr[Client::DEFAULTS_CONFIG_KEY]['year']   = (string) $asOfYearResult->FROM_YEAR;
            }
            else
            {
                // if period filter dropdown options are present in client object then use them
                if (
                    $client_config_dropdowns_are_present
                    && isset($ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::FILTERS_CONFIG_KEY]['period'])
                    && ! empty($ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::FILTERS_CONFIG_KEY]['period'])
                )
                {
                    $config_arr[Client::FILTERS_CONFIG_KEY]['period'] = $ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::FILTERS_CONFIG_KEY]['period'];
                }
                // else choose reasonable defaults
                else
                {
                    $config_arr[Client::FILTERS_CONFIG_KEY]['period'] = [
                        self::CALENDAR_YEAR_ABBREV,
                        self::TRAILING_12_ABBREV_FORMATTED,
                        self::YEAR_TO_DATE_ABBREV,
                    ];
                }

                // if period filter dropdown default is present in client object then use it
                if (
                    $client_config_dropdowns_are_present
                    && isset($ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['period'])
                    && ! empty($ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['period'])
                )
                {
                    $config_arr[Client::DEFAULTS_CONFIG_KEY]['period'] = $ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['period'];
                }
                // else choose reasonable defaults
                else
                {
                    $config_arr[Client::DEFAULTS_CONFIG_KEY]['period'] = self::TRAILING_12_ABBREV_FORMATTED;
                }

                // if year filter dropdown options are present in client object then use them
                if (
                    $client_config_dropdowns_are_present
                    && isset($ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::FILTERS_CONFIG_KEY]['year'])
                    && ! empty($ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::FILTERS_CONFIG_KEY]['year'])
                )
                {
                    $config_arr[Client::FILTERS_CONFIG_KEY]['year'] = $ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::FILTERS_CONFIG_KEY]['year'];
                }
                // else choose reasonable defaults: current year and previous year
                else
                {
                    $config_arr[Client::FILTERS_CONFIG_KEY]['year'] = [
                        date('Y', time()),
                        date('Y', strtotime('-1 year')),
                    ];
                }

                // if year filter dropdown default is present in client object then use it
                if (
                    $client_config_dropdowns_are_present
                    && isset($ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['year'])
                    && ! empty($ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['year'])
                )
                {
                    $config_arr[Client::DEFAULTS_CONFIG_KEY]['year'] = $ClientConfigJSON_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['year'];
                }
                // else choose reasonable defaults: latest year from list of dropdown filter options
                else
                {
                    $config_arr[Client::DEFAULTS_CONFIG_KEY]['year'] = max($config_arr[Client::FILTERS_CONFIG_KEY]['year']);
                }
            }

            // peer year dropdown filters & peer year dropdown filter default
            if ($waypoint_benchmark_enabled && $peer_data_is_present)
            {
                $config_arr[Client::FILTERS_CONFIG_KEY]['peerYear']  = $peer_years_array = $this->getPeerYears($ClientObj);
                $config_arr[Client::DEFAULTS_CONFIG_KEY]['peerYear'] = ! empty($peer_years_array) ? max($peer_years_array) : '';
            }
            else
            {
                // if peer year dropdown filter options are present in the client object then use them
                if (
                    $client_config_dropdowns_are_present
                    && isset($config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::FILTERS_CONFIG_KEY]['peerYear'])
                    && ! empty($config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::FILTERS_CONFIG_KEY]['peerYear'])
                )
                {
                    $config_arr[Client::FILTERS_CONFIG_KEY]['peerYear'] = $config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::FILTERS_CONFIG_KEY]['peerYear'];
                }
                // else choose reasonable default
                else
                {
                    $config_arr[Client::FILTERS_CONFIG_KEY]['peerYear'] = [self::PEER_YEAR_FILTER_OPTION_TEXT];
                }

                // if peer year dropdown filter default is present in the client object then use it
                if (
                    $client_config_dropdowns_are_present
                    && isset($config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['peerYear'])
                    && ! empty($config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['peerYear'])
                )
                {
                    $config_arr[Client::DEFAULTS_CONFIG_KEY]['peerYear'] = $config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['peerYear'];
                }
                // else choose reasonable default
                else
                {
                    $config_arr[Client::DEFAULTS_CONFIG_KEY]['peerYear'] = self::PEER_YEAR_FILTER_OPTION_TEXT;
                }
            }

            $ClientObj->setConfigJSON($config_arr);
        }
        catch (Exception $e)
        {
            DB::rollBack();
            throw new GeneralException($e->getMessage(), 403, $e);
        }

        return $config_arr;
    }

    /**
     * @param Client $ClientObj
     * @return array
     */
    public function getPeerYears(Client $ClientObj)
    {
        if ( ! $DatabaseConnectionObj = DatabaseConnectionRepository::getPeerDatabaseConnection($ClientObj, 'PEER_GROUP_INFO_CLIENT_' . $ClientObj->client_id_old))
        {
            return [];
        }

        $peer_calc_available_years = $DatabaseConnectionObj
            ->table('PEER_GROUP_INFO_CLIENT_' . $ClientObj->client_id_old)
            ->select('FROM_YEAR')
            ->groupBy('FROM_YEAR')
            ->orderBy('FROM_YEAR', 'desc')
            ->get('FROM_YEAR')
            ->pluck('FROM_YEAR')
            ->toArray();

        if (is_array($peer_calc_available_years))
        {
            return array_map('strval', $peer_calc_available_years);
        }
        elseif ( ! empty($peer_calc_available_years))
        {
            return [(string) $peer_calc_available_years];
        }

        return [];
    }

    /**
     * Delete a entity in repository by id
     *
     * Overriding normal call to parent::delete() to try to take advantage of eager loading
     *
     * @param integer $client_id
     * @return bool
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function delete($client_id)
    {
        /**
         * NO MATTER WHAT, we want to keep notification logs. Tie them to SuperUser
         * so they survive cascard delete
         */
        $NotificationLogRepositoryObj = App::make(NotificationLogRepository::class);
        /** @var Client $ClientObj */
        $ClientObj = $this->find($client_id);
        /** @var User $UserObj */
        foreach ($ClientObj->users as $UserObj)
        {
            foreach ($UserObj->notificationLogs as $NotificationLogObj)
            {
                $NotificationLogRepositoryObj
                    ->update(
                        [
                            'user_id' => 1,
                        ],
                        $NotificationLogObj->id
                    );
            }
        }

        /**
         * for constraint reasons, we have to deal with properties before asset_types, sigh, must be a better way
         */
        $this->setSuppressEvents(true);
        $this->PropertyRepositoryObj->setSuppressEvents(true);

        /**
         * we need to delete properties because of their asset_type_id constraint
         */
        foreach ($ClientObj->properties as $PropertyObj)
        {
            $this->PropertyRepositoryObj->update(
                [
                    'asset_type_id' => null,
                ],
                $PropertyObj->id
            );
        }

        $this->applyScope();

        $temporarySkipPresenter = $this->skipPresenter;
        $this->skipPresenter(true);

        $this->skipPresenter($temporarySkipPresenter);
        $this->resetModel();

        $deleted = parent::delete($client_id);

        $this->setSuppressEvents(false);
        $this->PropertyRepositoryObj->setSuppressEvents(false);

        $model = null;
        unset($model);
        $originalModel = null;
        unset($originalModel);

        Cache::tags('Client_' . $ClientObj->id)->flush();

        return $deleted;
    }

    /**
     * @param array $user_detail_arr
     * @param $candidate_role
     * @return bool
     */
    public function user_has_role($user_detail_arr, $candidate_role)
    {
        foreach ($user_detail_arr['roles'] as $role_name)
        {
            if ($role_name == $candidate_role)
            {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $mostRecentYearForCY
     * @param $mostRecentYearForYTD
     * @param $mostRecentYearForT12
     * @return array
     */
    private function getPeriodList($mostRecentYearForCY, $mostRecentYearForYTD, $mostRecentYearForT12)
    {
        $periodList = [];
        if ( ! empty($mostRecentYearForCY))
        {
            $periodList[] = self::CALENDAR_YEAR_ABBREV;
        }
        if ( ! empty($mostRecentYearForT12))
        {
            $periodList[] = self::TRAILING_12_ABBREV_FORMATTED;
        }
        if ( ! empty($mostRecentYearForYTD))
        {
            $periodList[] = self::YEAR_TO_DATE_ABBREV;
        }
        return $periodList;
    }

    /**
     * @param $mostRecentYearForCY
     * @param $mostRecentYearForYTD
     * @param $mostRecentYearForT12
     * @return string
     */
    private function getDefaultPeriod($mostRecentYearForCY, $mostRecentYearForYTD, $mostRecentYearForT12)
    {
        $defaultPeriod             = null;
        $mostRecentYearsCollection = collect([$mostRecentYearForCY, $mostRecentYearForYTD, $mostRecentYearForT12]);
        $mostRecentYear            = $mostRecentYearsCollection->max();
        if ($mostRecentYearForT12 == $mostRecentYear)
        {
            $defaultPeriod = self::TRAILING_12_ABBREV_FORMATTED;
        }
        elseif ($mostRecentYearForYTD == $mostRecentYear)
        {
            $defaultPeriod = self::YEAR_TO_DATE_ABBREV;
        }
        elseif ($mostRecentYearForCY == $mostRecentYear)
        {
            $defaultPeriod = self::CALENDAR_YEAR_ABBREV;
        }
        return $defaultPeriod;
    }

    /**
     * @param integer $client_id
     * @return App\Waypoint\Collection|mixed
     */
    public function getPropertyGroupObjArrForClient($client_id)
    {
        $result                    = DB::select(
            DB::raw(
                "
                    SELECT property_groups.id AS id FROM property_groups, users
                        WHERE
                            property_groups.user_id = users.id AND
                            users.client_id = :CLIENT_ID 
                "
            ),
            [
                'CLIENT_ID' => $client_id,
            ]
        );
        $property_group_id_arr     = array_unique(
            array_map(
                function ($value)
                {
                    return $value->id;
                },
                $result
            )
        );
        $PropertyGroupDetailObjArr = $this->PropertyGroupDetailRepositoryObj->findWhereIn('id', $property_group_id_arr);

        return $PropertyGroupDetailObjArr;
    }

    /**
     * @param integer $client_id
     * @return mixed
     */
    public function getStandardAttributeUniqueValues($client_id)
    {
        return $this->find($client_id)->getCustomAttributeUniqueValues();
    }

    /**
     * @param integer $client_id
     * @return mixed
     */
    public function getCustomAttributeUniqueValues($client_id)
    {
        return $this->find($client_id)->getStandardAttributeUniqueValues();
    }

    /**
     * @param integer $client_id
     * @return App\Waypoint\Collection|mixed
     * @return App\Waypoint\Collection|mixed
     */
    public function getOpportunityObjArrForClient($client_id)
    {
        $result             = DB::select(
            DB::raw(
                "
                    SELECT opportunities.id AS id FROM opportunities, properties
                        WHERE
                            properties.client_id = :client_id AND
                            properties.id = opportunities.property_id 
                "
            ),
            [
                'client_id' => $client_id,
            ]
        );
        $opportunity_id_arr = array_unique(
            array_map(
                function ($value)
                {
                    return $value->id;
                },
                $result
            )
        );

        return App::make(OpportunityRepository::class)->findWhereIn('id', $opportunity_id_arr);
    }

    /**
     * @param integer $client_id
     * @param bool $wipe_properties
     * @throws GeneralException
     * @throws \Exception
     */
    public function initAdvancedVariance($client_id, $wipe_properties = false)
    {
        /** @var Client $ClientObj */
        $ClientObj = $this->find($client_id);
        if ($ClientObj->users->count() == 0)
        {
            throw new GeneralException(__LINE__ . ' client no users' . __LINE__, 500);
        }
        $RelatedUserTypeRepositoryObj = App::make(RelatedUserTypeRepository::class);

        $reviewer_email_arr       = array_filter(
            $ClientObj->users
                ->getArrayOfGivenFieldValues('email'),
            'strlen'
        );
        $default_name_value_pairs =
            [
                ['name' => 'ADVANCED_VARIANCE', 'value' => true],
                ['name' => 'ADVANCED_VARIANCE_REVIEWERS', 'value' => $reviewer_email_arr],
                ['name' => 'ADVANCED_VARIANCE_FREQ', 'value' => AdvancedVariance::PERIOD_TYPE_MONTHLY],
                ['name' => 'ADVANCED_VARIANCE_TRIGGER', 'value' => AdvancedVariance::TRIGGER_MODE_MONTHLY],
                ['name' => 'ADVANCED_VARIANCE_CLOSE_DATE_DAYS', 'value' => 1],

                ['name' => 'ADVANCED_VARIANCE_NATIVE_ACCOUNT_OVERAGE_THRESHOLD_AMOUNT', 'value' => 1000],
                ['name' => 'ADVANCED_VARIANCE_NATIVE_ACCOUNT_OVERAGE_THRESHOLD_AMOUNT_TOO_GOOD', 'value' => 0],
                ['name' => 'ADVANCED_VARIANCE_NATIVE_ACCOUNT_OVERAGE_THRESHOLD_PERCENT', 'value' => 10],
                ['name' => 'ADVANCED_VARIANCE_NATIVE_ACCOUNT_OVERAGE_THRESHOLD_PERCENT_TOO_GOOD', 'value' => 0],
                ['name' => 'ADVANCED_VARIANCE_NATIVE_ACCOUNT_OVERAGE_THRESHOLD_OPERATOR', 'value' => AdvancedVariance::OVERAGE_THRESHOLD_OPERATOR_AND],

                ['name' => 'ADVANCED_VARIANCE_REPORT_TEMPLATE_ACCOUNT_GROUP_OVERAGE_THRESHOLD_AMOUNT', 'value' => 1000],
                ['name' => 'ADVANCED_VARIANCE_REPORT_TEMPLATE_ACCOUNT_GROUP_OVERAGE_THRESHOLD_AMOUNT_TOO_GOOD', 'value' => 0],
                ['name' => 'ADVANCED_VARIANCE_REPORT_TEMPLATE_ACCOUNT_GROUP_OVERAGE_THRESHOLD_PERCENT', 'value' => 10],
                ['name' => 'ADVANCED_VARIANCE_REPORT_TEMPLATE_ACCOUNT_GROUP_OVERAGE_THRESHOLD_PERCENT_TOO_GOOD', 'value' => 0],
                ['name' => 'ADVANCED_VARIANCE_REPORT_TEMPLATE_ACCOUNT_GROUP_OVERAGE_THRESHOLD_OPERATOR', 'value' => AdvancedVariance::OVERAGE_THRESHOLD_OPERATOR_AND],

                ['name' => 'ADVANCED_VARIANCE_CALCULATED_FIELD_OVERAGE_THRESHOLD_AMOUNT', 'value' => 1000],
                ['name' => 'ADVANCED_VARIANCE_CALCULATED_FIELD_OVERAGE_THRESHOLD_AMOUNT_TOO_GOOD', 'value' => 0],
                ['name' => 'ADVANCED_VARIANCE_CALCULATED_FIELD_OVERAGE_THRESHOLD_PERCENT', 'value' => 10],
                ['name' => 'ADVANCED_VARIANCE_CALCULATED_FIELD_OVERAGE_THRESHOLD_PERCENT_TOO_GOOD', 'value' => 0],
                ['name' => 'ADVANCED_VARIANCE_CALCULATED_FIELD_OVERAGE_THRESHOLD_OPERATOR', 'value' => AdvancedVariance::OVERAGE_THRESHOLD_OPERATOR_AND],

                ['name' => 'ADVANCED_VARIANCE_COMPLETION_DATE_DAYS', 'value' => 30],
                ['name' => 'ADVANCED_VARIANCE_THRESHOLD_MODE', 'value' => AdvancedVariance::THRESHOLD_MODE_BOTH],
            ];

        foreach ($default_name_value_pairs as $default_name_value_pair)
        {
            $name  = $default_name_value_pair['name'];
            $value = $default_name_value_pair['value'];
            $ClientObj->updateConfig($name, $value);
        }

        if ( ! $RelatedUserTypeRepositoryObj->findWhere(
            [
                'client_id'              => $client_id,
                'related_object_type'    => AdvancedVariance::class,
                'related_object_subtype' => AdvancedVariance::REVIEWER,
            ]
        )->first())
        {
            $RelatedUserTypeRepositoryObj->create(
                [
                    'name'                   => AdvancedVariance::REVIEWER,
                    'client_id'              => $client_id,
                    'related_object_type'    => AdvancedVariance::class,
                    'related_object_subtype' => AdvancedVariance::REVIEWER,
                ]
            );
        }

        $ClientObj->refresh();
        $client_config_arr = $ClientObj->getConfigJSON(true);
        $ClientObj->updateConfig('ADVANCED_VARIANCE_REVIEWERS', $ClientObj->users->getArrayOfGivenFieldValues('email'));

        if (
            ! isset($client_config_arr['ADVANCED_VARIANCE_REVIEWERS']) ||
            ! is_array($client_config_arr['ADVANCED_VARIANCE_REVIEWERS']) ||

            ! isset($client_config_arr['ADVANCED_VARIANCE_THRESHOLD_MODE'])
        )
        {
            throw new GeneralException(__LINE__ . ' client not properly configured for advanced variance - client ' . $ClientObj->name . print_r($client_config_arr, true));
        }

        $AdvancedVarianceThresholdRepositoryObj = App::make(AdvancedVarianceThresholdRepository::class);
        $AdvancedVarianceThresholdRepositoryObj->create(
            [
                'client_id'                                         => $ClientObj->id,
                'native_account_overage_threshold_amount'           => 1000,
                'native_account_overage_threshold_amount_too_good'  => 1000,
                'native_account_overage_threshold_percent'          => 10,
                'native_account_overage_threshold_percent_too_good' => 10,
                'native_account_overage_threshold_operator'         => 'and',

                'report_template_account_group_overage_threshold_amount'           => 100,
                'report_template_account_group_overage_threshold_amount_too_good'  => 100,
                'report_template_account_group_overage_threshold_percent'          => 10,
                'report_template_account_group_overage_threshold_percent_too_good' => 10,
                'report_template_account_group_overage_threshold_operator'         => 'and',

                'calculated_field_overage_threshold_amount'           => 100,
                'calculated_field_overage_threshold_amount_too_good'  => 100,
                'calculated_field_overage_threshold_percent'          => 10,
                'calculated_field_overage_threshold_percent_too_good' => 10,
                'calculated_field_overage_threshold_operator'         => 'and',
            ]
        );
    }

    /**
     * @param Client $ClientObj
     */
    public function initNativeAccountTypesConfig(Client $ClientObj)
    {
        $ClientConfigArr = json_decode($ClientObj->config_json, true);

        /** @var NativeAccountTypeSummaryRepository $NativeAccounTypeSummaryRepositoryObj */
        $NativeAccounTypeSummaryRepositoryObj = App::make(NativeAccountTypeSummaryRepository::class);

        $DefaultAdvancedVarianceReportTemplate
            = App\Waypoint\Models\ReportTemplate::where(
            [
                ['client_id', $ClientObj->id],
                ['is_default_advance_variance_report_template', 1],
            ]
        )->first();

        $DefaultAnalyticsReportTemplate
            = App\Waypoint\Models\ReportTemplate::where(
            [
                ['client_id', $ClientObj->id],
                ['is_default_analytics_report_template', 1],
            ]
        )->first();

        // setup native account types for tabs across analytics and advanced variance
        if ( ! isset($ClientConfigArr[NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY]))
        {
            $ClientConfigArr[NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY][AdvancedVariance::ADVANCED_VARIANCE_CONFIG_KEY] =
                $NativeAccounTypeSummaryRepositoryObj
                    ->getForReportTemplate($DefaultAdvancedVarianceReportTemplate->id);

            $ClientConfigArr[NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY][Ledger::ANALYTICS_CONFIG_KEY] =
                $NativeAccounTypeSummaryRepositoryObj
                    ->getForReportTemplate($DefaultAnalyticsReportTemplate->id);
        }

        $ClientObj->config_json = json_encode($ClientConfigArr);
        $ClientObj->save();
    }

    /**
     * @param integer $client_id
     * @return App\Waypoint\Collection|mixed
     */
    public function getAdvancedVarianceObjArrForClient($client_id)
    {
        $result                   = DB::select(
            DB::raw(
                "
                    SELECT advanced_variances.id AS id FROM advanced_variances, properties
                        WHERE
                            properties.client_id = :client_id AND
                            properties.id = advanced_variances.property_id 
                "
            ),
            [
                'client_id' => $client_id,
            ]
        );
        $advanced_variance_id_arr = array_unique(
            array_map(
                function ($value)
                {
                    return $value->id;
                },
                $result
            )
        );

        return App::make(AdvancedVarianceRepository::class)->findWhereIn('id', $advanced_variance_id_arr);
    }

    /**
     * Update a Client entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return Client
     * @throws ValidatorException
     */
    public function update(array $attributes, $client_id)
    {
        $ClientObj = parent::update($attributes, $client_id);
        Cache::tags('Client_' . $ClientObj->id)->flush();

        return $ClientObj;
    }
}

