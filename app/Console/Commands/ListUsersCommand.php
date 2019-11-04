<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Auth0\Auth0ApiManagementUser;
use App\Waypoint\Command;
use App\Waypoint\Models\User;
use function waypoint_generate_uuid;

/**
 * Class ListUsersCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class ListUsersCommand extends Command
{
    private static $Auth0ManagementUsersObj = null;

    /**
     * @return null
     */
    public static function getAuth0ManagementUsersObj()
    {
        if ( ! self::$Auth0ManagementUsersObj)
        {
            self::$Auth0ManagementUsersObj = new Auth0ApiManagementUser();
        }
        return self::$Auth0ManagementUsersObj;
    }

    /**
     * @param null $Auth0ManagementUsersObj
     */
    public static function setAuth0ManagementUsersObj($Auth0ManagementUsersObj): void
    {
        self::$Auth0ManagementUsersObj = $Auth0ManagementUsersObj;
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:list:users 
                        {--client_ids= : Comma separated list client IDs or \'All\'}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List users information';

    /**
     * ListUsersCommand constructor.
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
     * @throws \Exception
     */
    public function handle()
    {
        parent::handle();

        $return_me = [];
        $filename  = waypoint_generate_uuid();
        foreach ($this->getClientsFromArray($this->option('client_ids')) as $ClientObj)
        {
            /** @var User $UserObj */
            foreach ($ClientObj->users as $UserObj)
            {
                $return_me[$UserObj->email] = [
                    'id'                 => $UserObj->id,
                    'client_id'          => $UserObj->client_id,
                    'client_name'        => $UserObj->client->name,
                    'firstname'          => $UserObj->firstname,
                    'lastname'           => $UserObj->lastname,
                    'email'              => $UserObj->email,
                    'active_status'      => $UserObj->active_status,
                    'active_status_date' => $UserObj->active_status_date,
                    'auth0_usr_id'       => null,
                ];
            }
        }

        $Auth0UserArr = self::getAuth0ManagementUsersObj()->get_all_users();

        foreach ($Auth0UserArr as $Auth0serObj)
        {
            if ( ! isset($return_me[$Auth0serObj->email]))
            {
                $return_me[$Auth0serObj->email] = [
                    'id'                      => null,
                    'firstname'               => null,
                    'lastname'                => null,
                    'email'                   => null,
                    'description'             => null,
                    'active_status'           => null,
                    'active_status_date'      => null,
                    'user_name'               => null,
                    'creation_auth0_response' => null,
                ];
            }
            $return_me[$Auth0serObj->email]['auth0_usr_id'] = $Auth0serObj->user_id;
            $return_me[$Auth0serObj->email]['email']        = $Auth0serObj->email;
        }

        collect_waypoint_spreadsheet($return_me)->toCSVFile($filename);
        $fq_filename = storage_path('exports') . '/' . $filename . '.csv';
        $this->alert('--------------------------------------------------------------------------------------------------------');
        $this->alert('----- Also See ' . $fq_filename . '  ------');
        $this->alert('--------------------------------------------------------------------------------------------------------');
        $this->alert(file_get_contents($fq_filename));
        $this->alert('--------------------------------------------------------------------------------------------------------');
        $this->alert('----- Also See ' . $fq_filename . '  ------');
        $this->alert('--------------------------------------------------------------------------------------------------------');

        return true;
    }
}