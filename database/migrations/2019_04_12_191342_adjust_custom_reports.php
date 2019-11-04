<?php

use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdjustCustomReports extends Migration
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
            $table->integer('property_id')->unsigned()->nullable()->change();
            $table->integer('property_group_id')->unsigned()->index()->after('property_id')->nullable();
            $table->foreign('property_group_id')->references('id')->on('property_groups');
            $table->foreign('property_id')->references('id')->on('properties');
        });

        Schema::table('custom_report_types', function (Blueprint $table)
        {
            $table->string('entity_type')->after('period_type')->default('property');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        throw new GeneralException('we do not support migration reversal');
    }
}
