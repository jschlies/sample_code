<?php

use Illuminate\Database\Migrations\Migration;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class ConsolidateMigrations
 */
class ConsolidateMigrations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('APP_ENV') == 'local')
        {
            $results_arr = DB::select(
                DB::raw("
                    SELECT * 
                        FROM information_schema.tables
                        WHERE table_schema = '" . config('database.mysql.database', 'homestead') . "'
                            AND table_name = 'clients'
                        LIMIT 1;  
                ")
            );
            if (env('APP_ENV') == 'local')
            {
                if (count($results_arr) == 0)
                {
                    DB::unprepared(file_get_contents(resource_path() . '/assets/mysql/homestead.HER-708_consolidate_migrations.sql'));
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        throw new GeneralException('Migration down() not supported');
    }
}
