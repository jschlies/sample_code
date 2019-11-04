<?php

namespace App\Waypoint\Repositories\Ledger;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Model;
use App\Waypoint\Repositories\ClientRepository;
use App\Waypoint\Models\Ledger\Ledger;
use App\Waypoint\Repositories\DatabaseConnectionRepository;
use Cache;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

/**
 * Class BenchmarkGenerationDateRepository
 */
class BenchmarkGenerationDateRepository extends LedgerRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [

    ];

    /**
     * @return mixed
     **/
    public function model()
    {
        return Ledger::class;
    }

    /**
     * @param integer $client_id
     * @return string
     * @throws GeneralException
     */
    public function getBenchmarkGenerationDate($client_id)
    {
        if ( ! config('waypoint.waypoint_ledger_mysql_connection_available', true))
        {
            return false;
        }

        $minutes   = config('cache.cache_on', false)
            ? config('cache.cache_tags.BenchmarkGenerationDate.ttl', Model::CACHE_TAG_DEFAULT_TTL) / 60
            :
            0;
        $key = 'BenchmarkGenerationDate_' . $client_id.'_'.md5(__FILE__.__LINE__);
        $return_me =
            Cache::tags([
                            $key,
                            'Non-Session',
                        ])
                 ->remember(
                     'BenchmarkGenerationDate_client_id_' . $client_id,
                     $minutes,
                     function () use ($client_id)
                     {
                         /** @var ClientRepository $ClientRepositoryObj */
                         if ( ! $ClientObj = App::make(ClientRepository::class)->find($client_id))
                         {
                             throw new ModelNotFoundException('no such client - client_id = ' . $client_id);
                         }
                         if ( ! $ClientObj->client_id_old)
                         {
                             return false;
                         }

                         try
                         {
                             $DBConnectionObj = DB::connection('mysql_WAYPOINT_LEDGER_' . $ClientObj->client_id_old);

                             $schema            = config('database.connections.mysql_WAYPOINT_LEDGER_' . $ClientObj->client_id_old . '.database');
                             $target_asof_month = 'TARGET_ASOF_MONTH';

                             if ( ! DatabaseConnectionRepository::tablesExist($ClientObj, $schema, [$target_asof_month]))
                             {
                                 return Carbon::createFromTimestamp(0)->toDateTimeString();
                             }

                             $results = $DBConnectionObj
                                 ->table($target_asof_month)
                                 ->select(
                                     "BENCHMARK_GENERATION_DATE"
                                 )->get();
                             return $results->first()->BENCHMARK_GENERATION_DATE;
                         }
                         catch (Exception $e)
                         {
                             /**
                              * no matter what, onward
                              */
                             Log::error('Unable to read TARGET_ASOF_MONTH in DB mysql_WAYPOINT_LEDGER_' . $ClientObj->client_id_old);
                             /**
                              * notice that we do not include seconds in time stamp.
                              * Thus if this query fails, we'll provide some kind of date.
                              */
                             return Carbon::today()->format('Y-m-d');
                         }

                     }
                 );

        return $return_me;

    }

    /**
     * @param integer $client_id
     * @return bool
     * @throws GeneralException
     */
    public function getBenchmarkPeerCalcTimeStamp($client_id)
    {
        if ( ! config('waypoint.waypoint_ledger_mysql_connection_available', true))
        {
            return false;
        }
        /** @var ClientRepository $ClientRepositoryObj */
        if ( ! $ClientObj = App::make(ClientRepository::class)->find($client_id))
        {
            throw new GeneralException('no such client - client_id = ' . $client_id);
        }
        if ( ! $ClientObj->client_id_old)
        {
            return false;
        }
        $schema   = "waypoint_peer_" . $ClientObj->client_id_old;
        $tables   = [];
        $tables[] = "PEER_GROUP_CALC_CLIENT_" . $ClientObj->client_id_old . "_YEARLY_STATUS";

        if ( ! DatabaseConnectionRepository::tablesExist($ClientObj, $schema, $tables))
        {
            return Carbon::createFromTimestamp(0)->toDateTimeString();
        }

        //-- IF there is only 1 unique LINUX system time under the completed status in the
        // peer status table, returns that time stamp
        //-- IF there are more than one LINUX system times under the
        // completed status in the peer status table,
        // returns that smallest time stamp (in case there is a peer calc ongoing)
        $DBConnectionObj = DB::connection('mysql_WAYPOINT_LEDGER_' . $ClientObj->client_id_old);
        $sql             = "
            SELECT 
                CASE WHEN 		
                    (SELECT COUNT(1) FROM 
                        (SELECT LINUX_SYSTEM_TIME 
                            FROM waypoint_peer_" . $ClientObj->client_id_old . ".PEER_GROUP_CALC_CLIENT_" . $ClientObj->client_id_old . "_YEARLY_STATUS 
                            WHERE STATUS=\"3\" GROUP BY LINUX_SYSTEM_TIME) a 
                        ) = \"1\"
                    THEN (
                        SELECT LINUX_SYSTEM_TIME 
                            FROM waypoint_peer_" . $ClientObj->client_id_old . ".PEER_GROUP_CALC_CLIENT_" . $ClientObj->client_id_old . "_YEARLY_STATUS 
                            WHERE STATUS=\"3\" GROUP BY LINUX_SYSTEM_TIME) 
                    ELSE (
                        SELECT MIN(LINUX_SYSTEM_TIME) 
                            FROM waypoint_peer_" . $ClientObj->client_id_old . ".PEER_GROUP_CALC_CLIENT_" . $ClientObj->client_id_old . "_YEARLY_STATUS 
                            WHERE STATUS=\"3\") 
                END PEER_CALC_TIME_STAMP;
         ";
        $results         = $DBConnectionObj->select(
            DB::raw(
                $sql
            ),
            []
        );

        return Carbon::createFromTimestamp($results[0]->PEER_CALC_TIME_STAMP)->toDateTimeString();
    }
}
