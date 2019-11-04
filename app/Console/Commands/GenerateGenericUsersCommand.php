<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Events\CalculateVariousPropertyListsEvent;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\ApiKey;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\User;
use App\Waypoint\Tests\TestCase;
use Carbon\Carbon;
use DB;
use Exception;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

/**
 * Class GenerateGenericUsersCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class GenerateGenericUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:generate_generic_users
                        {--client_ids=All : Comma separated list client IDs or \'All\' Default All}';

    /**
     * GenerateGenericUsersCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
        /**
         * NOTE you cannot populate $this->ClientRepositoryObj in parent::__construct. Messses with code generator
         */
    }

    /**
     * Execute the console command.
     *
     * @todo push this logic into a repository
     */
    public function handle()
    {
        if (App::environment() === 'production')
        {
            throw new GeneralException('What!! Are you crazy!!. You can\'t run generate_generic_users in production context', 403);
        }
        parent::handle();
        $this->loadAllRepositories(true);

        /**
         * we want to roll back this particular command
         */
        DB::beginTransaction();
        try
        {
            $this->generate_generic_users($this->getClientsFromArray($this->option('client_ids')));

            DB::commit();
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException($e->getMessage(), 500, $e);
        }
        catch (Throwable $e)
        {
            $e = new FatalThrowableError($e);
            throw new GeneralException($e->getMessage(), 500, $e);
        }

        return true;
    }

    /**
     * @param $ClientObjArr
     * @return string
     * @throws App\Waypoint\Exceptions\ValidationException
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function generate_generic_users($ClientObjArr)
    {
        $saved_user_ids           = [];
        $saved_property_group_ids = [];
        foreach ($ClientObjArr as $ClientObj)
        {
            if ($ClientObj->name == Client::DUMMY_CLIENT_NAME)
            {
                continue;
            }
            $testEmailsArr = self::testEmailsArr($ClientObj->id);

            foreach ($testEmailsArr as $candidate_user)
            {
                /**
                 * User exists and is active but let's make sure his display_email is right
                 * and that he/she has API key
                 * and that he/she has correct roles
                 * @var User $UserObj
                 */

                if ($UserObj = $this->UserRepositoryObj->getActiveUserWithClientIdAndEmail(
                    $ClientObj->id,
                    $candidate_user['email']
                )
                )
                {
                    if ( ! $UserObj->display_email)
                    {
                        $UserObj->display_email = $UserObj->email;
                        $UserObj->save();
                    }
                    $this->alert('User ' . $candidate_user['email'] . ' already activly exists in client ' . $ClientObj->name);

                    if ( ! $UserObj->apiKey)
                    {
                        $this->alert('Creating ApiKey ' . $candidate_user['email'] . ' in client ' . $ClientObj->name);
                        ApiKey::make($UserObj->id);
                    }
                }

                if ($UserObj = $this->UserRepositoryObj->getUserWithClientIdAndEmail($ClientObj->id, $candidate_user['email']))
                {
                    $this->alert('Updating User ' . $candidate_user['email'] . ' to ACTIVE in client ' . $ClientObj->name);
                    /** @var User $UserAdminObj */
                    $UserObj = $this->UserRepositoryObj->update(
                        [
                            'active_status' => User::ACTIVE_STATUS_ACTIVE,
                        ],
                        $UserObj->id
                    );
                }
                else
                {
                    $this->alert('Creating ACTIVE User ' . $candidate_user['email'] . ' in client ' . $ClientObj->name);
                    $UserObj = $this->UserRepositoryObj->create(
                        [
                            'firstname'                   => $candidate_user['email'],
                            'lastname'                    => $candidate_user['email'],
                            'email'                       => $candidate_user['email'],
                            'display_email'               => $candidate_user['email'],
                            'user_name'                   => $candidate_user['email'],
                            'active_status'               => User::ACTIVE_STATUS_ACTIVE,
                            'user_invitation_status'      => User::USER_INVITATION_STATUS_ACCEPTED,
                            'user_invitation_status_date' => Carbon::now()->format('Y-m-d H:i:s'),
                            'client_id'                   => $ClientObj->id,
                            'role'                        => $candidate_user['role'],
                        ]
                    );
                }

                $this->alert('Confirm User ' . $candidate_user['email'] . ' has role ' . $candidate_user['role']);
                $UserObj->attachRole(Role::where('name', $candidate_user['role'])->first());

                $this->alert('Confirm User ' . $candidate_user['email'] . ' is correctly hidden or not');
                $is_hidden = false;
                if (
                    $candidate_user['role'] == Role::WAYPOINT_ROOT_ROLE ||
                    $candidate_user['role'] == Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE ||
                    $candidate_user['role'] == Role::WAYPOINT_ASSOCIATE_ROLE
                )
                {
                    $is_hidden = true;
                }
                $UserObj = $this->UserRepositoryObj->update(
                    [
                        'is_hidden' => $is_hidden,
                    ],
                    $UserObj->id
                );

                try
                {
                    $this->UserRepositoryObj->updatePassword($UserObj->id, config('waypoint.password_change_token_secret_word'));
                }
                catch (GeneralException $e)
                {
                    /**
                     * when generation generic users, we often create the same user over and over. However we
                     * also want generic users to have same password, which violates Auth0 rules. This deals with that.
                     *
                     * Nothing to see here folks.....
                     */
                    if ( ! preg_match("/updating user password failed/", $e->getMessage()))
                    {
                        throw $e;
                    }
                }
                catch (Exception $e)
                {
                    throw new GeneralException($e->getMessage(), 500, $e);
                }
                catch (Throwable $e)
                {
                    $e = new FatalThrowableError($e);
                    throw new GeneralException($e->getMessage(), 500, $e);
                }

                if ( ! $UserObj->apiKey)
                {
                    $this->alert('Creating ApiKey ' . $candidate_user['email'] . ' in client ' . $ClientObj->name);
                    ApiKey::make($UserObj->id);
                }

                $saved_user_ids[]         = $UserObj->id;
                $saved_property_group_ids = array_merge($saved_property_group_ids, $UserObj->propertyGroups->pluck('id')->toArray());
                /**
                 * this script sometime fails by overloading the throttles that Auth0 has. Let's sleep a bit
                 */
                sleep(1);
            }
        }
        /**
         * running this in-line avoids nasty race condition
         */
        $this->CalculateVariousPropertyListsRepositoryObj = App::make(App\Waypoint\Repositories\CalculateVariousPropertyListsRepository::class)->setSuppressEvents(true);
        $this->CalculateVariousPropertyListsRepositoryObj->CalculateVariousPropertyListsJobProcessor($ClientObj->id);

        event(
            new App\Waypoint\Events\PreCalcUsersEvent(
                $ClientObj,
                [
                    'event_trigger_message'        => 'Triggered at ' . __CLASS__ . ':' . __LINE__,
                    'event_trigger_id'             => waypoint_generate_uuid(),
                    'event_trigger_class'          => self::class,
                    'event_trigger_class_instance' => get_class($this),
                    'event_trigger_object_class'   => get_class($ClientObj),
                    'event_trigger_absolute_class' => __CLASS__,
                    'event_trigger_file'           => __FILE__,
                    'event_trigger_line'           => __LINE__,
                    'wipe_out_list'                =>
                        [
                            'users' => ['skip-soiling'],
                        ],
                    'launch_job_user_id_arr'       => $saved_user_ids,
                ]
            )
        );
        event(
            new App\Waypoint\Events\PreCalcPropertyGroupsEvent(
                $ClientObj,
                [
                    'event_trigger_message'            => 'Triggered at ' . __CLASS__ . ':' . __LINE__,
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($ClientObj),
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                    'wipe_out_list'                    =>
                        [
                            'users' => ['skip-soiling'],
                        ],
                    'launch_job_property_group_id_arr' => $saved_property_group_ids,
                ]
            )
        );
        return true;
    }

    /**
     * @param integer $client_id
     * @return array
     */
    static function testEmailsArr($client_id)
    {
        return [
            ['email' => 'ClientAdmin.' . $client_id . '@' . TestCase::getUnitTestEmailDomain(), 'role' => Role::CLIENT_ADMINISTRATIVE_USER_ROLE],
            ['email' => 'ClientGeneric.' . $client_id . '@' . TestCase::getUnitTestEmailDomain(), 'role' => Role::CLIENT_GENERIC_USER_ROLE],
            ['email' => 'WaypointAsso.' . $client_id . '@' . TestCase::getUnitTestEmailDomain(), 'role' => Role::WAYPOINT_ASSOCIATE_ROLE],
            ['email' => 'WaypointSystemAdmin.' . $client_id . '@' . TestCase::getUnitTestEmailDomain(), 'role' => Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE],

            ['email' => 'MultiClientAdmin@' . TestCase::getUnitTestEmailDomain(), 'role' => Role::CLIENT_ADMINISTRATIVE_USER_ROLE],
            ['email' => 'MultiClientGeneric@' . TestCase::getUnitTestEmailDomain(), 'role' => Role::CLIENT_GENERIC_USER_ROLE],
            ['email' => 'MultiWaypointAsso@' . TestCase::getUnitTestEmailDomain(), 'role' => Role::WAYPOINT_ASSOCIATE_ROLE],
            ['email' => 'MultiWaypointSystemAdmin@' . TestCase::getUnitTestEmailDomain(), 'role' => Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE],
        ];
    }
}