<?php

use App\Waypoint\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CalculatedFieldEquationDisplayString
 */
class LockDownOktaAccountsTwo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('users', 'authenticating_entity_id'))
        {
            Schema::table(
                'users',
                function (Blueprint $table)
                {
                    /**
                     * See HER-3127
                     */
                    $table->integer('authenticating_entity_id')->unsigned()->default(1)->index()->after('client_id');

                }
            );
        }

        if ( ! Schema::hasTable('authenticating_entities'))
        {
            Schema::create('authenticating_entities', function (Blueprint $table)
            {
                $table->increments('id');
                $table->string('name');
                $table->string('description')->nullable();
                $table->string('email_regex');
                $table->string('identity_connection');
                $table->boolean('is_default')->default(false);
                $table->timestamps();

                $table->unique(['name']);
                $table->unique(['email_regex']);
            });
            DB::insert(
                DB::raw(
                    "
                    INSERT INTO authenticating_entities 
                        SET 
                            name = 'Auth0', 
                            description = 'Auth0', 
                            email_regex = '/.*/', 
                            identity_connection = 'Username-Password-Authentication',
                            is_default = true                       
                "
                )
            );
        }

        $Auth0ResultObjArr = DB::select(
            DB::raw(
                "
                    SELECT * FROM authenticating_entities 
                        WHERE 
                            name = 'Auth0' AND
                            description = 'Auth0' AND
                            email_regex = '/.*/' AND 
                            identity_connection = 'Username-Password-Authentication' AND
                            is_default = true                       
                "
            )
        );

        DB::update(
            DB::raw(
                "
                    UPDATE users SET authenticating_entity_id = :AUTHENTICATING_ENTITY_ID
                "
            ),
            [
                'AUTHENTICATING_ENTITY_ID' => $Auth0ResultObjArr[0]->id,
            ]
        );
        if ( ! MigrationHelper::foreign_key_exists('users', 'users_authenticating_entity_id_foreign'))
        {
            Schema::table(
                'users',
                function (Blueprint $table)
                {
                    $table->foreign('authenticating_entity_id')->references('id')->on('authenticating_entities');
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
