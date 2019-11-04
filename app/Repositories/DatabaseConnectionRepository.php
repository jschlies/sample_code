<?php

namespace App\Waypoint\Repositories;

use App;
use \App\Waypoint\Repository as BaseRepository;
use App\Waypoint\Models\Client;
use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Connection;
use DB;

class DatabaseConnectionRepository extends BaseRepository
{
    /**
     * @param Client $ClientObj
     * @return bool|\Illuminate\Database\Connection
     */
    static function getPeerDatabaseConnection(Client $ClientObj, $tables = false)
    {
        if ( ! $ClientObj)
        {
            throw new GeneralException('missing client object');
        }

        if ( ! self::schemasExist($ClientObj, 'waypoint_peer_' . $ClientObj->client_id_old))
        {
            throw new GeneralException('could not find peer database');
        }

        $PeerDatabaseConnectionObj = DB::connection('mysql_WAYPOINT_PEER_AVERAGE_' . $ClientObj->client_id_old);

        if ($tables)
        {
            self::tablesExist($ClientObj, 'waypoint_peer_' . $ClientObj->client_id_old, $tables);
        }

        return $PeerDatabaseConnectionObj;
    }

    /**
     * @param Client $ClientObj
     * @param bool $tables
     * @return \Illuminate\Database\Connection
     * @throws GeneralException
     */
    static function getGroupDatabaseConnection(Client $ClientObj, $tables = false)
    {
        if ( ! $ClientObj)
        {
            throw new GeneralException('missing client object');
        }

        if ( ! self::schemasExist($ClientObj, 'waypoint_group_' . $ClientObj->client_id_old))
        {
            throw new GeneralException('could not find group database');
        }

        $GroupDatabaseConnectionObj = DB::connection('mysql_GROUPS_FOR_CLIENT_' . $ClientObj->client_id_old);

        if ($tables)
        {
            self::tablesExist($ClientObj, 'waypoint_group_' . $ClientObj->client_id_old, $tables);
        }

        return $GroupDatabaseConnectionObj;
    }

    /**
     * @param Client $ClientObj
     * @param bool $tables
     * @return \Illuminate\Database\Connection
     * @throws GeneralException
     */
    static function getStagingDatabaseConnection(Client $ClientObj, $tables = false)
    {
        if ( ! $ClientObj)
        {
            throw new GeneralException('missing client object');
        }

        $database_name = 'waypoint_staging_' . $ClientObj->client_id_old;
        if ( ! self::schemasExist($ClientObj, $database_name))
        {
            throw new GeneralException('could not find database: ' . $database_name);
        }

        $StagingDatabaseConnectionObj = DB::connection('mysql_WAYPOINT_STAGING_FOR_CLIENT_' . $ClientObj->client_id_old);

        if ($tables)
        {
            self::tablesExist($ClientObj, $database_name, $tables);
        }

        return $StagingDatabaseConnectionObj;
    }

    /**
     * @param Client $ClientObj
     * @param bool $enable_query_log
     * @return \Illuminate\Database\Connection
     */
    static function getLedgerDatabaseConnection(Client $ClientObj, $enable_query_log = false)
    {
        if ( ! $ClientObj)
        {
            throw new GeneralException("no client given");
        }

        $connectionHandle            = 'mysql_WAYPOINT_LEDGER_' . $ClientObj->client_id_old;
        $LedgerDatabaseConnectionObj = DB::connection($connectionHandle);

        /**
         *  TODO (Alex) - remove condition when native chart of accounts analytics feature is released to all clients by default [JD73KEMD7]
         */
        if ($ClientObj->nativeCoaAnalyticsFeatureIsEnabled())
        {
            $LedgerDatabaseConnectionObj = self::switchToReportTemplateBasedLedgerDatabase($LedgerDatabaseConnectionObj, $ClientObj);
        }
        else
        {
            if ( ! self::schemasExist($ClientObj, 'waypoint_ledger_' . $ClientObj->client_id_old))
            {
                throw new GeneralException('could not find ledger database');
            }
        }
        if ($enable_query_log)
        {
            $LedgerDatabaseConnectionObj->enableQueryLog();
        }
        return $LedgerDatabaseConnectionObj;
    }

    /**
     * @param Connection $LedgerDatabaseConnectionObj
     * @param Client $ClientObj
     * @return Connection
     */
    static function switchToReportTemplateBasedLedgerDatabase(Connection $LedgerDatabaseConnectionObj, Client $ClientObj)
    {
        // set new report template based database name
        $default_analytics_report_template_id = App::make(UserRepository::class)->getDefaultAnalyticsReportTemplate()->id;
        $adjusted_schema_name                 = 'waypoint_ledger_' . $ClientObj->client_id_old . '_' . $default_analytics_report_template_id;

        if ( ! self::schemasExist($ClientObj, $adjusted_schema_name))
        {
            throw new GeneralException('could not find ledger database based on report template');
        }

        // gather existing config and adjust the database name
        $databaseConfig             = $LedgerDatabaseConnectionObj->getConfig();
        $databaseConfig['database'] = $adjusted_schema_name;
        config(['database.connections.' . 'mysql_WAYPOINT_LEDGER_' . $ClientObj->client_id_old => $databaseConfig]);

        // remove the old connection
        DB::purge('mysql_WAYPOINT_LEDGER_' . $ClientObj->client_id_old);

        // create and return new connection with new database name configured
        return DB::connection('mysql_WAYPOINT_LEDGER_' . $ClientObj->client_id_old);
    }

    /**
     * @param Client $ClientObj
     * @return \Illuminate\Database\Connection
     */
    static function getInformationSchemaDatabaseConnection(Client $ClientObj)
    {
        if ( ! $ClientObj)
        {
            throw new GeneralException('unusable client object');
        }
        if ( ! $InformationSchemaDatabaseConnectionObj = DB::connection('mysql_BENCHMARK_INFORMATION_SCHEMA'))
        {
            throw new GeneralException('could not connect to information schema database');
        }
        return $InformationSchemaDatabaseConnectionObj;
    }

    /**
     * @param Client $ClientObj
     * @param $schemas
     * @return bool
     * @throws GeneralException
     */
    static function schemasExist(Client $ClientObj, $schemas)
    {
        $schemas = is_array($schemas) ? $schemas : [$schemas];
        $results = self::getInformationSchemaDatabaseConnection($ClientObj)
                       ->table('SCHEMATA')
                       ->whereIn('SCHEMA_NAME', $schemas)
                       ->select('SCHEMA_NAME')
                       ->get();

        return $results->count() == count($schemas);
    }

    /**
     * @param Client $ClientObj
     * @param $table_schema
     * @param array|string $table_names
     * @return bool
     */
    static function tablesExist(Client $ClientObj, $table_schema, $tables)
    {
        if (empty($tables))
        {
            return true;
        }

        $tables  = is_array($tables) ? $tables : [$tables];
        $results = self::getInformationSchemaDatabaseConnection($ClientObj)
                       ->table('TABLES')
                       ->where('TABLE_SCHEMA', $table_schema)
                       ->whereIn('TABLE_NAME', $tables)
                       ->select('TABLE_NAME')
                       ->get();

        if ($results->count() == count($tables))
        {
            // if ledger database
            if (strpos($table_schema, 'ledger') !== false)
            {
                $DatabaseConnectionObj = self::getLedgerDatabaseConnection($ClientObj);
            }
            elseif (strpos($table_schema, 'peer') !== false)
            {
                $DatabaseConnectionObj = self::getPeerDatabaseConnection($ClientObj);
            }
            elseif (strpos($table_schema, 'group') !== false)
            {
                $DatabaseConnectionObj = self::getGroupDatabaseConnection($ClientObj);
            }
            elseif (strpos($table_schema, 'staging') !== false)
            {
                $DatabaseConnectionObj = self::getStagingDatabaseConnection($ClientObj);
            }
            else
            {
                return false;
            }

            foreach ($tables as $table)
            {
                $table_count = $DatabaseConnectionObj
                    ->table($table)
                    ->count();

                if (empty($table_count))
                {
                    return false;
                }
            }
            return true;
        }
        else
        {
            return false;
        }
    }
}
