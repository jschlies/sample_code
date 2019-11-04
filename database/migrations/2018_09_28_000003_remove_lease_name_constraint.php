<?php

use App\Waypoint\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CalculatedFieldEquationDisplayString
 */
class RemoveLeaseNameConstraint extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (MigrationHelper::index_exists('leases', 'leases_property_id_lease_name_unique'))
        {
            Schema::table(
                'leases',
                function (Blueprint $table)
                {
                    $table->dropUnique('leases_property_id_lease_name_unique');
                }
            );
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
