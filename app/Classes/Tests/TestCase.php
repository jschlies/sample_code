<?php

namespace App\Waypoint\Tests;

use \PHPUnit\Framework\TestResult as PHPUnitFrameworkTestResult;
use App;
use App\Waypoint\AllRepositoryTrait;
use App\Waypoint\Collection;
use App\Waypoint\Console\Commands\ListUsersCommand;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\ApiGuardAuth;
use App\Waypoint\Models\AccessList;
use App\Waypoint\Models\ApiKey;
use App\Waypoint\Models\ApiLog;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Permission;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\User;
use App\Waypoint\Notifications\Facades\Notification;
use App\Waypoint\Repositories\AdvancedVarianceRepository;
use App\Waypoint\Repositories\LeaseRepository;
use App\Waypoint\Repositories\PasswordRuleRepository;
use App\Waypoint\Repositories\UserRepository;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\Mocks\Auth0ApiManagementConnectionMock;
use App\Waypoint\Tests\Mocks\Auth0ApiManagementUserMock;
use App\Waypoint\Tests\Mocks\NativeCoaLedgerMockRepository;
use App\Waypoint\Tests\Mocks\RentRollMockRepository;
use Auth;
use Cache;
use Carbon\Carbon;
use DB;
use Exception;
use function config;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as TestCaseBase;
use ReflectionObject;

/**
 * Class TestCase
 * @package       App\Waypoint\Tests
 *
 * @codeCoverageIgnore
 */
abstract class TestCase extends TestCaseBase
{
    use AllRepositoryTrait;

    /** @var  User */
    protected $logged_in_user_id = null;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://homestead.app/';
    /** @var string|null */
    protected static $unit_test_email_domain = null;
    /** @var  Client */
    protected $ClientObj;
    /** @var  Client */
    public static $StaticClientObj = null;

    /** @var User */
    protected $FirstGenericUserObj;
    /** @var User */
    protected $SecondGenericUserObj;
    /** @var User */
    protected $ThirdGenericUserObj;
    /** @var User */
    protected $FourthGenericUserObj;
    /** @var User */
    protected $FifthGenericUserObj;
    /** @var User */
    protected $SixthGenericUserObj;
    /** @var User */
    protected $SeventhGenericUserObj;

    /** @var User */
    protected $FirstAdminUserObj;
    /** @var User */
    protected $SecondAdminUserObj;
    /** @var User */
    protected $ThirdAdminUserObj;
    /** @var User */
    protected $FourthAdminUserObj;
    /** @var User */
    protected $FifthAdminUserObj;
    /** @var User */
    protected $SixthAdminUserObj;
    /** @var User */
    protected $SeventhAdminUserObj;

    /** @var Property */
    protected $FirstPropertyObj;
    /** @var Property */
    protected $SecondPropertyObj;
    /** @var Property */
    protected $ThirdPropertyObj;
    /** @var Property */
    protected $FourthPropertyObj;
    /** @var Property */
    protected $FifthPropertyObj;
    /** @var Property */
    protected $SixthPropertyObj;
    /** @var Property */
    protected $SeventhPropertyObj;

    /** @var AccessList */
    protected $FirstAccessListObj;
    /** @var AccessList */
    protected $SecondAccessListObj;
    /** @var AccessList */
    protected $ThirdAccessListObj;
    /** @var AccessList */
    protected $FourthAccessListObj;
    /** @var AccessList */
    protected $FifthAccessListObj;
    /** @var AccessList */
    protected $SixthAccessListObj;
    /** @var AccessList */
    protected $SeventhAccessListObj;

    /** @var string */
    private static $unit_test_client_name = 'Premiere Properties';
    /** @var string */
    protected $logged_in_user_role = Role::WAYPOINT_ROOT_ROLE;

    /**
     * @param string $unit_test_client_name
     */
    public static function setUnitTestClientName(string $unit_test_client_name): void
    {
        self::$unit_test_client_name = $unit_test_client_name;
    }

    /**
     * TestCase constructor.
     * @param null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    /**
     * @param PHPUnitFrameworkTestResult|null $result
     * @return PHPUnitFrameworkTestResult
     * @throws \ReflectionException
     */
    public function run(PHPUnitFrameworkTestResult $result = null): PHPUnitFrameworkTestResult
    {
        $this->setPreserveGlobalState(false);

        ini_set('MAX_EXECUTION_TIME', -1);
        require __DIR__ . '/../../../bootstrap/helper.php';
        return parent::run($result);
    }

    /**
     * @throws GeneralException
     */
    public function setUp()
    {
        try
        {
            parent::setUp();
            if ( ! config('waypoint.commit_to_database_on_unittests', false))
            {
                DB::beginTransaction();
            }

            /**
             * we UNCONDITIONALLY use mocks in unit tests, if you really, really (be careful) need to hit the non-mock,
             * 1. load non-mock in the unit test setup
             *      ListUsersCommand::setAuth0ManagementUsersObj(new App\Waypoint\Tests\Mocks\Auth0ApiManagementUser());
             * 2 DO NOT forget to put the mock back in teardown
             *      ListUsersCommand::setAuth0ManagementUsersObj(new \App\Waypoint\Tests\Mocks\Auth0ApiManagementUserMock());
             *
             */
            ListUsersCommand::setAuth0ManagementUsersObj(new Auth0ApiManagementUserMock());
            UserRepository::setAuth0ApiManagementUserObj(new Auth0ApiManagementUserMock());
            PasswordRuleRepository::setAuth0ApiManagementConnectionObj(new Auth0ApiManagementConnectionMock());
            LeaseRepository::setRentRollRepositoryObj(new RentRollMockRepository());
            AdvancedVarianceRepository::setNativeCoaLedgerRepositoryObj(new NativeCoaLedgerMockRepository());
            $this->loadAllRepositories(true);

            /**
             * get a FRESH client from DB
             */
            $this->ClientObj = self::getUnitTestClient(true);

            $this->turn_on_fake_notifications();
            $this->logInUser();

            /**
             * @todo - this REALLY slows down unit tests
             * @todo - this REALLY slows down unit tests
             * @todo - this REALLY slows down unit tests
             * @todo - this REALLY slows down unit tests
             * @todo - this REALLY slows down unit tests
             * @todo - this REALLY slows down unit tests
             * @todo - this REALLY slows down unit tests
             * @todo - this REALLY slows down unit tests
             */
            if ($this->logged_in_user_role !== Role::WAYPOINT_ROOT_ROLE)
            {
                $this->ClientObj->addUserToAllAccessList($this->getLoggedInUserObj()->id);
            }
            $this->populateVariousUnittestObjects();
        }
        catch (GeneralException $e)
        {
            if ( ! config('waypoint.commit_to_database_on_unittests', false))
            {
                DB::rollBack();
                Cache::tags('Client_' . $this->ClientObj->id)->flush();
                Cache::tags('Property_' . $this->ClientObj->id)->flush();
                Cache::tags('PropertyGroup_' . $this->ClientObj->id)->flush();
                Cache::tags('AccessList_' . $this->ClientObj->id)->flush();
                Cache::tags('AdvancedVariance_' . $this->ClientObj->id)->flush();
                Cache::tags('BenchmarkGenerationDate_' . $this->ClientObj->id)->flush();
                Cache::tags('ProcessRefreshNativeAccountValues_' . $this->ClientObj->id)->flush();
            }
            throw $e;
        }
        catch (Exception $e)
        {
            if ( ! config('waypoint.commit_to_database_on_unittests', false))
            {
                DB::rollBack();
                Cache::tags('Client_' . $this->ClientObj->id)->flush();
                Cache::tags('Property_' . $this->ClientObj->id)->flush();
                Cache::tags('PropertyGroup_' . $this->ClientObj->id)->flush();
                Cache::tags('AccessList_' . $this->ClientObj->id)->flush();
                Cache::tags('AdvancedVariance_' . $this->ClientObj->id)->flush();
                Cache::tags('BenchmarkGenerationDate_' . $this->ClientObj->id)->flush();
                Cache::tags('ProcessRefreshNativeAccountValues_' . $this->ClientObj->id)->flush();
            }
            throw new GeneralException($e->getMessage(), 400, $e);
        }
    }

    /**
     * @return User|bool
     * @throws GeneralException
     */
    public function getLoggedInUserObj()
    {
        if ($this->logged_in_user_id)
        {
            return $this->UserRepositoryObj->find($this->logged_in_user_id);
        }
        return false;
    }

    /**
     * @param null
     */
    public function setLoggedInUserId($logged_in_user_id)
    {
        $this->logged_in_user_id = $logged_in_user_id;
    }

    /**
     * @return string
     */
    public function getLoggedInUserRole(): string
    {
        return $this->logged_in_user_role;
    }

    /**
     * @param string $logged_in_user_role
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function setLoggedInUserRole(string $logged_in_user_role)
    {
        $this->assertTrue(in_array($logged_in_user_role, Role::$waypoint_system_roles));
        $this->logged_in_user_role = $logged_in_user_role;
    }

    /**
     * @param User|null $UserObj
     * @return bool
     * @throws GeneralException
     *
     * NOTE NOTE NOTe that passing in a $UserObj nullifies $this->getLoggedInUserRole()
     */
    public function logInUser(User $UserObj = null)
    {
        $this->assertTrue(in_array($this->logged_in_user_role, Role::$waypoint_system_roles));
        /**
         * $this->logged_in_user_role is Role::WAYPOINT_ROOT_ROLE unless updated in Test::setup()
         */
        try
        {
            /**
             * if someone is logged in, see if we need to log them out and do so
             */
            if ($this->getLoggedInUserObj())
            {
                /**
                 * if $UserObj is already logged in.....
                 */
                if ($UserObj && $this->getLoggedInUserObj()->id == $UserObj->id)
                {
                    return true;
                }
                /**
                 * if no $UserObj passed in, check if the right kind of
                 * user per $this->getLoggedInUserRole() is logged in. If
                 * it is, get out of here
                 */
                if ( ! $UserObj)
                {
                    if (
                        $this->getLoggedInUserRole() == Role::WAYPOINT_ROOT_ROLE &&
                        $this->getLoggedInUserObj()->hasRole($this->getLoggedInUserRole())
                    )
                    {
                        return true;
                    }
                    elseif (
                        $this->getLoggedInUserRole() == Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE &&
                        ! $this->getLoggedInUserObj()->hasRole(Role::WAYPOINT_ROOT_ROLE) &&
                        $this->getLoggedInUserObj()->hasRole($this->getLoggedInUserRole())
                    )
                    {
                        return true;
                    }
                    elseif (
                        $this->getLoggedInUserRole() == Role::WAYPOINT_ASSOCIATE_ROLE &&
                        ! $this->getLoggedInUserObj()->hasRole(Role::WAYPOINT_ROOT_ROLE) &&
                        ! $this->getLoggedInUserObj()->hasRole(Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE) &&
                        $this->getLoggedInUserObj()->hasRole($this->getLoggedInUserRole())
                    )
                    {
                        return true;
                    }
                    elseif (
                        $this->getLoggedInUserRole() == Role::CLIENT_ADMINISTRATIVE_USER_ROLE &&
                        ! $this->getLoggedInUserObj()->hasRole(Role::WAYPOINT_ROOT_ROLE) &&
                        ! $this->getLoggedInUserObj()->hasRole(Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE) &&
                        ! $this->getLoggedInUserObj()->hasRole(Role::WAYPOINT_ASSOCIATE_ROLE) &&
                        $this->getLoggedInUserObj()->hasRole($this->getLoggedInUserRole())
                    )
                    {
                        return true;
                    }
                    elseif (
                        $this->getLoggedInUserRole() == Role::CLIENT_GENERIC_USER_ROLE &&
                        ! $this->getLoggedInUserObj()->hasRole(Role::WAYPOINT_ROOT_ROLE) &&
                        ! $this->getLoggedInUserObj()->hasRole(Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE) &&
                        ! $this->getLoggedInUserObj()->hasRole(Role::WAYPOINT_ASSOCIATE_ROLE) &&
                        ! $this->getLoggedInUserObj()->hasRole(Role::CLIENT_ADMINISTRATIVE_USER_ROLE) &&
                        $this->getLoggedInUserObj()->hasRole($this->getLoggedInUserRole())
                    )
                    {
                        return true;
                    }
                }

                /**
                 * at this point we know currentLoggedIn is to be logged out as
                 * he/she have the wrong Role
                 */
                $this->LogOutUser();
            }

            /**
             * at this point we know that we need to log somebody in
             */
            if ( ! $UserObj)
            {
                if ($this->logged_in_user_role == Role::WAYPOINT_ROOT_ROLE)
                {
                    $UserObj = $this->UserRepositoryObj->findWhere(
                        ['client_id' => 1, 'email' => User::SUPERUSER_EMAIL]
                    )->first();
                }
                elseif ($this->logged_in_user_role == Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE)
                {
                    $UserObj = $this->UserRepositoryObj->findWhere(
                        ['client_id' => $this->ClientObj->id, 'lastname' => 'Africa Waypoint System Admin User']
                    )->first();
                }
                elseif ($this->logged_in_user_role == Role::WAYPOINT_ASSOCIATE_ROLE)
                {
                    $UserObj = $this->UserRepositoryObj->findWhere(
                        ['client_id' => $this->ClientObj->id, 'lastname' => 'Happy Waypoint Asso User']
                    )->first();
                }
                elseif ($this->logged_in_user_role == Role::CLIENT_ADMINISTRATIVE_USER_ROLE)
                {
                    $UserObj = $this->UserRepositoryObj->findWhere(
                        ['client_id' => $this->ClientObj->id, 'lastname' => 'Chastity Client Admin User']
                    )->first();
                }
                elseif ($this->logged_in_user_role == Role::CLIENT_GENERIC_USER_ROLE)
                {
                    $UserObj = $this->UserRepositoryObj->findWhere(
                        ['client_id' => $this->ClientObj->id, 'lastname' => 'Lust Client Generic User']
                    )->first();
                }
                else
                {
                    throw new GeneralException('Unable to log in user ' . __METHOD__ . ':' . __LINE__);
                }
            }

            if ( ! $UserObj)
            {
                throw new GeneralException('Unable to log in user ' . __METHOD__ . ':' . __LINE__);
            }

            Auth::login($UserObj);

            if ( ! $this->UserRepositoryObj->getLoggedInUser())
            {
                throw new GeneralException('User not logged in');
            }
            $this->setLoggedInUserId($UserObj->id);
            $this->assertEquals($UserObj->id, $this->UserRepositoryObj->getLoggedInUser()->id);
            $this->assertTrue(
                User::class == get_class($this->UserRepositoryObj->getLoggedInUser()) ||
                is_subclass_of($this->UserRepositoryObj->getLoggedInUser(), User::class)
            );

            $this->assertTrue($this->UserRepositoryObj->getLoggedInUser()->hasRole($this->getLoggedInUserRole()));
            return true;
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException($e->getMessage(), 400, $e);
        }
    }

    /**
     * @throws GeneralException
     */
    public function LogOutUser()
    {
        try
        {
            Auth::logout();
            $this->setLoggedInUserId(null);
            Cache::flush();
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException($e->getMessage(), 400, $e);
        }
    }

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../../../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * @param $hidden_list_arr
     * @param $list
     * @return mixed
     */
    public static function stripHidden($hidden_list_arr, $list)
    {
        foreach ($hidden_list_arr as $hidden)
        {
            if (isset($list[$hidden]))
            {
                unset($list[$hidden]);
            }
        }

        foreach ($list as $index => $value)
        {
            if (is_array($value))
            {
                unset($list[$index]);
            }
        }
        return $list;
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        if ( ! config('waypoint.commit_to_database_on_unittests', false))
        {
            DB::rollBack();
            Cache::tags('Client_' . $this->ClientObj->id)->flush();
            Cache::tags('Property_' . $this->ClientObj->id)->flush();
            Cache::tags('PropertyGroup_' . $this->ClientObj->id)->flush();
            Cache::tags('AccessList_' . $this->ClientObj->id)->flush();
            Cache::tags('AdvancedVariance_' . $this->ClientObj->id)->flush();
            Cache::tags('BenchmarkGenerationDate_' . $this->ClientObj->id)->flush();
            Cache::tags('ProcessRefreshNativeAccountValues_' . $this->ClientObj->id)->flush();
        }
        $this->unsetAllRepositories();
        parent::tearDown();
        ApiGuardAuth::logout();

        /**
         * Tear it down, tear it all down
         */
        $refl = new ReflectionObject($this);
        foreach ($refl->getProperties() as $prop)
        {
            if ( ! $prop->isStatic() && 0 !== strpos($prop->getDeclaringClass()->getName(), 'PHPUnit_'))
            {
                $prop->setAccessible(true);
                $prop->setValue($this, null);
            }
        }
    }

    /**
     * @param $fq_class_name
     * @return mixed
     */
    public function getClassName($fq_class_name)
    {
        $path = explode('\\', $fq_class_name);
        return array_pop($path);
    }

    /**
     * @param $string
     * @return bool
     */
    function is_json_string($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * @return mixed
     * @throws GeneralException
     * @throws \Exception
     */
    public static function getUnitTestClient($refresh = false)
    {
        if (App::environment() === 'production')
        {
            throw new Exception('What, you crazy!!!!! No Seeders in production context ' . __FILE__);
        }
        if ( ! $refresh && TestCase::$StaticClientObj)
        {
            return TestCase::$StaticClientObj;
        }
        $unit_test_name_pattern = 'SEEDED - ' . Seeder::$unit_test_client_name;

        $ClientObj =
            Client::where('name', 'LIKE', '%' . $unit_test_name_pattern . '%')
                  ->with('properties')
                  ->with('users')
                  ->get()
                  ->first();
        if ( ! $ClientObj)
        {
            throw new GeneralException('No client named ' . $unit_test_name_pattern . '* found. Run composer update and migration');
        }
        TestCase::$StaticClientObj = $ClientObj;

        return TestCase::$StaticClientObj;
    }

    /**
     * @param App\Waypoint\Model|User|App\Waypoint\Models\ApiKey|App\Waypoint\Models\ApiLog|App\Waypoint\Models\Role|App\Waypoint\Models\Permission $ObjectInQuestionObj
     * @param App\Waypoint\Model|User|ApiKey|ApiLog|Role|Permission $ObjectInQuestionObj
     * @return bool
     */
    public static function implements_oddball_model($ObjectInQuestionObj)
    {
        return
            $ObjectInQuestionObj instanceof User ||
            $ObjectInQuestionObj instanceof Role ||
            $ObjectInQuestionObj instanceof Permission ||
            $ObjectInQuestionObj instanceof ApiKey ||
            $ObjectInQuestionObj instanceof ApiLog;
    }

    /**
     * @param $bridge_response_string
     * @return array
     */
    public static function parse_bridge_response_into_array($bridge_response_string)
    {
        return array_map(
            function ($m)
            {
                return array_map(
                    function ($n)
                    {
                        return trim(preg_replace("/\R/", '', trim($n, '"')), '"');
                    },
                    explode(',', $m)
                );
            }, explode(PHP_EOL, $bridge_response_string)
        );
    }

    /**
     * @param $url_in_question
     * @return bool
     */
    public static function is_syntactially_valid_url($url_in_question)
    {
        if (filter_var($url_in_question, FILTER_VALIDATE_URL) === false)
        {
            return false;
        }
        return true;
    }

    /**
     * see http://stackoverflow.com/questions/1755144/how-to-validate-domain-name-in-php
     * @param $domain_name
     * @return bool
     */
    public static function is_valid_domain_name($domain_name)
    {
        return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) //valid chars check
                && preg_match("/^.{1,253}$/", $domain_name) //overall length check
                && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name)); //length of each label
    }

    /**
     * @return string
     */
    public static function getUnitTestEmailDomain(): string
    {
        if (self::$unit_test_email_domain)
        {
            return self::$unit_test_email_domain;
        }
        return config('waypoint.unit_test_email_domain', false);
    }

    /**
     * @param string $unit_test_email_domain
     */
    public static function setUnitTestEmailDomain(string $unit_test_email_domain)
    {
        self::$unit_test_email_domain = $unit_test_email_domain;
    }

    /**
     * @return string
     */
    public static function getFakeEmailAddress()
    {
        $fake_email = 'SEEDED' . mt_rand() . Seeder::getFakerObj()->word . '@' . self::getUnitTestEmailDomain();
        return $fake_email;
    }

    /**
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function turn_on_fake_notifications()
    {
        Notification::fake();
        $this->assertTrue(
            (boolean) config('waypoint.enable_notifications_emails', false),
            'No point in running test if waypoint.enable_notifications_emails is false. Look in phpunit.*.xml'
        );
        $this->assertEquals(
            'sync',
            config(
                'queue.driver', false
            ),
            'No point in running test if queue.drivers is not \'sync\'. Look in phpunit.*.xml'
        );
    }

    /**
     * @param $string_to_write_to_std_out
     */
    protected function write($string_to_write_to_std_out)
    {
        fwrite(STDERR, print_r($string_to_write_to_std_out, true));
    }

    /**
     * @return Collection
     */
    protected function getAllClientsExceptDummy()
    {
        return $this->ClientRepositoryObj
            ->all()
            ->filter(
                function (Client $Client)
                {
                    return ! str_contains($Client->name, 'Dummy');
                }
            );
    }

    /**
     * @return string
     */
    public static function getUnitTestClientName(): string
    {
        return 'SEEDED - ' . self::$unit_test_client_name . ' ' . Carbon::now()->format('F j, Y, g:i:u a');
    }

    public function populateVariousUnittestObjects()
    {
        $this->populateUsers();
        $this->populateProperties();
        $this->populateAccessLists();
    }

    public function populateUsers()
    {
        $this->FirstGenericUserObj   = $this->UserRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'lastname'  => 'Lust Client Generic User',
            ]
        )->first();
        $this->SecondGenericUserObj  = $this->UserRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'lastname'  => 'Gluttony Client Generic User',
            ]
        )->first();
        $this->ThirdGenericUserObj   = $this->UserRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'lastname'  => 'Greed Client Generic User',
            ]
        )->first();
        $this->FourthGenericUserObj  = $this->UserRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'lastname'  => 'Sloth Client Generic User',
            ]
        )->first();
        $this->FifthGenericUserObj   = $this->UserRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'lastname'  => 'Wrath Client Generic User',
            ]
        )->first();
        $this->SixthGenericUserObj   = $this->UserRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'lastname'  => 'Envy Client Generic User',
            ]
        )->first();
        $this->SeventhGenericUserObj = $this->UserRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'lastname'  => 'Pride Client Generic User',
            ]
        )->first();

        /**
         * 'Chastity', 'Temperance', 'Charity', 'Diligence', 'Patience', 'Kindness', 'Humility'
         * */
        $this->FirstAdminUserObj   = $this->UserRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'lastname'  => 'Chastity Client Admin User',
            ]
        )->first();
        $this->SecondAdminUserObj  = $this->UserRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'lastname'  => 'Temperance Client Admin User',
            ]
        )->first();
        $this->ThirdAdminUserObj   = $this->UserRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'lastname'  => 'Charity Client Admin User',
            ]
        )->first();
        $this->FourthAdminUserObj  = $this->UserRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'lastname'  => 'Diligence Client Admin User',
            ]
        )->first();
        $this->FifthAdminUserObj   = $this->UserRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'lastname'  => 'Patience Client Admin User',
            ]
        )->first();
        $this->SixthAdminUserObj   = $this->UserRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'lastname'  => 'Kindness Client Admin User',
            ]
        )->first();
        $this->SeventhAdminUserObj = $this->UserRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'lastname'  => 'Humility Client Admin User',
            ]
        )->first();

        $this->assertNotNull($this->FirstGenericUserObj);
        $this->assertNotNull($this->SecondGenericUserObj);
        $this->assertNotNull($this->ThirdGenericUserObj);
        $this->assertNotNull($this->FourthGenericUserObj);
        $this->assertNotNull($this->FifthGenericUserObj);
        $this->assertNotNull($this->SixthGenericUserObj);
        $this->assertNotNull($this->SeventhGenericUserObj);

        $this->assertNotNull($this->FirstAdminUserObj);
        $this->assertNotNull($this->SecondAdminUserObj);
        $this->assertNotNull($this->ThirdAdminUserObj);
        $this->assertNotNull($this->FourthAdminUserObj);
        $this->assertNotNull($this->FifthAdminUserObj);
        $this->assertNotNull($this->SixthAdminUserObj);
        $this->assertNotNull($this->SeventhAdminUserObj);
    }

    public function populateProperties()
    {
        $this->FirstPropertyObj   = $this->PropertyRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'name'      => 'The Great Pyramid of Giza',
            ]
        )->first();
        $this->SecondPropertyObj  = $this->PropertyRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'name'      => 'The Hanging Gardens of Babylon',
            ]
        )->first();
        $this->ThirdPropertyObj   = $this->PropertyRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'name'      => 'The Colossus of Rhodes',
            ]
        )->first();
        $this->FourthPropertyObj  = $this->PropertyRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'name'      => 'The Lighthouse of Alexandria',
            ]
        )->first();
        $this->FifthPropertyObj   = $this->PropertyRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'name'      => 'The Mausoleum at Halicarnassus',
            ]
        )->first();
        $this->SixthPropertyObj   = $this->PropertyRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'name'      => 'The Temple of Artemis',
            ]
        )->first();
        $this->SeventhPropertyObj = $this->PropertyRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'name'      => 'The Statue of Zeus',
            ]
        )->first();

        $this->assertNotNull($this->FirstPropertyObj);
        $this->assertNotNull($this->SecondPropertyObj);
        $this->assertNotNull($this->ThirdPropertyObj);
        $this->assertNotNull($this->FourthPropertyObj);
        $this->assertNotNull($this->FifthPropertyObj);
        $this->assertNotNull($this->SixthPropertyObj);
        $this->assertNotNull($this->SeventhPropertyObj);
    }

    public function populateAccessLists()
    {
        $this->FirstAccessListObj   = $this->AccessListRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'name'      => 'Monday AccessList',
            ]
        )->first();
        $this->SecondAccessListObj  = $this->AccessListRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'name'      => 'Tuesday AccessList',
            ]
        )->first();
        $this->ThirdAccessListObj   = $this->AccessListRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'name'      => 'Wednesday AccessList',
            ]
        )->first();
        $this->FourthAccessListObj  = $this->AccessListRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'name'      => 'Thursday AccessList',
            ]
        )->first();
        $this->FifthAccessListObj   = $this->AccessListRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'name'      => 'Friday AccessList',
            ]
        )->first();
        $this->SixthAccessListObj   = $this->AccessListRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'name'      => 'Saturday AccessList',
            ]
        )->first();
        $this->SeventhAccessListObj = $this->AccessListRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'name'      => 'Sunday AccessList',
            ]
        )->first();

        $this->assertNotNull($this->FirstAccessListObj);
        $this->assertNotNull($this->SecondAccessListObj);
        $this->assertNotNull($this->ThirdAccessListObj);
        $this->assertNotNull($this->FourthAccessListObj);
        $this->assertNotNull($this->FifthAccessListObj);
        $this->assertNotNull($this->SixthAccessListObj);
        $this->assertNotNull($this->SeventhAccessListObj);
    }
}