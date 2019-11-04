<?php

use Illuminate\Database\Migrations\Migration;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class CalculatedFieldEquationDisplayString
 */
class LockDownOktaAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * database/migrations/2019_01_08_000001_lock_down_okta_accounts_two.php
         */
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
