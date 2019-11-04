<?php

use App\Waypoint\Events\CalculateVariousPropertyListsEvent;
use App\Waypoint\Events\PreCalcClientEvent;
use App\Waypoint\Events\PreCalcPropertiesEvent;
use App\Waypoint\Events\PreCalcPropertyGroupsEvent;
use App\Waypoint\Events\PreCalcUsersEvent;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Client;
use App\Waypoint\Repositories\CalculateVariousPropertyListsRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FinallyForPropertyGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('property_groups', 'client_id'))
        {
            Schema::table('property_groups', function (Blueprint $table)
            {
                $table->integer('client_id')->unsigned()->index()->after('user_id');
            });

            DB::update("ALTER TABLE clients  ADD pre_calc_json LONGBLOB;");
            DB::update("ALTER TABLE users  ADD pre_calc_json LONGBLOB;");
            DB::update("ALTER TABLE properties  ADD pre_calc_json LONGBLOB;");
            DB::update("ALTER TABLE property_groups  ADD pre_calc_json LONGBLOB;");

            DB::update(
                DB::raw(
                    '
                        UPDATE clients
                            SET clients.pre_calc_json = :EMPTY_JSON;
                    '
                ),
                [
                    'EMPTY_JSON' => json_encode(new stdClass()),
                ]
            );
            DB::update(
                DB::raw(
                    '
                        UPDATE properties
                            SET properties.pre_calc_json = :EMPTY_JSON;
                    '
                ),
                [
                    'EMPTY_JSON' => json_encode(new stdClass()),
                ]
            );
            DB::update(
                DB::raw(
                    '
                        UPDATE users
                            SET users.pre_calc_json = :EMPTY_JSON;
                    '
                ),
                [
                    'EMPTY_JSON' => json_encode(new stdClass()),
                ]
            );

            DB::update(
                DB::raw(
                    '
                        UPDATE property_groups
                            SET property_groups.client_id = (SELECT users.client_id FROM users WHERE users.id=property_groups.user_id);
                    '
                )
            );
            Schema::table('property_groups', function (Blueprint $table)
            {
                $table->foreign('client_id')->references('id')->on('clients');
            });

            $ClientResultArr = DB::select(
                DB::raw(
                    '
                        SELECT * FROM clients where id != 1
                    '
                )
            );
            /** @var CalculateVariousPropertyListsRepository $CalculateVariousPropertyListsRepositoryObj */
            $CalculateVariousPropertyListsRepositoryObj = App::make(CalculateVariousPropertyListsRepository::class);
            foreach ($ClientResultArr as $ClientResult)
            {
                echo "Migrating " . $ClientResult->name . ' ' . Carbon::now()->format('Y-m-d H:i:s') . PHP_EOL;
                $ClientObj = Client::find($ClientResult->id);

                $CalculateVariousPropertyListsRepositoryObj->CalculateVariousPropertyListsJobProcessor($ClientObj->id, true);

                if ( ! config('waypoint.suppress_pre_calc_events', true))
                {
                    event(new CalculateVariousPropertyListsEvent($ClientObj));
                    event(new PreCalcClientEvent($ClientObj));
                    event(new PreCalcUsersEvent($ClientObj));
                    event(new PreCalcPropertiesEvent($ClientObj));
                    event(new PreCalcPropertyGroupsEvent($ClientObj));
                }
            }
        }

        $ClientResultArr = DB::select(
            DB::raw(
                '
                    SELECT * FROM clients where id != 1
                '
            )
        );

        /** @var CalculateVariousPropertyListsRepository $CalculateVariousPropertyListsRepositoryObj */
        $CalculateVariousPropertyListsRepositoryObj = App::make(CalculateVariousPropertyListsRepository::class);
        foreach ($ClientResultArr as $ClientResult)
        {
            echo "Migrating " . $ClientResult->name . ' ' . Carbon::now()->format('Y-m-d H:i:s') . PHP_EOL;
            $ClientObj = Client::find($ClientResult->id);

            $CalculateVariousPropertyListsRepositoryObj->CalculateVariousPropertyListsJobProcessor($ClientObj->id, true);
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
