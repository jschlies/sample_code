<?php

namespace App\Waypoint;

use App;
use DB;

/**
 * Trait MigrationHelperAuditTrait
 * @package App\Waypoint
 *
 * @codeCoverageIgnore
 */
trait MigrationHelperPotpourriTrait
{
    /**
     * @param $table
     * @param $foreign_key_name
     * @return bool
     */
    public static function foreign_key_exists($table, $foreign_key_name)
    {
        $keyExists = DB::select(
            DB::raw(
                "SELECT 
                        TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
                    FROM
                        INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                    WHERE
                        REFERENCED_TABLE_SCHEMA = '" . config('database.connections.mysql.database', 'homestead') . "' AND
                        TABLE_NAME = '" . $table . "' AND 
                        CONSTRAINT_NAME = '" . $foreign_key_name . "';"
            )
        );
        return (boolean) $keyExists;
    }

    /**
     * @param $table
     * @param $index_name
     * @return bool
     */
    public static function index_exists($table, $index_name)
    {
        $keyExists = DB::select(
            DB::raw(
                "SHOW KEYS
                    FROM " . $table . "
                    WHERE Key_name='" . $index_name . "'"
            )
        );
        return (boolean) $keyExists;
    }

}