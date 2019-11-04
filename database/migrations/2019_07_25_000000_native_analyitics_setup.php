<?php

use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class NativeAnalyiticsSetup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if ( ! Schema::hasTable('native_account_amounts'))
        {
            Schema::create(
                'native_account_amounts',
                function (Blueprint $table)
                {
                    $table->increments('id');
                    $table->integer('client_id')->unsigned()->index();
                    $table->integer('property_id')->unsigned()->index();
                    $table->integer('native_account_id')->unsigned()->index();
                    $table->integer('month');
                    $table->integer('year');
                    $table->datetime('month_year_timestamp')->index();
                    $table->decimal('actual', 9, 3);
                    $table->decimal('budget', 9, 3);

                    $table->timestamps();

                    $table->unique(['property_id', 'native_account_id', 'month', 'year'], 'native_account_amounts_pinaimy_unique');
                });

            Schema::table(
                'native_account_amounts',
                function (Blueprint $table)
                {
                    $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
                    $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
                    $table->foreign('native_account_id')->references('id')->on('native_accounts')->onDelete('cascade');
                });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public
    function down()
    {
        throw new GeneralException('we do not support migration reversal');
    }
}
