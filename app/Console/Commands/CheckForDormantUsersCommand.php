<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Events\ClearDormantUsersEvent;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class CheckForDormantUsersCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class CheckForDormantUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:check_for_dormant:users 
                        {--client_ids=All : Comma separated list client IDs or \'All\'}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark dormant users';

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

        $this->check_for_dormant_users($this->option('client_ids'));

        return true;
    }

    /**
     * @param null $client_ids
     * @throws GeneralException
     */
    public function check_for_dormant_users($client_ids = null)
    {
        if ($client_ids == 'All')
        {
            $ClientObjArr = $this->ClientRepositoryObj->all();
        }
        elseif ($client_ids)
        {
            $ClientObjArr = $this->ClientRepositoryObj->findWhereIn('id', explode(',', $client_ids));
        }
        else
        {
            throw new GeneralException('Invalid client_id');
        }

        foreach ($ClientObjArr as $ClientObj)
        {
            if (
                $ClientObj->dormant_user_switch &&
                $ClientObj->dormant_user_ttl &&
                $ClientObj->id !== 1
            )
            {
                event(
                    new ClearDormantUsersEvent(
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
        return;
    }
}