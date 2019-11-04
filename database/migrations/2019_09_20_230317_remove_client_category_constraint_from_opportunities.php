<?php

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\MigrationHelper;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveClientCategoryConstraintFromOpportunities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (MigrationHelper::foreign_key_exists('opportunities', 'opportunities_client_category_id_foreign'))
        {
            Schema::table('opportunities', function (Blueprint $table)
            {
                $table->dropForeign('opportunities_client_category_id_foreign');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        throw new GeneralException('we do not support down migrations');
    }
}
