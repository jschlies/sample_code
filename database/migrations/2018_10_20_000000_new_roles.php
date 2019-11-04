<?php

use App\Waypoint\Models\Role;
use Illuminate\Database\Migrations\Migration;
use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CalculatedFieldEquationDisplayString
 */
class NewRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'permissions',
            function (Blueprint $table)
            {
                /**
                 * deadcode
                 */
                $table->dropColumn('client_based');
                $table->dropColumn('user_based');
                $table->dropColumn('property_group_based');
            }
        );
        Schema::table(
            'roles',
            function (Blueprint $table)
            {
                $table->integer('client_id')->unsigned()->default(1)->index()->after('description');
                $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            }
        );
        Schema::table(
            'roles',
            function (Blueprint $table)
            {
                $table->dropUnique('roles_name_unique');
                $table->unique(['client_id', 'name']);
            }
        );
        DB::select(
            DB::raw(
                '
                    DELETE FROM roles 
                        WHERE 
                            name not in (\'Root\', \'ClientAdmin\',\'ClientUser\')
                '
            )
        );
        DB::select(
            DB::raw(
                '
                    INSERT INTO roles 
                        SET 
                            name = :NAME, 
                            display_name = :DISPLAY_NAME, 
                            description = :DESCRIPTION,
                            created_at = now(),
                            updated_at = now()
                '
            ),
            [
                'NAME'         => Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE,
                'DISPLAY_NAME' => Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE,
                'DESCRIPTION'  => Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE,
            ]
        );
        DB::select(
            DB::raw(
                '
                    INSERT INTO roles 
                        SET 
                            name = :NAME, 
                            display_name = :DISPLAY_NAME, 
                            description = :DESCRIPTION,
                            created_at = now(),
                            updated_at = now()
                '
            ),
            [
                'NAME'         => Role::WAYPOINT_ASSOCIATE_ROLE,
                'DISPLAY_NAME' => Role::WAYPOINT_ASSOCIATE_ROLE,
                'DESCRIPTION'  => Role::WAYPOINT_ASSOCIATE_ROLE,
            ]
        );
        DB::update(
            DB::raw(
                '
                    UPDATE role_users  
                        SET 
                            created_at = now(),
                            updated_at = now()
                '
            )
        );
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
