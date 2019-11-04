<?php

use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\ApiKey;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\NativeCoa;
use App\Waypoint\Models\ReportTemplate;
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\CalculateVariousPropertyListsRepository;
use App\Waypoint\Repositories\ClientCategoryRepository;
use App\Waypoint\Repositories\ClientRepository;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class ClientSeeder
 */
class ClientSeeder extends Seeder
{
    const THESEVENCONTINENTS        = ['Africa', 'Europe', 'Asia', 'NorthAmerica', 'SouthAmerica', 'Australia', 'Antarctica'];
    const THESEVENDWARFS            = ['Happy', 'Doc', 'Grumpy', 'Sleepy', 'Bashful', 'Sneezy', 'Dopey'];
    const THESEVENDEADLYSINS        = ['Lust', 'Gluttony', 'Greed', 'Sloth', 'Wrath', 'Envy', 'Pride'];
    const THESEVENCARDINALVIRTUES   = ['Chastity', 'Temperance', 'Charity', 'Diligence', 'Patience', 'Kindness', 'Humility'];
    const THESEVENWONDERSOFTHEWORLD =
        [
            'The Great Pyramid of Giza',
            'The Hanging Gardens of Babylon',
            'The Colossus of Rhodes',
            'The Lighthouse of Alexandria',
            'The Mausoleum at Halicarnassus',
            'The Temple of Artemis',
            'The Statue of Zeus',
        ];
    const THEDAYSOFTHEWEEK          = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    private $LeafBomaReportTemplateAccountGroupObjArr;

    /**
     * ClientSeeder constructor.
     * @param array $seeder_provided_data_arrr
     * @param int $count
     * @param string $factory_name
     * @throws Exception
     */
    public function __construct($seeder_provided_data_arrr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_data_arrr, $count, $factory_name);
        $this->setResultingClass(Client::class);
        $this->ModelRepositoryObj = App::make(ClientRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return Collection
     */
    public function run()
    {
        $ClientObjArr = parent::run();
        /**
         * note that ModelRepositoryObj->create() can create several
         * objects by practice, NativeCoa, ClientCategory and others so
         * understand whats already taken care of in ModelRepositoryObj->create()
         * Here is a breakdown
         * $this->initClient($ClientObj->id);
         *      allAccessList is created
         *      client config updates
         *      created these client categories ClientCategory::$default_client_categories_arr
         * $this->initClientNativeAccountTypes($ClientObj->id);
         *      created these native account types NativeAccount::$native_coa_type_arr
         * $this->initClientNativeCoa($ClientObj->id);
         *      created a COA
         * $this->initClientNativeAccountTypeTrailers($ClientObj->id);
         *      created NativeAccountTypeTrailers for all NativeAccountTypes created above
         * $this->initClientReportTemplates($ClientObj->id);
         *      $this->ReportTemplateRepositoryObj->generateAccountTypeBasedReportTemplate($client_id);
         *          create a report template based on the Dummy Clients (id=1) BOMA report template.
         *              Since no native accounts exist yet this are no report_template_mappings
         *      $this->ReportTemplateRepositoryObj->generateBomaBasedReportTemplate($client_id);
         *          create a report template based on the native account types created above.
         *              Since no native accounts exist yet this are no report_template_mappings
         *
         */

        foreach ($ClientObjArr as &$ClientObj)
        {
            /**
             * @var Client $ClientObj
             *
             * Note that these were run as part of $ClientRepositoryObj->create()
             *      $this->initClient($ClientObj->id);
             *      $this->initClientNativeAccountTypes($ClientObj->id);
             *      $this->initClientNativeCoa($ClientObj->id);
             *      $this->initClientNativeAccountTypeTrailers($ClientObj->id);
             *      $this->initClientReportTemplates($ClientObj->id);
             *
             */
            echo 'Seeding Client for ' . $ClientObj->name . PHP_EOL;

            echo 'Seeding Native Accounts for ' . $ClientObj->name . PHP_EOL;
            /**
             * we process this special because of the many connections, relations and
             * conventions in native accounts.
             *
             * @var NativeCoa $NativeCoaObj
             */
            $this->initLeafBomaReportTemplateAccountGroupObjArr($ClientObj);

            echo 'Seeding AdvancedVariance generate_native_accounts_and_mapping for ' . $ClientObj->name . PHP_EOL;
            $this->generate_native_accounts_and_mapping($ClientObj);

            $ClientObj = Client::find($ClientObj->id);

            /** @var ReportTemplate $ReportTemplateObj */
            foreach ($ClientObj->reportTemplates as $ReportTemplateObj)
            {
                if ( ! $ReportTemplateObj->is_default_advance_variance_report_template)
                {
                    continue;
                }
                /** @var ClientCategoryRepository $BomaClientCagegoryRepositoryObj */
                $CalculatedFieldSeederObj = new CalculatedFieldSeeder(
                    [
                        'client_id'          => $ReportTemplateObj->client_id,
                        'report_template_id' => $ReportTemplateObj->id,
                    ],
                    10,
                    Seeder::PHPUNIT_FACTORY_NAME
                );
                $CalculatedFieldObjArr    = $CalculatedFieldSeederObj->run();
                /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
                foreach ($ReportTemplateObj->reportTemplateAccountGroups as $ReportTemplateAccountGroupObj)
                {
                    if ($ReportTemplateMappingObj = $ReportTemplateAccountGroupObj->reportTemplateMappings->first())
                    {
                        break;
                    }
                }

                foreach ($ClientObj->properties as $PropertyObj)
                {
                    foreach ($CalculatedFieldObjArr as $CalculatedFieldObj)
                    {
                        $this->CalculatedFieldEquationRepositoryObj->create(
                            [
                                'calculated_field_id' => $CalculatedFieldObj->id,
                                'property_id'         => $PropertyObj->id,
                                'equation_string'     => '[NA_' . $ReportTemplateMappingObj->native_account_id . '] + 1000 * [RTAG_' . $ReportTemplateMappingObj->report_template_account_group_id . '] + ' . mt_rand(),
                            ]
                        );
                    }
                }
            }
            /**
             * deal with client configs
             */
            $ClientObj->setConfigJSON(Seeder::getFakeClientConfigJson());

            echo 'Seeding AdvancedVarianceExplanationTypes for ' . $ClientObj->name . PHP_EOL;
            $AdvancedVarianceExplanationTypeSeederObj = new AdvancedVarianceExplanationTypeSeeder(
                [
                    'client_id' => $ClientObj->id,
                ],
                3
            );
            $AdvancedVarianceExplanationTypeSeederObj->run();

            /**
             * make some AccessLists
             */
            $this->generateSeededAccessLists($ClientObj);

            echo 'Seeding ClientCategories for ' . $ClientObj->name . PHP_EOL;
            $ClientCategorySeederObj = new ClientCategorySeeder(
                [
                    'client_id' => $ClientObj->id,
                ]
            );
            $ClientCategorySeederObj->run()->first();

            echo 'Seeding AssetTypes for ' . $ClientObj->name . PHP_EOL;
            $AssetTypeSeederObj = new AssetTypeSeeder(
                [
                    'client_id' => $ClientObj->id,
                ],
                5
            );
            $AssetTypeSeederObj->run();

            /**
             * lets create some generic users and some admins for users in unit testing
             *
             * not using factory since we want particular names/pw's
             *
             * THis section creates the following
             * Seven CLIENT_GENERIC_USER_ROLE for the client in question - 'Lust', 'Gluttony', 'Greed', 'Sloth', 'Wrath', 'Envy', 'Pride'
             * Seven CLIENT_ADMINISTRATIVE_USER_ROLE for the client in question - 'Chastity', 'Temperance', 'Charity', 'Diligence', 'Patience', 'Kindness', 'Humility'
             * Seven WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE for the client in question - 'Africa', 'Europe', 'Asia', 'NorthAmerica', 'SouthAmerica', 'Australia', 'Antarctica'
             * Seven WAYPOINT_ASSOCIATE_ROLE for the client in question - 'Happy', 'Doc', 'Grumpy', 'Sleepy', 'Bashful', 'Sneezy', 'Dopey'
             */
            $this->generateSeededUsers($ClientObj);

            $this->ClientRepositoryObj->initAdvancedVariance($ClientObj->id);

            $this->generateSeededProperties($ClientObj);

            echo 'Seeding RelatedUsers for ' . $ClientObj->name . PHP_EOL;

            $RelatedUserTypeSeederObj = new RelatedUserTypeSeeder(
                [
                    'client_id' => $ClientObj->id,
                ],
                Seeder::RELATED_USER_TYPES_PER_CLIENT
            );
            $RelatedUserTypeSeederObj->run();

            $this->ClientRepositoryObj->initAdvancedVariance($ClientObj->id);

            /***
             * SPECIFIC UNIT TEST SETUOP
             */
            $AdvancedVarianceMonthlyPropertyObj =
                $this->PropertyRepositoryObj->findWhere(
                    ['client_id' => $ClientObj->id, 'name' => 'The Great Pyramid of Giza']
                )->first();

            echo 'Seeding AdvancedVariance for (monthly) ' . $AdvancedVarianceMonthlyPropertyObj->name . PHP_EOL;
            $AdvancedVarienceSeederObj = new AdvancedVarianceSeeder(
                [
                    'client_id'   => $AdvancedVarianceMonthlyPropertyObj->client_id,
                    'property_id' => $AdvancedVarianceMonthlyPropertyObj->id,
                    'period_type' => AdvancedVariance::PERIOD_TYPE_MONTHLY,
                ],
                self::ADVANCED_VARIANCES_PER_CLIENT,
                self::PHPUNIT_FACTORY_NAME
            );
            $AdvancedVarienceSeederObj->run();

            $AdvancedVarianceQuarterlyPropertyObj =
                $this->PropertyRepositoryObj->findWhere(
                    ['client_id' => $ClientObj->id, 'name' => 'The Hanging Gardens of Babylon']
                )->first();

            echo 'Seeding AdvancedVariance for (quarterly) ' . $AdvancedVarianceQuarterlyPropertyObj->name . PHP_EOL;
            $AdvancedVarienceSeederObj = new AdvancedVarianceSeeder(
                [
                    'client_id'   => $AdvancedVarianceQuarterlyPropertyObj->client_id,
                    'property_id' => $AdvancedVarianceQuarterlyPropertyObj->id,
                    'period_type' => AdvancedVariance::PERIOD_TYPE_MONTHLY,
                ],
                self::ADVANCED_VARIANCES_PER_CLIENT,
                self::PHPUNIT_FACTORY_NAME
            );
            $AdvancedVarienceSeederObj->run();

            $this->CalculateVariousPropertyListsRepositoryObj = App::make(CalculateVariousPropertyListsRepository::class)->setSuppressEvents(true);
            $this->CalculateVariousPropertyListsRepositoryObj->CalculateVariousPropertyListsJobProcessor($ClientObj->id);
        }
        return $ClientObjArr;
    }

    /**
     * @param integer $client_id
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function initClientReportTemplates($client_id)
    {
        $this->ReportTemplateRepositoryObj->generateAccountTypeBasedReportTemplate($client_id);
        $this->ReportTemplateRepositoryObj->generateBomaBasedReportTemplate($client_id);
    }

    /**
     * @param $ClientObj
     * @throws Exception
     */
    public function generateSeededUsers($ClientObj)
    {
        echo 'Seeding CLIENT_GENERIC_USER_ROLE Users for ' . $ClientObj->name . PHP_EOL;
        foreach (self::THESEVENDEADLYSINS as $deadly_sin)
        {
            echo 'Seeding CLIENT_GENERIC_USER_ROLE User ' . $deadly_sin . PHP_EOL;
            /** @var User $ClientGenericUserObj */
            $ClientGenericUserObj = $UserSeederObj = $this->UserRepositoryObj->create(
                [
                    'client_id'     => $ClientObj->id,
                    'firstname'     => $deadly_sin . ' Client Generic User',
                    'lastname'      => $deadly_sin . ' Client Generic User',
                    'email'         => $deadly_sin . 'ClientGeneric.' . $ClientObj->id . '@' . TestCase::getUnitTestEmailDomain(),
                    'user_name'     => $deadly_sin . 'ClientGeneric.' . $ClientObj->id,
                    'active_status' => User::ACTIVE_STATUS_ACTIVE,
                    'password'      => config('waypoint.password_change_token_secret_word'),
                ]
            );

            $ClientGenericUserObj->user_invitation_status = User::USER_INVITATION_STATUS_ACCEPTED;
            $ClientGenericUserObj->save();
            $ClientGenericUserObj->attachRole(Role::where('name', Role::CLIENT_GENERIC_USER_ROLE)->first(), true);
            if ( ! $ClientGenericUserObj->apiKey)
            {
                ApiKey::make($ClientGenericUserObj->id);
            }
        }

        echo 'Seeding CLIENT_ADMINISTRATIVE_USER_ROLE Users ' . $ClientObj->name . PHP_EOL;
        foreach (self::THESEVENCARDINALVIRTUES as $cardinal_virtue)
        {
            echo 'Seeding CLIENT_ADMINISTRATIVE_USER_ROLE User ' . $cardinal_virtue . PHP_EOL;
            /** @var User $ClientAdminUserObj */
            $ClientAdminUserObj = $this->UserRepositoryObj->create(
                [
                    'client_id'     => $ClientObj->id,
                    'firstname'     => $cardinal_virtue . ' Client Admin User',
                    'lastname'      => $cardinal_virtue . ' Client Admin User',
                    'email'         => $cardinal_virtue . 'ClientAdmin.' . $ClientObj->id . '@' . TestCase::getUnitTestEmailDomain(),
                    'user_name'     => $cardinal_virtue . 'ClientAdmin.' . $ClientObj->id,
                    'active_status' => User::ACTIVE_STATUS_ACTIVE,
                    'password'      => config('waypoint.password_change_token_secret_word'),
                ]
            );

            $ClientGenericUserObj->user_invitation_status = User::USER_INVITATION_STATUS_ACCEPTED;
            $ClientGenericUserObj->save();
            $ClientAdminUserObj->attachRole(Role::where('name', Role::CLIENT_ADMINISTRATIVE_USER_ROLE)->first(), true);
            if ( ! $ClientAdminUserObj->apiKey)
            {
                ApiKey::make($ClientAdminUserObj->id);
            }
        }

        echo 'Seeding WAYPOINT_ASSOCIATE_ROLE Users ' . $ClientObj->name . PHP_EOL;
        foreach (self::THESEVENDWARFS as $dwarf)
        {
            echo 'Seeding WAYPOINT_ASSOCIATE_ROLE User ' . $dwarf . PHP_EOL;
            /** @var User $WaypointAssoUserObj */
            $WaypointAssoUserObj = $this->UserRepositoryObj->create(
                [
                    'client_id'     => $ClientObj->id,
                    'firstname'     => $dwarf . ' Waypoint Asso User',
                    'lastname'      => $dwarf . ' Waypoint Asso User',
                    'email'         => $dwarf . 'WaypointAsso.' . $ClientObj->id . '@' . TestCase::getUnitTestEmailDomain(),
                    'user_name'     => $dwarf . 'WaypointAsso.' . $ClientObj->id,
                    'active_status' => User::ACTIVE_STATUS_ACTIVE,
                    'password'      => config('waypoint.password_change_token_secret_word'),
                ]
            );

            $ClientGenericUserObj->user_invitation_status = User::USER_INVITATION_STATUS_ACCEPTED;
            $ClientGenericUserObj->save();
            $WaypointAssoUserObj->attachRole(Role::where('name', Role::WAYPOINT_ASSOCIATE_ROLE)->first(), true);
            if ( ! $WaypointAssoUserObj->apiKey)
            {
                ApiKey::make($WaypointAssoUserObj->id);
            }
        }

        echo 'Seeding WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE Users ' . $ClientObj->name . PHP_EOL;
        foreach (self::THESEVENCONTINENTS as $continent)
        {
            echo 'Seeding WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE User ' . $continent . PHP_EOL;
            /** @var User $WaypointSystemAdminUserObj */
            $WaypointSystemAdminUserObj = $this->UserRepositoryObj->create(
                [
                    'client_id'     => $ClientObj->id,
                    'firstname'     => $continent . ' Waypoint System Admin User',
                    'lastname'      => $continent . ' Waypoint System Admin User',
                    'email'         => $continent . 'WaypointSystemAdmin.' . $ClientObj->id . '@' . TestCase::getUnitTestEmailDomain(),
                    'user_name'     => $continent . 'WaypointSystemAdmin.' . $ClientObj->id,
                    'active_status' => User::ACTIVE_STATUS_ACTIVE,
                    'password'      => config('waypoint.password_change_token_secret_word'),
                ]
            );

            $ClientGenericUserObj->user_invitation_status = User::USER_INVITATION_STATUS_ACCEPTED;
            $ClientGenericUserObj->save();
            $WaypointSystemAdminUserObj->attachRole(Role::where('name', Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE)->first(), true);
            if ( ! $WaypointSystemAdminUserObj->apiKey)
            {
                ApiKey::make($WaypointSystemAdminUserObj->id);
            }
        }

    }

    /**
     * @param Client $ClientObj
     */
    public function generateSeededProperties(Client $ClientObj)
    {
        echo 'Seeding Properties for ' . $ClientObj->name . PHP_EOL;
        foreach (
            self::THESEVENWONDERSOFTHEWORLD as $wonder_of_the_world)
        {
            echo 'Seeding Property ' . $wonder_of_the_world . PHP_EOL;
            $PropertySeederObj = new PropertySeeder(
                [
                    'client_id'     => $ClientObj->id,
                    'asset_type_id' => $ClientObj->assetTypes->random()->id,
                    'name'          => $wonder_of_the_world,
                ]
            );
            $PropertySeederObj->run();
        }
    }

    /**
     * @param Client $ClientObj
     */
    public function initClientNativeAccounts(Client $ClientObj)
    {
        foreach ($ClientObj->nativeCoas as $NativeCoaObj)
        {
            echo 'Seeding Native Accounts ' . $ClientObj->name . PHP_EOL;
            shuffle($this->LeafBomaReportTemplateAccountGroupObjArr);
            foreach ($this->LeafBomaReportTemplateAccountGroupObjArr as $LeafReportTemplateAccountGroupObj)
            {
                $this->NativeAccountRepositoryObj->create(
                    [
                        'native_coa_id'            => $NativeCoaObj->id,
                        'native_account_name'      => $LeafReportTemplateAccountGroupObj->report_template_account_group_name,
                        'native_account_code'      => Seeder::getFakerObj()->randomLetter . Seeder::getFakerObj()->randomLetter . Seeder::getFakerObj()->isbn13,
                        'parent_native_account_id' => null,
                        'is_category'              => false,
                        'is_recoverable'           => false,
                        'native_account_type_id'   => $ClientObj
                            ->nativeAccountTypes
                            ->where(
                                'native_account_type_name',
                                $LeafReportTemplateAccountGroupObj->nativeAccountType->native_account_type_name)
                            ->first()->id,
                    ]
                );
            }
        }
    }

    /**
     * @param Client $ClientObj
     */
    public function initLeafBomaReportTemplateAccountGroupObjArr(Client $ClientObj)
    {
        $DummyClientBomaBasedReportTemplateObj          = $this->ReportTemplateRepositoryObj
            ->with('reportTemplateAccountGroups.reportTemplateAccountGroupChildren')
            ->find(1);
        $this->LeafBomaReportTemplateAccountGroupObjArr = [];
        /** @var ReportTemplateAccountGroup $DummyReportTemplateAccountGroupObj */
        foreach ($DummyClientBomaBasedReportTemplateObj->reportTemplateAccountGroups as $DummyReportTemplateAccountGroupObj)
        {
            /**
             * if it's no ones parent
             */
            if ( ! $DummyReportTemplateAccountGroupObj->reportTemplateAccountGroupChildren->count())
            {
                $this->LeafBomaReportTemplateAccountGroupObjArr[] = $DummyReportTemplateAccountGroupObj;
            }
        }
    }

    /**
     * @param $ClientObj
     * @throws ValidatorException
     */
    public function generateSeededAccessLists($ClientObj)
    {
        foreach (self::THEDAYSOFTHEWEEK as $day_of_week)
        {
            echo 'Seeding AccessList ' . $day_of_week . PHP_EOL;
            /** @var User $ClientGenericUserObj */
            $this->AccessListRepositoryObj->create(
                [
                    'client_id'   => $ClientObj->id,
                    'name'        => $day_of_week . ' AccessList',
                    'description' => $day_of_week . ' AccessList',
                ]
            );
        }
    }
}