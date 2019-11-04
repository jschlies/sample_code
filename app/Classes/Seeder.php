<?php

namespace App\Waypoint;

use \Faker\Generator as Faker_Generator;
use \Faker\Provider\en_US\Company as Faker_Provider_en_US_Company;
use \Faker\Provider\en_US\Person as Faker_Provider_en_US_Person;
use App;
use App\Waypoint\Console\Commands\ListUsersCommand;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\ReportTemplate;
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\AdvancedVarianceRepository;
use App\Waypoint\Repositories\LeaseRepository;
use App\Waypoint\Repositories\PasswordRuleRepository;
use App\Waypoint\Repositories\ReportTemplateRepository;
use App\Waypoint\Repositories\UserRepository;
use App\Waypoint\Tests\Factory;
use App\Waypoint\Tests\Mocks\Auth0ApiManagementConnectionMock;
use App\Waypoint\Tests\Mocks\Auth0ApiManagementUserMock;
use App\Waypoint\Tests\Mocks\NativeCoaLedgerMockRepository;
use App\Waypoint\Tests\Mocks\RentRollMockRepository;
use Exception;
use NativeCoaSeeder;
use ReportTemplateAccountGroupSeeder;

class Seeder extends \Illuminate\Database\Seeder
{
    use AllRepositoryTrait;

    const ACCESS_LISTS_NOT_COUNTING_ALLS_PER_CLIENT = 5;
    const ADVANCED_VARIANCES_PER_CLIENT             = 1;
    /**
     * See below. This meaning of these is not obvious
     */
    const CLIENT_ADMIN_PERCENT                   = 50;
    const PROPERTY_GROUP_PERCENT                 = 30;
    const NON_ACCESS_LIST_USER_PERCENT           = 50;
    const ACCESS_LIST_PROPERTY_PERCENT           = 10;
    const PROPERTY_GROUP_PARENT_PERCENT          = 10;
    const PROPERTY_FAVORITE_ENTITY_PERCENT       = 20;
    const PROPERTY_GROUP_FAVORITE_ENTITY_PERCENT = 20;
    const RELATED_USER_PERCENT                   = 10;
    const OPPORTUNITIES_PERCENT                  = 50;
    const NO_FRILL_PROPERTY_PERCENT              = 0;
    const LEASED_SUITES_PER_PROPERTY             = 3;
    const LEASES_PER_SUITE                       = 2;
    const UNLEASED_SUITES_PER_PROPERTY           = 10;
    const TAG_PERCENT                            = 50;
    const ECM_PROJECTS_PER_PROPERTY              = 10;
    const RELATED_USER_TYPES_PER_CLIENT          = 10;

    /** @var Repository */
    public $ModelRepositoryObj;
    protected $attributes;
    protected $count;
    protected $factory_name;
    protected $resulting_class;
    public static $tricky_string = '*\'%&"*';

    const DEFAULT_FACTORY_NAME = 'default';
    const PHPUNIT_FACTORY_NAME = 'phpunit';
    public static $factory_name_values_arr = [
        self::DEFAULT_FACTORY_NAME,
        self::PHPUNIT_FACTORY_NAME,
    ];

    /**
     * @var null if $client_suffix=null, always use $unit_test_client_name
     */
    protected static $client_suffix = null;
    public static $unit_test_client_name = 'Premiere Properties';
    private static $currnet_client_id = null;

    /**
     * @param null $currnet_client_id
     */
    public static function setCurrnetClientId($currnet_client_id): void
    {
        self::$currnet_client_id = $currnet_client_id;
    }

    /** @var  Faker_Generator */
    private static $FakerObj;

    /** @var Client */
    protected $ClientObj;

    /**
     * @return Faker_Generator
     */
    public static function getFakerObj(): Faker_Generator
    {
        if ( ! Seeder::$FakerObj)
        {
            $FakerObj = \Faker\Factory::create();
            $FakerObj->addProvider(new Faker_Provider_en_US_Company($FakerObj));
            $FakerObj->addProvider(new Faker_Provider_en_US_Person($FakerObj));
            Seeder::setFakerObj($FakerObj);
        }
        return Seeder::$FakerObj;
    }

    /**
     * @param Faker_Generator $FakerObj
     */
    public static function setFakerObj(Faker_Generator $FakerObj): void
    {
        self::$FakerObj = $FakerObj;
    }

    /**
     * Seeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     *
     * @throws \Exception
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        if (App::environment() === 'production')
        {
            throw new Exception('What, you crazy!!!!! No Seeders in production context ' . __FILE__);
        }

        $this->setAttributes($seeder_provided_attributes_arr);
        $this->setCount($count);
        $this->setFactoryName($factory_name);

        /**
         * we UNCONDITIONALLY use mocks in seeders, if you really, really need to hit the non-mock, tough.
         */
        ListUsersCommand::setAuth0ManagementUsersObj(new Auth0ApiManagementUserMock());
        UserRepository::setAuth0ApiManagementUserObj(new Auth0ApiManagementUserMock());
        PasswordRuleRepository::setAuth0ApiManagementConnectionObj(new Auth0ApiManagementConnectionMock());
        LeaseRepository::setRentRollRepositoryObj(new RentRollMockRepository());
        AdvancedVarianceRepository::setNativeCoaLedgerRepositoryObj(new NativeCoaLedgerMockRepository());

        $this->loadAllRepositories(true);
    }

    /**
     * @return null
     */
    public static function getUnitTestClientName()
    {
        return 'SEEDED - ' . self::$unit_test_client_name . ' ' . mt_rand(10000, 99999);
    }

    /**
     * @return string|null
     */
    public static function getClientSuffix()
    {
        return self::$client_suffix;
    }

    /**
     * @param string $client_suffix
     */
    public static function setClientSuffix(string $client_suffix): void
    {
        self::$client_suffix = $client_suffix;
    }

    /**
     * Run the database seeds.
     *
     * @return Collection
     */
    public function run()
    {
        try
        {
            return
                collect_waypoint(
                    $this->factory(
                        $this->getResultingClass(),
                        $this->getFactoryName(),
                        $this->count
                    )->raw(
                        $this->getAttributes()
                    )
                )->map(
                    function ($item)
                    {
                        try
                        {
                            $ResultObj = $this->ModelRepositoryObj->create($item);
                        }
                        catch (Exception $e)
                        {
                            throw $e;
                        }
                        return $ResultObj;
                    }
                );
        }
        catch (Exception $e)
        {
            throw $e;
        }
    }

    /**
     * @return mixed
     */
    function factory()
    {
        $factory = app(Factory::class);

        $arguments = func_get_args();

        if (isset($arguments[1]) && is_string($arguments[1]))
        {
            return $factory->of($arguments[0], $arguments[1])->times(isset($arguments[2]) ? $arguments[2] : 1);
        }
        elseif (isset($arguments[1]))
        {
            return $factory->of($arguments[0])->times($arguments[1]);
        }
        else
        {
            return $factory->of($arguments[0]);
        }
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount($count)
    {
        $this->count = (integer) $count;
    }

    /**
     * @return mixed
     */
    public function getFactoryName()
    {
        return $this->factory_name;
    }

    /**
     * @param mixed $factory_name
     */
    public function setFactoryName($factory_name)
    {
        $this->factory_name = $factory_name;
    }

    /**
     * @return mixed
     */
    public function getResultingClass()
    {
        return $this->resulting_class;
    }

    /**
     * @param mixed $resulting_class
     */
    public function setResultingClass($resulting_class)
    {
        $this->resulting_class = $resulting_class;
    }

    /**
     * @return string
     *
     * Unless self::getClientSuffix(), return unit test client name
     */
    public static function getFakeClientName()
    {
        if (self::getClientSuffix())
        {
            return 'SEEDED - ' . Seeder::getFakerObj()->company . ' ' . Seeder::getFakerObj()->companySuffix . ' ' . self::getClientSuffix();
        }
        return Seeder::getUnitTestClientName();
    }

    /**
     * @return string
     */
    public static function getFakeCompanyName()
    {
        return Seeder::getFakerObj()->company . Seeder::$tricky_string . mt_rand(1000, 999999) . ' ' . Seeder::getFakerObj()->companySuffix;
    }

    /**
     * @return string
     */
    public static function getFakePropertyName()
    {
        return Seeder::getFakerObj()->company . Seeder::$tricky_string . mt_rand(1000, 999999) . ' Building';
    }

    /**
     * @return string
     */
    public static function getFakeDescription()
    {
        return Seeder::getFakerObj()->words(4, true) . Seeder::$tricky_string . mt_rand(1000, 999999);
    }

    /**
     * @return string
     */
    public static function getFakeName()
    {
        return Seeder::getFakerObj()->words(3, true) . Seeder::$tricky_string . ' ' . mt_rand(1000, 999999);
    }

    /**
     * @param int $length
     * @return string
     */
    public static function getRandomString($length = 16)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randstring = '';
        for ($i = 0; $i < $length; $i++)
        {
            $randstring .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randstring;
    }

    /**
     * @return object
     */
    public static function getFakeClientConfigJson()
    {
        $config_arr = [
            'CODE'                             => Seeder::getFakerObj()->currencyCode,
            'LOCALE'                           => Seeder::getFakerObj()->locale,
            'FEATURE_OPPORTUNITIES'            => true,
            'NOTIFICATIONS'                    => true,
            'CUSTOM_REPORT_TEMPLATE_ANALYTICS' => false,
            'FEATURE_TASKS'                    => true,
            'DEFAULT_EXPENSE_UNIT_DENOMINATOR' => 'unit',
            'FEATURE_VARIANCE'                 => true,
            'FEATURE_SUMMARY_TAB'              => true,
            'FEATURE_VARIANCE_FORECAST'        => true,
            'DISABLE_BENCHMARK'                => false,
            'SUPPRESS_PRE_CALC_USAGE'          => true,
            'SUPPRESS_PRE_CALC_EVENTS'         => true,
            'ENABLE_AUDITS'                    => false,
        ];
        foreach (User::$user_notification_flags as $notification_type)
        {
            $config_arr[$notification_type]                     = true;
            $config_arr[$notification_type . '_ADMIN_OVERRIDE'] = false; // shall not override the user level notification config setting
        }
        $config_arr['WAYPOINT_LEDGER_DROPDOWNS'] = Seeder::getSeedClientConfigDropdownDefaultValues();

        return json_decode(
            json_encode($config_arr)
        );
    }

    /**
     * @return mixed
     */
    public static function getFakeUserConfigJson()
    {
        // TODO (Alex) - remove USER_PROFILE_NOTIFICATIONS as we don't use it anymore
        $config_arr['USER_PROFILE_NOTIFICATIONS']                 = true;
        $config_arr[User::DEFAULT_ANALYTICS_REPORT_TEMPLATE_FLAG] = true;
        foreach (User::$user_notification_flags as $notification_type)
        {
            $config_arr[$notification_type] = true;
        }
        return json_decode(
            json_encode($config_arr)
        );
    }

    /**
     * return true "$howOftenTrue" percent of the time
     * @param $howOftenTrue
     * @return bool
     */
    public static function trueSomeOfTheTime($howOftenTrue)
    {
        return mt_rand(0, 1000) < ($howOftenTrue * 10);
    }

    /** @var Collection|null */
    private static $LeafBomaReportTemplateAccountGroupObjArr = null;
    /** @var ReportTemplate|null */
    private static $DummyClientBomaBasedReportTemplateObj = null;

    /**
     * @return Collection
     */
    public static function getLeafBomaReportTemplateAccountGroupObjArr()
    {
        if ( ! self::$LeafBomaReportTemplateAccountGroupObjArr)
        {
            if ( ! self::$DummyClientBomaBasedReportTemplateObj)
            {
                $ReportTemplateRepositoryObj                 = App::make(ReportTemplateRepository::class)->setSuppressEvents(false);
                self::$DummyClientBomaBasedReportTemplateObj = $ReportTemplateRepositoryObj
                    ->with('reportTemplateAccountGroups.reportTemplateAccountGroupChildren')
                    ->find(1);
            }
            /** @var ReportTemplateAccountGroup $DummyReportTemplateAccountGroupObj */
            self::$LeafBomaReportTemplateAccountGroupObjArr = self::$DummyClientBomaBasedReportTemplateObj->reportTemplateAccountGroups->filter(
                function ($DummyReportTemplateAccountGroupObj)
                {
                    return $DummyReportTemplateAccountGroupObj->reportTemplateAccountGroupChildren->count() == 0;
                }
            );
        }
        return self::$LeafBomaReportTemplateAccountGroupObjArr;
    }

    /**
     * @param $report_template_account_group_name
     */
    public static function deleteLeafOfBomaReportTemplateAccountGroupObjArr($report_template_account_group_name)
    {
        self::$LeafBomaReportTemplateAccountGroupObjArr = self::$LeafBomaReportTemplateAccountGroupObjArr->filter(
            function ($LeafBomaReportTemplateAccountGroupObj) use ($report_template_account_group_name)
            {
                return $LeafBomaReportTemplateAccountGroupObj->report_template_account_group_name !== $report_template_account_group_name;
            }
        );
    }

    /**
     * @return array
     */
    public static function getSeedClientConfigDropdownDefaultValues()
    {
        return [
            "FILTERS"  => [
                "area"     => [
                    "RENTABLE",
                    "OCCUPIED",
                ],
                "report"   => [
                    "ACTUAL",
                    "BUDGET",
                ],
                "period"   => [
                    "CY",
                    "T12",
                    "YTD",
                ],
                "year"     => [
                    "2017",
                    "2016",
                    "2015",
                    "2014",
                    "2013",
                ],
                "peerYear" => [
                    "2017",
                ],
            ],
            "DEFAULTS" => [
                "area"     => "RENTABLE",
                "report"   => "ACTUAL",
                "code"     => [
                    "Operating Expenses" => "40_000_h2",
                ],
                "period"   => "T12",
                "year"     => "2017",
                "peerYear" => "2017",
            ],
        ];
    }

    /**
     * @param $PropertyObj
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function generate_native_accounts_and_mapping($ClientObj)
    {
        foreach ($ClientObj->reportTemplates as $ReportTemplateObj)
        {
            if ( ! $ReportTemplateObj->reportTemplateAccountGroups->where('is_category', '=', 0)->count())
            {
                foreach ($ReportTemplateObj->reportTemplateAccountGroups->where('is_category', '=', 1) as $ReportTemplateAccountGroupObj)
                {
                    $ReportTemplateAccountGroupSeederObj = new ReportTemplateAccountGroupSeeder(
                        [
                            'client_id'                               => $ClientObj->id,
                            'report_template_id'                      => $ReportTemplateObj->id,
                            'is_category'                             => 0,
                            'parent_report_template_account_group_id' => $ReportTemplateAccountGroupObj->id,
                            'native_account_type_id'                  => $ReportTemplateAccountGroupObj->native_account_type_id,
                        ],
                        1,
                        Seeder::PHPUNIT_FACTORY_NAME
                    );
                    $ReportTemplateAccountGroupSeederObj->run();
                }
            }
        }
        /** @var Client $ClientObj */
        if ( ! $NativeCoaObj = $ClientObj->nativeCoas->first())
        {
            $NativeCoaSeederObj = new NativeCoaSeeder(
                ['client_id' => $ClientObj->id]
            );
            $NativeCoaObj       = $NativeCoaSeederObj->run()->first();
        }
        $ClientObj = Client::find($ClientObj->id);
        /** @var ReportTemplate $ReportTemplateObj */
        foreach ($ClientObj->reportTemplates as $ReportTemplateObj)
        {
            $LeafReportTemplateAccountGroupObjArr  = $ReportTemplateObj->reportTemplateAccountGroups->where('is_category', '=', 0);
            $ReportTemplateAccountGroupCategoryObj = $ReportTemplateObj->reportTemplateAccountGroups->where('is_category', '=', 1)->first();

            $i = 0;
            foreach ($LeafReportTemplateAccountGroupObjArr as $LeafReportTemplateAccountGroupObj)
            {
                $NativeAccountObjArr = $this->NativeAccountRepositoryObj->findWhere(
                    [
                        'native_coa_id'       => $NativeCoaObj->id,
                        'native_account_code' => $LeafReportTemplateAccountGroupObj->report_template_account_group_code,
                    ]
                );
                if ( ! $NativeAccountObjArr->count()
                )
                {
                    $NativeAccountObj = $this->NativeAccountRepositoryObj->create(
                        [
                            'client_id'                => $ClientObj->id,
                            'native_coa_id'            => $NativeCoaObj->id,
                            'parent_native_account_id' => $ReportTemplateAccountGroupCategoryObj->id,
                            'native_account_name'      => $LeafReportTemplateAccountGroupObj->report_template_account_group_name,
                            'native_account_code'      => $LeafReportTemplateAccountGroupObj->report_template_account_group_code,
                            'native_account_type_id'   => $LeafReportTemplateAccountGroupObj->native_account_type_id,
                            'is_category'              => $LeafReportTemplateAccountGroupObj->is_category,
                        ]
                    );
                }
                else
                {
                    $NativeAccountObj = $NativeAccountObjArr->first();
                }

                $this->ReportTemplateMappingRepositoryObj->create(
                    [
                        'native_account_id'                => $NativeAccountObj->id,
                        'report_template_account_group_id' => $LeafReportTemplateAccountGroupObj->id,
                    ]
                );

                if ($i++ > Seeder::LEASES_PER_SUITE)
                {
                    break;
                }
            }

        }
    }
}
