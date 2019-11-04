<?php

namespace App\Waypoint\Console\Commands;

use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;
use Carbon\Carbon;
use App;
use App\Waypoint\Models\Client;
use App\Waypoint\Auditors\WaypointAuditor;
use OwenIt\Auditing\Auditable;

/**
 * Class AddUsersCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class PropertyGroupCalcHistoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:property_group_calc_history  
                        {--client_id= : client_id} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'property_group_calc_history for a client';

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

        if ( ! $client_id = $this->option('client_id'))
        {
            throw new GeneralException("no client_id found", 500);
        }

        /** @var Client $ClientObj */
        $ClientObj = $this->ClientRepositoryObj->find($client_id);
        /** @var WaypointAuditor $WaypointAuditorObj */

        $AuditObjArr     = $ClientObj->audits()->get();
        $out_message_arr = [];
        /** @var Auditable $AuditObj */
        foreach ($AuditObjArr as $AuditObj)
        {
            if ($AuditObj->type == 'created')
            {
                $out_message_arr[$AuditObj->created_at->timestamp] = $ClientObj->name . ' created at ' . $AuditObj->created_at->format(Carbon::DEFAULT_TO_STRING_FORMAT);
                continue;
            }
            if ($AuditObj->type == 'deleted')
            {
                $out_message_arr[$AuditObj->created_at->timestamp] = $ClientObj->name . ' deleted at ' . $AuditObj->created_at->format(Carbon::DEFAULT_TO_STRING_FORMAT);
                continue;
            }
            $out_message = '';
            if (isset($AuditObj->new['property_group_calc_status']))
            {
                $out_message                                       .= $ClientObj->name . ' ' . $AuditObj->created_at->format(Carbon::DEFAULT_TO_STRING_FORMAT);
                $out_message                                       .= ' ' . $AuditObj->old['property_group_calc_status'] . ' >>> ' . $AuditObj->new['property_group_calc_status'];
                $out_message_arr[$AuditObj->created_at->timestamp] = $out_message;
                continue;
            }
        }
        ksort($out_message_arr);
        foreach ($out_message_arr as $out_message)
        {
            $this->alert($out_message);
        }
        return true;
    }
}