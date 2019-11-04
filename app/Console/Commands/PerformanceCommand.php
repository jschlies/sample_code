<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Events\CalculateVariousPropertyListsEvent;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\ClientRepository;
use App\Waypoint\Repositories\PropertyGroupRepository;
use App\Waypoint\Repositories\UserAdminRepository;
use App\Waypoint\Models\UserAdmin;
use App\Waypoint\Repositories\UserRepository;
use App\Waypoint\Tests\ApiTestTrait;
use Performance\Config;
use Performance\Performance;

/**
 * Class PerformanceCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class PerformanceCommand extends Command
{
    use ApiTestTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:performance  ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'performance';

    /**
     * AddUsersCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
        /**
         * NOTE you cannot populate $this->ClientRepositoryObj in parent::__construct. Messes up code generator
         */
    }

    /**
     * Execute the console command.
     *
     * @todo push this logic into a repository
     */
    public function handle()
    {
        parent::handle();
        Config::setConsoleLive(true);

        $user = User::find(1326);
        /*
         * One simply performance check
         */
        Performance::point('Retrieving accessible properties');


        $user->getAccessiblePropertyObjArr();

        // Finish all tasks and show test results
        Performance::results();
    }

    /**
     * @param integer $client_id
     * @param string $email
     * @param string $role
     * @param bool $add_to_client_all_access_list
     * @param string $firstname
     * @param string $lastname
     * @param bool $is_hidden
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function add_user($client_id, $email, $role, $add_to_client_all_access_list, $firstname, $lastname, $is_hidden = false)
    {
        $this->ClientRepositoryObj = App::make(ClientRepository::class);
        $this->UserRepositoryObj   = App::make(UserRepository::class);
        if ( ! $ClientObj = $this->ClientRepositoryObj->find($client_id))
        {
            throw new GeneralException("no client found", 500);
        }

        if ($ClientObj->name == Client::DUMMY_CLIENT_NAME)
        {
            throw new GeneralException("invalid client found", 500);
        }

        /**
         * @var UserAdminRepository $UserRepositoryObj
         *
         * remember that UserAdminRepository is special and okly way to update
         * is_hidden so we do not make a property of $this
         */
        $UserAdminRepositoryObj = App::make(UserAdminRepository::class);

        /** @var UserAdmin $UserAdminObj */
        if ($UserAdminObj = $this->UserRepositoryObj->getActiveUserWithClientIdAndEmail($ClientObj->id, $email))
        {
            $this->alert('Updating User ' . $email . ' to ACTIVE in client ' . $ClientObj->name);
            /** @var User $UserAdminObj */
            $UserAdminObj = $UserAdminRepositoryObj->update(
                [
                    'firstname'     => $firstname,
                    'lastname'      => $lastname,
                    'email'         => $email,
                    'display_email' => $email,
                    'user_name'     => $email,
                    'active_status' => User::ACTIVE_STATUS_ACTIVE,
                    'client_id'     => $ClientObj->id,
                    'is_hidden'     => $is_hidden,
                ],
                $UserAdminObj->id
            );
        }
        else
        {
            $this->alert('Creating User ' . $email . ' in client ' . $ClientObj->name);
            /** @var User $UserAdminObj */
            $UserAdminObj = $UserAdminRepositoryObj->create(
                [
                    'firstname'     => $firstname,
                    'lastname'      => $lastname,
                    'email'         => $email,
                    'display_email' => $email,
                    'user_name'     => $email,
                    'active_status' => User::ACTIVE_STATUS_ACTIVE,
                    'client_id'     => $ClientObj->id,
                    'is_hidden'     => $is_hidden,
                ]
            );
        }

        if ( ! $UserAdminObj->hasRole($role))
        {
            $UserAdminObj->attachRole(Role::where('name', $role)->first());
        }

        if ($add_to_client_all_access_list)
        {
            if ( ! $this->AccessListUserRepositoryObj->findWhere(
                [
                    'user_id'        => $UserAdminObj->id,
                    'access_list_id' => $ClientObj->getAllAccessListObj()->id,
                ]
            )->first)
            {
                $this->AccessListUserRepositoryObj->create(
                    [
                        'user_id'        => $UserAdminObj->id,
                        'access_list_id' => $ClientObj->getAllAccessListObj()->id,
                    ]
                );
                $this->alert('Added ' . $UserAdminObj->firstname . ' ' . $UserAdminObj->lastname . ' to ' . $ClientObj->getAllAccessListObj()->name);
            }
        }

        $this->alert('Refreshing various access lists and property groups for ' . $ClientObj->name);

        event(
            new CalculateVariousPropertyListsEvent(
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
                ]
            )
        );
    }
}