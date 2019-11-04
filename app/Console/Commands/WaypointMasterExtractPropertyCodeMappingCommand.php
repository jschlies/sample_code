<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\UserRepository;
use Auth;
use Cache;
use DB;
use Exception;

/**
 * Class AccessListConverterCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 * @codeCoverageIgnore
 */
class WaypointMasterExtractPropertyCodeMappingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:waypoint_master_extractor:property_update_old_data
                        {--suppress_sysout=0 : Suppress sysout - Values are 0 and 1}
                        {--sysout_file=0 : fully qualified or relative path or 0 (not null) - FOLDER MUST EXIST - Example: storage/sysout.txt }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh property_code_mapping data exported from waypoint_master';

    /**
     * Execute the console command.
     *
     * @throws GeneralException
     */
    public function handle()
    {
        try
        {
            if (config('cache.cache_on', false) && config('cache.default', false) == 'memcached')
            {
                Cache::tags(['Non-Session'])->flush();
            }
            else
            {
                Cache::flush();
            }

            $this->UserRepositoryObj = App::make(UserRepository::class);
            Auth::login($this->UserRepositoryObj->getActiveUserWithClientIdAndEmail(1, User::SUPERUSER_EMAIL));

            $DBConnectionObj = DB::connection('mysql_WAYPOINT_MASTER');
            /** @var Property $PropertyObj */
            foreach (Property::all() as $PropertyObj)
            {
                $results = $DBConnectionObj->select(
                    DB::raw(
                        "
                    SELECT *
                    FROM 
                        PROPERTY_CODE_MAPPING, PROPERTY_DETAILS
                    WHERE 
                        PROPERTY_DETAILS.FK_CLIENT_ID = :CLIENT_ID AND 
                        PROPERTY_DETAILS.PROPERTY_CODE  = :PROPERTY_CODE AND 
                        PROPERTY_DETAILS.PROPERTY_CODE = PROPERTY_CODE_MAPPING.PROPERTY_CODE
                  "
                    ),
                    [
                        'CLIENT_ID'     => $PropertyObj->client->client_id_old,
                        'PROPERTY_CODE' => $PropertyObj->property_code,
                    ]
                );
                $results = array_map(
                    function ($val)
                    {
                        return json_decode(json_encode($val), true);
                    }, $results
                );

                $ORIGINAL_PROPERTY_CODE = null;
                $PROPERTY_OWNED         = null;
                $WP_PROPERTY_ID_OLD     = null;
                $MASTER_PROPERTY_ID     = null;
                $PROPERTY_TYPE          = null;
                $LOAD_FACTOR            = 1;

                /**
                 * skip if nothing found
                 */
                if (count($results))
                {
                    foreach ($results as $result_arr)
                    {
                        if ( ! $result_arr['ORIGINAL_PROPERTY_CODE'])
                        {
                            echo 'WaypointMasterExtractPropertyCodeMappingCommand [WARNING] null ORIGINAL_PROPERTY_CODE detected in PROPERTY_CODE_MAPPING record for ' . $PropertyObj->client->name . ' old_client_id ' . $PropertyObj->client->client_id_old . ' PROPERTY_NAME = ' . $PropertyObj->name . ' PROPERTY_CODE = ' . $PropertyObj->property_code . PHP_EOL;
                            continue;
                        }
                        if ($ORIGINAL_PROPERTY_CODE)
                        {
                            $ORIGINAL_PROPERTY_CODE .= ',';
                        }

                        $ORIGINAL_PROPERTY_CODE .= $result_arr['ORIGINAL_PROPERTY_CODE'];
                        $PROPERTY_OWNED         = $result_arr['PROPERTY_OWNED'];
                        $WP_PROPERTY_ID_OLD     = $result_arr['WP_PROPERTY_ID'];
                        /**
                         * Default = 1 per Peter B
                         */
                        $LOAD_FACTOR        = $result_arr['LOAD_FACTOR'] ? $result_arr['LOAD_FACTOR'] : 1;
                        $MASTER_PROPERTY_ID = $result_arr['MASTER_PROPERTY_ID'];
                    }

                    $PropertyObj->original_property_code = $ORIGINAL_PROPERTY_CODE;
                    $PropertyObj->property_owned         = $PROPERTY_OWNED;
                    $PropertyObj->wp_property_id_old     = $WP_PROPERTY_ID_OLD;
                    if ($PropertyObj->property_id_old > 1000000 && $MASTER_PROPERTY_ID < 1000000)
                    {
                        $PropertyObj->property_id_old = $MASTER_PROPERTY_ID;
                    }

                    $PropertyObj->load_factor_old = $LOAD_FACTOR;

                    $PropertyObj->save();
                }
                else
                {
                    echo 'WaypointMasterExtractPropertyCodeMappingCommand [WARNING] PROPERTY_CODE_MAPPING record not found for ' . $PropertyObj->client->name . ' old_client_id ' . $PropertyObj->client->client_id_old . ' PROPERTY_NAME = ' . $PropertyObj->name . ' PROPERTY_CODE = ' . $PropertyObj->property_code . PHP_EOL;
                }
            }
            foreach (Property::whereNull('load_factor_old')->orWhere('load_factor_old', '=', 0) as $PropertyObj)
            {
                $PropertyObj->load_factor_old = 1;
                $PropertyObj->save();
            }
        }
            /** @noinspection PhpRedundantCatchClauseInspection */
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException($e->getMessage(), 500, $e);
        }
    }
}