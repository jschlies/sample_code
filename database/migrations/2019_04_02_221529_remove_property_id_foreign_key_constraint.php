<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Waypoint\Exceptions\GeneralException;

class RemovePropertyIdForeignKeyConstraint extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('custom_reports', function (Blueprint $table)
        {
            $table->dropForeign('custom_reports_property_id_foreign');
        });

    }

    public function down()
    {
        throw new GeneralException('we do not support migration reversal');
    }
}
